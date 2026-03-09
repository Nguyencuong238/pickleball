# Phase 2: Frontend - Auto Generate Teams UI

## Priority: High
## Status: Not Started

## Overview
Add "Auto Generate" button next to "Them Doi" button, with modal for selecting pairing mode.

## Related Files
- `resources/views/home-yard/leagues/_tab-teams.blade.php` (modify)

## Implementation Steps

### 1. Add "Tu dong xep doi" button
Next to existing "Them Doi" button, same style but different color (blue/info).

### 2. Create Auto-Generate Modal
```
+------------------------------------------+
| Tu dong xep doi                    [X]   |
+------------------------------------------+
| Che do xep cap:                          |
|                                          |
| So VDV/doi: [__2__]  (mac dinh theo format)|
|                                          |
| (*) Phan hang theo trinh                 |
|     Trinh A mix voi trinh B              |
|                                          |
| ( ) Ngau nhien                           |
|     Xep cap ngau nhien                   |
|                                          |
| [!] Co {N} VDV chua xep doi             |
|                                          |
|              [Huy]  [Tao doi tu dong]    |
+------------------------------------------+
```

### 3. JavaScript
- On modal open: fetch pool count to show info
- On submit: POST to auto-generate endpoint with `mode` param
- On success: reload page to show new teams
- On error: show toastr error

### 4. Confirm dialog
Before submitting, show confirm: "Se tao {X} doi tu {Y} VDV. Tiep tuc?"

## Todo
- [ ] Add auto-generate button next to "Them Doi"
- [ ] Create modal with radio buttons for mode selection
- [ ] Show pool count info
- [ ] AJAX submit to backend endpoint
- [ ] Handle success/error responses
- [ ] Confirm dialog before submission

## Success Criteria
- Button visible only in draft/registration status
- Modal shows pairing mode options with descriptions
- Pool count displayed accurately
- Successful generation reloads page with new teams
