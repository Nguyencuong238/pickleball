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

        [$toPersist, $skipped] = $this->detectDuplicates($normalized, $tournament);

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
     * Skip rows that already exist in tournament (phone or email match).
     *
     * @param  array<int, array<string, mixed>>  $rows Normalized rows
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, array{row:int, athlete_name:string, reason:string}>}
     */
    private function detectDuplicates(array $rows, Tournament $tournament): array
    {
        $existing = TournamentAthlete::where('tournament_id', $tournament->id)
            ->get(['phone', 'email']);

        // Flip to associative hash for O(1) lookup
        $existingPhones = [];
        foreach ($existing->pluck('phone')->filter() as $p) {
            $existingPhones[(string) $p] = true;
        }
        $existingEmails = [];
        foreach ($existing->pluck('email')->filter() as $e) {
            $existingEmails[mb_strtolower((string) $e)] = true;
        }

        $toPersist = [];
        $skipped = [];
        foreach ($rows as $row) {
            $phoneDup = $row['phone'] !== '' && isset($existingPhones[$row['phone']]);
            $emailDup = $row['email'] !== '' && isset($existingEmails[$row['email']]);

            if ($phoneDup || $emailDup) {
                $skipped[] = [
                    'row'          => $row['_row_number'],
                    'athlete_name' => $row['athlete_name'],
                    'reason'       => 'Đã tồn tại trong giải.',
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
