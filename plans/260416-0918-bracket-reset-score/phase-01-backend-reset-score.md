# Phase 01 - Backend: Reset Score Endpoint

## Overview
- **Priority:** P1
- **Status:** Pending
- **Mo ta:** Them method `resetScore` trong controller va service de xoa ti so tran bracket, reset status, cascade downstream

## Key Insights
- `cascadeClearDownstream()` trong `KnockoutBracketQuery` (line 205) **chi xoa athlete/winner/status** nhung **KHONG xoa score fields** (`set_scores`, `final_score`, `athlete1_score`, `athlete2_score`, `actual_end_time`) -> can sua hoac dung method rieng
- `handleBracketAdvancement` (BracketAdvancementTrait) advance winner vao next match va route loser vao bronze match
- `loser_id` la computed accessor (`winner_id === athlete1_id ? athlete2_id : athlete1_id`) -> mat khi `winner_id = null` -> phai capture TRUOC khi clear
- Tran bracket khong co `group_id` nen khong can rollback group standings

## Requirements

### Functional
- Xoa tat ca truong score cua tran hien tai: `set_scores`, `final_score`, `winner_id`, `athlete1_score`, `athlete2_score`, `actual_end_time`
- Reset `status` ve `scheduled`
- Cascade clear downstream: xoa VDV/winner/scores o cac tran vong sau
- Cap nhat lai round completion count cho tat ca round bi anh huong
- Revert third-place routing neu la tran semifinal (chi xoa VDV cu the, khong xoa toan bo bronze match)

### Non-functional
- Dung DB transaction de dam bao data consistency
- Lock row truoc khi update (lockForUpdate)
- Chi cho phep owner giai dau thao tac

## Related Code Files

| File | Action | Mo ta |
|------|--------|-------|
| `app/Http/Controllers/Front/Tournament/TournamentBracketController.php` | Modify | Them method `resetScore()` |
| `app/Services/Tournament/KnockoutBracketService.php` | Modify | Them method `resetMatchScore()`, `clearThirdPlaceRouting()` |
| `app/Services/Tournament/KnockoutBracketQuery.php` | Modify | Sua `cascadeClearDownstream()` de cung xoa score fields |
| `routes/web.php` | Modify | Them route `DELETE bracket/reset-score` |

## Implementation Steps

### 1. Them route trong `routes/web.php`

Tim group bracket routes (line ~661-666), them:
```php
Route::delete('{tournament}/bracket/reset-score', [TournamentBracketController::class, 'resetScore'])->name('bracket.reset-score');
```

### 2. Sua `cascadeClearDownstream()` trong `KnockoutBracketQuery.php` (line 205-224)

Them cac score fields vao update array:
```php
public function cascadeClearDownstream(MatchModel $match): void
{
    $currentMatch = $match;

    while ($currentMatch->next_match_id) {
        $nextMatch = MatchModel::find($currentMatch->next_match_id);
        if (!$nextMatch) break;

        $nextMatch->update([
            'athlete1_id'     => null,
            'athlete1_name'   => null,
            'athlete2_id'     => null,
            'athlete2_name'   => null,
            'winner_id'       => null,
            'set_scores'      => null,
            'final_score'     => null,
            'athlete1_score'  => 0,
            'athlete2_score'  => 0,
            'actual_end_time' => null,
            'status'          => 'scheduled',
        ]);

        $currentMatch = $nextMatch;
    }
}
```

**Luu y:** Method nay cung duoc goi boi `updateMatch` khi thay doi VDV. Viec them clear score fields o day la **an toan** vi khi athlete thay doi, score cu cung khong con y nghia.

### 3. Them `resetScore()` trong `TournamentBracketController.php`

Sau method `updateMatch()` (line 166), them:
```php
public function resetScore(Request $request, Tournament $tournament): JsonResponse
{
    $this->authorizeOwner($tournament);

    $validated = $request->validate([
        'match_id' => ['required', 'integer', Rule::exists('matches', 'id')->where('tournament_id', $tournament->id)],
    ]);

    try {
        $match = MatchModel::findOrFail($validated['match_id']);

        if ($match->status === 'bye') {
            return response()->json([
                'success' => false,
                'message' => 'Khong the xoa ti so tran bye.',
            ], 422);
        }

        if (!$match->set_scores && !$match->winner_id) {
            return response()->json([
                'success' => false,
                'message' => 'Tran dau chua co ti so de xoa.',
            ], 422);
        }

        $result = $this->bracketService->resetMatchScore($match);
        $statusCode = ($result['success'] ?? false) ? 200 : 422;
        return response()->json($result, $statusCode);
    } catch (\Exception $e) {
        Log::error('Xoa ti so that bai: ' . $e->getMessage(), ['exception' => $e]);
        return response()->json(['success' => false, 'message' => 'Xoa ti so that bai'], 500);
    }
}
```

### 4. Them `resetMatchScore()` trong `KnockoutBracketService.php`

Sau method `updateMatch()` (line 260), them:
```php
/**
 * Xoa ti so va reset tran dau ve trang thai scheduled.
 * Cascade clear cac tran vong sau (bao gom score).
 *
 * @return array{success: bool, message: string}
 */
public function resetMatchScore(MatchModel $match): array
{
    return DB::transaction(function () use ($match) {
        $match = MatchModel::lockForUpdate()->findOrFail($match->id);

        // Capture loser_id TRUOC khi clear winner (cho third-place routing)
        $loserId = $match->loser_id;

        // Collect downstream round IDs truoc khi cascade (de update round status sau)
        $affectedRoundIds = $this->collectDownstreamRoundIds($match);

        // Xoa score fields cua tran hien tai
        $match->set_scores      = null;
        $match->final_score     = null;
        $match->winner_id       = null;
        $match->athlete1_score  = 0;
        $match->athlete2_score  = 0;
        $match->actual_end_time = null;
        $match->status          = 'scheduled';
        $match->save();

        // Cascade clear downstream (xoa VDV + score da advance)
        $this->bracketQuery->cascadeClearDownstream($match);

        // Cap nhat round completion count cho round hien tai + downstream rounds
        $allRoundIds = array_unique(array_merge(
            [$match->round_id],
            $affectedRoundIds
        ));
        $this->refreshRoundStatuses($allRoundIds);

        // Clear third-place routing neu la semifinal
        $this->clearThirdPlaceRouting($match, $loserId);

        return ['success' => true, 'message' => 'Da xoa ti so thanh cong'];
    });
}

/**
 * Thu thap round IDs cua cac tran downstream.
 *
 * @return array<int>
 */
private function collectDownstreamRoundIds(MatchModel $match): array
{
    $roundIds = [];
    $current = $match;
    while ($current->next_match_id) {
        $next = MatchModel::find($current->next_match_id);
        if (!$next) break;
        $roundIds[] = $next->round_id;
        $current = $next;
    }
    return $roundIds;
}

/**
 * Cap nhat completed_matches va status cho danh sach rounds.
 *
 * @param array<int> $roundIds
 */
private function refreshRoundStatuses(array $roundIds): void
{
    $rounds = \App\Models\Round::whereIn('id', $roundIds)->get();
    foreach ($rounds as $round) {
        $completed = $round->matches()
            ->whereIn('status', ['completed', 'bye'])
            ->count();
        $round->update([
            'completed_matches' => $completed,
            'status' => $completed >= $round->total_matches ? 'completed' : 'in_progress',
        ]);
    }
}

/**
 * Xoa VDV thua cu the tu tran tranh hang ba (neu la tran ban ket).
 * Chi xoa VDV lien quan, khong reset toan bo bronze match.
 */
private function clearThirdPlaceRouting(MatchModel $match, ?int $loserId): void
{
    if (!$loserId) {
        return;
    }

    $round = $match->round;
    if (!$round || $round->round_type !== 'semifinal') {
        return;
    }

    $bronzeRound = \App\Models\Round::where('tournament_id', $match->tournament_id)
        ->where('category_id', $match->category_id)
        ->where('round_type', 'bronze')
        ->first();

    if (!$bronzeRound) {
        return;
    }

    $bronzeMatch = MatchModel::where('round_id', $bronzeRound->id)->first();
    if (!$bronzeMatch) {
        return;
    }

    // Chi xoa VDV cu the cua tran ban ket nay, khong dong den VDV tran ban ket kia
    if ((int) $bronzeMatch->athlete1_id === $loserId) {
        $bronzeMatch->update(['athlete1_id' => null, 'athlete1_name' => null]);
    } elseif ((int) $bronzeMatch->athlete2_id === $loserId) {
        $bronzeMatch->update(['athlete2_id' => null, 'athlete2_name' => null]);
    } else {
        return; // Loser khong co trong bronze match -> khong can lam gi
    }

    // Reset score bronze match neu da co score nhung mat VDV
    $bronzeMatch->refresh();
    if ($bronzeMatch->set_scores && (!$bronzeMatch->athlete1_id || !$bronzeMatch->athlete2_id)) {
        $bronzeMatch->update([
            'set_scores'      => null,
            'final_score'     => null,
            'winner_id'       => null,
            'athlete1_score'  => 0,
            'athlete2_score'  => 0,
            'actual_end_time' => null,
            'status'          => 'scheduled',
        ]);
    }

    // Cap nhat bronze round completion status (bronze round KHONG nam trong next_match_id chain)
    $this->refreshRoundStatuses([$bronzeRound->id]);
}
```

## Todo List
- [ ] Them route `DELETE bracket/reset-score` trong `routes/web.php`
- [ ] Sua `cascadeClearDownstream()` them clear score fields
- [ ] Them method `resetScore()` trong `TournamentBracketController`
- [ ] Them method `resetMatchScore()` trong `KnockoutBracketService`
- [ ] Them method `collectDownstreamRoundIds()` trong `KnockoutBracketService`
- [ ] Them method `refreshRoundStatuses()` trong `KnockoutBracketService`
- [ ] Them method `clearThirdPlaceRouting()` trong `KnockoutBracketService`
- [ ] Chay compile check

## Success Criteria
- Goi `DELETE /bracket/reset-score` voi `match_id` -> xoa score, reset status ve scheduled
- VDV da advance vao vong sau bi xoa + score cua tran downstream cung bi xoa
- Round completion count cap nhat dung cho tat ca round bi anh huong
- Third-place: chi xoa VDV cu the tu semifinal reset, khong xoa VDV tu semifinal kia
- Bronze match score cung bi reset neu mat VDV
- Transaction dam bao atomicity

## Risk Assessment
- **Cascade xoa qua nhieu:** Neu final da co ket qua, reset semifinal se xoa ca final -> confirm dialog o frontend (Phase 3) canh bao user
- **Sua `cascadeClearDownstream`:** Them score fields vao clear la backward-compatible vi khi athlete thay doi score cu cung vo nghia
- **Bronze match da co score:** Neu reset semifinal nhung bronze match da completed va co score -> cung phai reset bronze score (vi mat 1 VDV)
