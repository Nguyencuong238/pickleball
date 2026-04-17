<?php

namespace App\Services\Tournament;

use App\Models\Tournament;
use App\Models\TournamentAthlete;
use App\Models\TournamentCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AthleteImportService
{
    public function __construct(
        private readonly AthleteRowNormalizer $normalizer = new AthleteRowNormalizer(),
        private readonly AthleteImportValidator $validator = new AthleteImportValidator(),
    ) {
    }

    /**
     * Orchestrate full import pipeline: normalize → validate → dedupe → persist.
     *
     * @param  array<int, array<string, mixed>>  $rows Raw rows from Importer::parse() (each has _row_number)
     * @return array{created:int, skipped:array, errors:array}
     */
    public function execute(array $rows, Tournament $tournament): array
    {
        $categoriesByName = $this->buildCategoryMap($tournament);

        $normalized = array_map(fn ($r) => $this->normalizer->normalize($r), $rows);

        $errors = $this->validator->validate($normalized, $categoriesByName);
        if (!empty($errors)) {
            return ['created' => 0, 'skipped' => [], 'errors' => $errors];
        }

        [$toPersist, $skipped] = $this->detectDuplicates($normalized, $tournament, $categoriesByName);

        $created = 0;
        if (!empty($toPersist)) {
            DB::transaction(function () use ($toPersist, $categoriesByName, $tournament, &$created) {
                $created = $this->persistAthletes($toPersist, $categoriesByName, $tournament);
            });
        }

        return ['created' => $created, 'skipped' => $skipped, 'errors' => []];
    }

    /**
     * Resolve existing user by phone/email or create one with random password.
     * Shared with TournamentAthleteController@store for DRY.
     */
    public function resolveOrCreateUser(string $email, string $phone, string $name): User
    {
        $user = User::where('phone', $phone)->first()
            ?? User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'name'     => $name,
                'email'    => $email,
                'phone'    => $phone,
                'password' => bcrypt(Str::random(16)),
            ]);
        }

        return $user;
    }

    /**
     * @return array<string, TournamentCategory> Keyed by lowercase category_name
     */
    private function buildCategoryMap(Tournament $tournament): array
    {
        return $tournament->categories()
            ->get()
            ->keyBy(fn ($c) => mb_strtolower(trim($c->category_name)))
            ->all();
    }

    /**
     * Skip rows already existing within the SAME (tournament, category) on phone/email/name.
     * Cross-category collisions are allowed — one athlete may register in multiple categories.
     *
     * @param  array<int, array<string, mixed>>  $rows Normalized rows
     * @param  array<string, TournamentCategory>  $categoriesByName Keyed by lowercase category_name
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, array{row:int, athlete_name:string, reason:string}>}
     */
    private function detectDuplicates(array $rows, Tournament $tournament, array $categoriesByName): array
    {
        $existing = TournamentAthlete::where('tournament_id', $tournament->id)
            ->get(['phone', 'email', 'athlete_name', 'category_id']);

        $existingPhones = []; // key "categoryId|phone"
        $existingEmails = []; // key "categoryId|emailLower"
        $existingNames  = []; // key "categoryId|nameLower"
        foreach ($existing as $e) {
            if ($e->phone) {
                $existingPhones[$e->category_id . '|' . $e->phone] = true;
            }
            if ($e->email) {
                $existingEmails[$e->category_id . '|' . mb_strtolower($e->email)] = true;
            }
            if ($e->athlete_name) {
                $existingNames[$e->category_id . '|' . mb_strtolower($e->athlete_name)] = true;
            }
        }

        $toPersist = [];
        $skipped = [];
        foreach ($rows as $row) {
            $catKey = mb_strtolower($row['category_name']);
            // Defensive: validator rejects unknown categories upstream, but guard against warnings.
            if (!isset($categoriesByName[$catKey])) {
                continue;
            }
            $category = $categoriesByName[$catKey];
            $phoneKey = $category->id . '|' . $row['phone'];
            $emailKey = $category->id . '|' . mb_strtolower($row['email']);
            $nameKey  = $category->id . '|' . mb_strtolower($row['athlete_name']);

            $phoneDup = $row['phone'] !== '' && isset($existingPhones[$phoneKey]);
            $emailDup = $row['email'] !== '' && isset($existingEmails[$emailKey]);
            $nameDup  = $row['athlete_name'] !== '' && isset($existingNames[$nameKey]);

            if ($phoneDup || $emailDup || $nameDup) {
                $skipped[] = [
                    'row'          => $row['_row_number'],
                    'athlete_name' => $row['athlete_name'],
                    'reason'       => "Đã tồn tại trong hạng mục '{$category->category_name}'.",
                ];
                continue;
            }
            $toPersist[] = $row;
        }

        return [$toPersist, $skipped];
    }

    /**
     * Persist athletes in two passes: create all, then link partners bidirectionally.
     *
     * @param  array<int, array<string, mixed>>  $validRows Normalized, non-duplicate rows
     * @param  array<string, TournamentCategory>  $categoriesByName
     */
    private function persistAthletes(array $validRows, array $categoriesByName, Tournament $tournament): int
    {
        // Pass A: create all athletes and index by (categoryId → lowercase name)
        $byCategory = [];
        foreach ($validRows as $row) {
            $user = $this->resolveOrCreateUser($row['email'], $row['phone'], $row['athlete_name']);
            $category = $categoriesByName[mb_strtolower($row['category_name'])];

            $athlete = TournamentAthlete::create([
                'tournament_id'  => $tournament->id,
                'category_id'    => $category->id,
                'athlete_name'   => $row['athlete_name'],
                'email'          => $row['email'],
                'phone'          => $row['phone'],
                'user_id'        => $user->id,
                'status'         => 'pending',
                'payment_status' => 'unpaid',
            ]);

            $byCategory[$category->id][mb_strtolower($row['athlete_name'])] = [
                'id'           => $athlete->id,
                'partner_name' => $row['partner_name'],
            ];
        }

        // Pass B: link partners (bidirectional achieved by iterating every row)
        $total = 0;
        foreach ($byCategory as $athletes) {
            $total += count($athletes);
            foreach ($athletes as $data) {
                if ($data['partner_name'] === '') {
                    continue;
                }
                $partnerKey = mb_strtolower($data['partner_name']);
                if (!isset($athletes[$partnerKey])) {
                    continue;
                }
                TournamentAthlete::where('id', $data['id'])
                    ->update(['partner_id' => $athletes[$partnerKey]['id']]);
            }
        }

        return $total;
    }
}
