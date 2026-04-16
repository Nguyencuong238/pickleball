# Phase 02 - Backend: Noi long guard updateMatch

## Overview
- **Priority:** P2
- **Status:** Pending
- **Mo ta:** Cho phep sua truong phi-ket-qua (ngay, gio, best_of, notes) tren tran completed. Chi block khi thay doi VDV.

## Key Insights
- Hien tai guard block **tat ca** edit khi completed + co score (line 147-155 controller)
- Cac truong `match_time`, `match_date`, `best_of`, `notes` khong anh huong ket qua -> an toan de sua
- Chi can block khi `athlete1_id` hoac `athlete2_id` thay doi tren tran completed
- **CRITICAL:** `KnockoutBracketService::updateMatch()` luon goi `reEvaluateMatch()` (line 256) sau moi update. Method nay **reset status ve scheduled + xoa winner khoi next match** -> neu khong skip se pha huy ket qua khi chi sua metadata

## Related Code Files

| File | Action | Mo ta |
|------|--------|-------|
| `app/Http/Controllers/Front/Tournament/TournamentBracketController.php` | Modify | Sua logic guard `isCompleted` |
| `app/Services/Tournament/KnockoutBracketService.php` | Modify | Skip `reEvaluateMatch` khi completed + chi sua metadata |

## Implementation Steps

### 1. Sua guard trong `TournamentBracketController::updateMatch()` (line 147-155)

Thay the logic hien tai:

**Truoc:**
```php
$isCompleted = $match->status === 'completed'
    && $match->athlete1_id && $match->athlete2_id
    && $match->set_scores;

if ($isCompleted) {
    return response()->json([
        'success' => false,
        'message' => 'Khong the chinh sua tran dau da hoan thanh co ti so. Xoa ti so truoc.',
    ], 422);
}
```

**Sau:**
```php
$isCompleted = $match->status === 'completed'
    && $match->athlete1_id && $match->athlete2_id
    && $match->set_scores;

if ($isCompleted) {
    $athleteChanging = (array_key_exists('athlete1_id', $validated) && (int) ($validated['athlete1_id'] ?? 0) !== (int) $match->athlete1_id)
        || (array_key_exists('athlete2_id', $validated) && (int) ($validated['athlete2_id'] ?? 0) !== (int) $match->athlete2_id);

    if ($athleteChanging) {
        return response()->json([
            'success' => false,
            'message' => 'Khong the thay doi VDV tren tran dau da hoan thanh co ti so. Xoa ti so truoc.',
        ], 422);
    }
}
```

### 2. Sua `KnockoutBracketService::updateMatch()` (line 245-259) - skip reEvaluateMatch khi completed

**Truoc:**
```php
return DB::transaction(function () use ($match, $data, $athleteChanged) {
    $match = MatchModel::lockForUpdate()->findOrFail($match->id);

    if ($athleteChanged) {
        $affectedCount = $this->bracketQuery->countCascadeAffected($match);
        if ($affectedCount > 0) {
            $this->bracketQuery->cascadeClearDownstream($match);
        }
    }

    $this->bracketQuery->updateMatchAthletes($match, $data);
    $this->reEvaluateMatch($match->fresh());

    return ['success' => true, 'message' => 'Cap nhat thanh cong'];
});
```

**Sau:**
```php
return DB::transaction(function () use ($match, $data, $athleteChanged) {
    $match = MatchModel::lockForUpdate()->findOrFail($match->id);

    if ($athleteChanged) {
        $affectedCount = $this->bracketQuery->countCascadeAffected($match);
        if ($affectedCount > 0) {
            $this->bracketQuery->cascadeClearDownstream($match);
        }
    }

    $this->bracketQuery->updateMatchAthletes($match, $data);

    // Chi re-evaluate khi athlete thay doi hoac tran chua completed
    // Tran completed chi sua metadata -> khong can re-evaluate (se pha huy ket qua)
    if ($athleteChanged || $match->status !== 'completed') {
        $this->reEvaluateMatch($match->fresh());
    }

    return ['success' => true, 'message' => 'Cap nhat thanh cong'];
});
```

**Ly do:** `reEvaluateMatch` reset `status=scheduled`, `winner_id=null` va xoa winner khoi next match. Goi no tren tran completed khi chi sua `match_time`/`notes` se pha huy ket qua.

## Todo List
- [ ] Sua guard `isCompleted` trong `TournamentBracketController::updateMatch()`
- [ ] Sua `KnockoutBracketService::updateMatch()` skip `reEvaluateMatch` khi completed + chi sua metadata
- [ ] Chay compile check

## Success Criteria
- Tran completed co score: sua ngay/gio/best_of/notes -> thanh cong, **winner/status/downstream KHONG bi thay doi**
- Tran completed co score: sua VDV -> block voi message ro rang
- Tran scheduled/in_progress: hanh vi khong thay doi (reEvaluateMatch van duoc goi)

## Risk Assessment
- **Medium:** Dieu kien skip `reEvaluateMatch` phai chinh xac. Neu sai se khong re-evaluate khi can thiet
- **Mitigation:** Dieu kien `$match->status !== 'completed'` dam bao chi skip cho tran da hoan thanh
