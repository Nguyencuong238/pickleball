---
title: "Front-end Tournament Schedule Tab"
description: "Update schedule tab to show group stage matches, standings, and knockout bracket"
status: completed
priority: P1
effort: 6h
branch: main
tags: [frontend, tournament, blade]
created: 2026-03-14
completed: 2026-03-14
---

# Front-end Tournament Schedule Tab

## Overview

Replace plain-text schedule tab in `tabs-section.blade.php` with real tournament data: group stage matches, standings tables, and knockout bracket tree. Pure server-side render, read-only, no JS interactivity needed.

## Phases

| # | Phase | Status | Effort | Link |
|---|-------|--------|--------|------|
| 1 | Controller data loading | Completed | 1.5h | [phase-01](./phase-01-controller-data.md) |
| 2 | Group stage + standings UI | Completed | 2h | [phase-02](./phase-02-group-stage-ui.md) |
| 3 | Knockout bracket UI | Completed | 2h | [phase-03](./phase-03-knockout-bracket-ui.md) |
| 4 | Responsive + polish | Completed | 0.5h | [phase-04](./phase-04-responsive-polish.md) |

## Dependencies

- Existing models: `MatchModel`, `Group`, `GroupStanding`, `Round`, `TournamentCategory`
- Existing controller: `HomeController::tournamentsDetail()`
- Target view: `resources/views/front/tournaments/tabs-section.blade.php`

## Key Decisions

- **PA2 chosen**: New front-end view code, not reusing admin partials (admin uses Alpine.js + edit features, dark theme)
- **Server-side render only**: No Alpine.js, no JS API calls. All data loaded in controller, rendered in Blade.
- **Category tabs**: Each category gets its own group stage + bracket section
- **Fallback**: If no matches/groups exist, show existing text-based schedule from `competition_schedule`
