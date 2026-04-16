# Phase 03 - Frontend: Nut xoa ti so

## Overview
- **Priority:** P1
- **Status:** Pending
- **Mo ta:** Them nut "Xoa ti so" tren UI bracket cho tran completed, goi endpoint reset-score

## Key Insights
- Nut "Sua ti so" da co (line 53 `_bracket-match.blade.php`) -> dat nut "Xoa ti so" canh no
- `bracket-score-entry.js` la noi chua logic lien quan score -> them `resetScore()` o day
- Sau khi xoa, goi `fetchBracket()` de reload data (da co san)

## Related Code Files

| File | Action | Mo ta |
|------|--------|-------|
| `resources/views/home-yard/tournaments/partials/_bracket-match.blade.php` | Modify | Them nut "Xoa ti so" |
| `public/assets/js/bracket-score-entry.js` | Modify | Them method `resetScore()` |

## Implementation Steps

### 1. Them nut "Xoa ti so" trong `_bracket-match.blade.php`

Trong block "Nut nhap ti so" (line 48-56), them nut xoa cho tran completed:

```blade
{{-- Nut nhap ti so --}}
<template x-if="(match.status === 'scheduled' || match.status === 'completed' || match.status === 'in_progress') && match.athlete1 && match.athlete2">
    <div style="padding:4px 8px;text-align:center;border-top:1px solid #f1f5f9;display:flex;justify-content:center;gap:12px;">
        <button class="bracket-score-btn"
                @click="openScore(match.id)"
                style="font-size:0.75rem;color:#0369a1;cursor:pointer;background:none;border:none;">
            <span x-text="match.status === 'completed' ? 'Sua ti so' : 'Nhap ti so'"></span>
        </button>
        <template x-if="match.status === 'completed' && match.set_scores">
            <button class="bracket-score-btn"
                    @click="resetScore(match.id)"
                    style="font-size:0.75rem;color:#dc2626;cursor:pointer;background:none;border:none;">
                Xoa ti so
            </button>
        </template>
    </div>
</template>
```

**Thay doi:** Wrap 2 nut trong flex container, them nut "Xoa ti so" mau do chi hien khi completed + co set_scores.

### 2. Them method `resetScore()` trong `bracket-score-entry.js`

Them sau method `saveScore()`:

```javascript
async resetScore(matchId) {
    if (!confirm('Xoa ti so se huy ket qua tran dau nay va cac tran tiep theo. Ban co chac?')) {
        return;
    }

    try {
        var url = this.dataUrl.replace('/data', '/reset-score');
        var res = await fetch(url, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ match_id: matchId }),
        });
        var data = await res.json();
        if (data.success) {
            await this.fetchBracket();
        } else {
            alert(data.message || 'Xoa ti so that bai');
        }
    } catch (e) {
        alert('Loi ket noi');
        console.error(e);
    }
},
```

## Todo List
- [ ] Them nut "Xoa ti so" trong `_bracket-match.blade.php`
- [ ] Them method `resetScore()` trong `bracket-score-entry.js`
- [ ] Test tren browser: nut chi hien khi completed + co score
- [ ] Test confirm dialog hien thi dung
- [ ] Test sau xoa: tran reset ve scheduled, downstream bi clear

## Success Criteria
- Nut "Xoa ti so" mau do chi hien tren tran completed co set_scores
- Click -> confirm dialog canh bao cascade
- Xac nhan -> goi API -> reload bracket
- Nut "Sua ti so" van hoat dong binh thuong

## Risk Assessment
- **Mis-click:** Confirm dialog ngan xoa nham
- **UX:** Mau do giup phan biet voi nut "Sua ti so" mau xanh
