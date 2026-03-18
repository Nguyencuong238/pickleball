<?php

namespace App\Services\Tournament;

use App\Models\Tournament;
use App\Models\TournamentCategory;
use Illuminate\Support\Str;

class TournamentCrudService
{
    public function validationRules(bool $isUpdate = false): array
    {
        return [
            'name'                  => 'required|string|max:255',
            'start_date'            => 'required|date',
            'end_date'              => 'nullable|date|after_or_equal:start_date',
            'registration_deadline' => 'nullable|date',
            'location'              => 'nullable|string|max:255',
            'description'           => 'nullable|string',
            'competition_format'    => 'nullable|in:single,double,mixed',
            'tournament_rank'       => 'nullable|in:beginner,intermediate,advanced,professional',
            'max_participants'      => 'required|integer|min:2',
            'price'                 => 'nullable|numeric|min:0',
            'status'                => 'required|boolean',
            'banner'                => 'nullable|image|max:5120',
            'club_id'               => 'nullable|integer|exists:clubs,id',
            'competition_rules'     => 'nullable|string',
            'event_timeline'        => 'nullable|string',
            'prizes'                => 'nullable|numeric|min:0',
            'organizer_email'       => 'nullable|email|max:255',
            'organizer_hotline'     => 'nullable|string|max:50',
            'registration_benefits' => 'nullable|string',
            'social_information'    => 'nullable|string',
            'categories'                        => 'nullable|array',
            'categories.*.id'                   => 'nullable|integer',
            'categories.*.category_name'        => 'required|string|max:255',
            'categories.*.category_type'        => 'required|string',
            'categories.*.age_group'            => 'nullable|string|max:50',
            'categories.*.max_participants'     => 'nullable|integer|min:2',
        ];
    }

    public function fillable(array $validated): array
    {
        return [
            'name'                  => $validated['name'],
            'start_date'            => $validated['start_date'],
            'end_date'              => $validated['end_date'] ?? null,
            'registration_deadline' => $validated['registration_deadline'] ?? null,
            'location'              => $validated['location'] ?? null,
            'description'           => $validated['description'] ?? null,
            'competition_format'    => $validated['competition_format'] ?? null,
            'tournament_rank'       => $validated['tournament_rank'] ?? null,
            'max_participants'      => $validated['max_participants'],
            'price'                 => $validated['price'] ?? 0,
            'status'                => $validated['status'],
            'club_id'               => $validated['club_id'] ?? null,
            'competition_rules'     => $validated['competition_rules'] ?? null,
            'event_timeline'        => $validated['event_timeline'] ?? null,
            'prizes'                => $validated['prizes'] ?? 0,
            'organizer_email'       => $validated['organizer_email'] ?? null,
            'organizer_hotline'     => $validated['organizer_hotline'] ?? null,
            'registration_benefits' => $validated['registration_benefits'] ?? null,
            'social_information'    => $validated['social_information'] ?? null,
        ];
    }

    public function generateSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i    = 1;

        while (Tournament::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    public function syncCategories(Tournament $tournament, array $categories): void
    {
        $keptIds = [];

        foreach ($categories as $data) {
            $id = isset($data['id']) && $data['id'] ? (int) $data['id'] : null;

            if ($id) {
                $cat = TournamentCategory::where('id', $id)
                    ->where('tournament_id', $tournament->id)
                    ->first();

                if ($cat) {
                    $cat->update($this->categoryFillable($data));
                    $keptIds[] = $cat->id;
                    continue;
                }
            }

            $cat = $tournament->categories()->create(
                array_merge($this->categoryFillable($data), ['status' => 1])
            );
            $keptIds[] = $cat->id;
        }

        $deleteQuery = $tournament->categories()->whereDoesntHave('athletes');

        if (!empty($keptIds)) {
            $deleteQuery->whereNotIn('id', $keptIds)->delete();
        } elseif (empty($categories)) {
            $deleteQuery->delete();
        }
    }

    private function categoryFillable(array $data): array
    {
        return [
            'category_name'    => $data['category_name'],
            'category_type'    => $data['category_type'],
            'age_group'        => $data['age_group'] ?? '',
            'max_participants' => $data['max_participants'] ?? 16,
        ];
    }
}
