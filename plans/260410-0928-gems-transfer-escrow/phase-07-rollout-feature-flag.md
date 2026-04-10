# Phase 07 — Rollout via Feature Flag

## Context Links
- Parent: `./plan.md` | Deps: phases 04, 05, 06
- Env: `GEMS_TRANSFER_ENABLED`, `GEMS_REFUND_WINDOW_DAYS`, `GEMS_PLATFORM_FEE_PERCENT`

## Overview
**Priority:** P1 | **Status:** pending | **Est:** 2h

Controlled rollout: document env vars, flip flag on staging → observe → production. No data backfill. Document mixed-mode in changelog. Add appendix on future withdraw design.

## Key Insights
- Historical burn-model txs remain untouched; no backfill per design decision.
- Flag is code-level default `false`; env override enables.
- Future withdraw feature design locked-in at schema level already.

## Requirements
**Functional**
- Add env vars to `.env.example` with comments
- Update `docs/project-changelog.md` with rollout notes
- Update `docs/system-architecture.md` with new wallet model diagram (optional inline)
- Document withdraw-stub design in appendix (this file)
- Monitor checklist for post-rollout

**Non-functional**
- Zero-downtime rollout
- Instant rollback via flag

## Rollout Sequence
1. Merge phases 01-06 with flag defaulted off. Production unaffected.
2. Deploy to staging. Flip `GEMS_TRANSFER_ENABLED=true` in staging env. Run smoke tests:
   - Book court with Gems → verify stadium owner receives locked Gems
   - Cancel within 24h → verify refund
   - Wait for release cron → verify unlock
   - Attempt cancel after release → verify block
3. Monitor staging for 48h.
4. Deploy to production with flag OFF initially. Verify no regressions.
5. Flip production flag ON during low-traffic window.
6. Observe metrics for 72h.
7. After stability confirmed (7+ days), remove feature flag & legacy `deduct()` branch (separate follow-up task, not in this plan).

## Monitoring Checklist (post-flip)
- [ ] `gem_transactions` new rows include `type IN (receipt, refund_clawback)`
- [ ] `gem_wallets.locked_balance` growing for active stadium owners
- [ ] `gems:release-locked` scheduler running every 5 min (check logs)
- [ ] No orphaned receipts older than `refund_window_days + 1`
- [ ] Invariant spot-check query:
  ```sql
  SELECT id, user_id, balance, locked_balance
  FROM gem_wallets
  WHERE locked_balance < 0 OR balance < 0 OR locked_balance > balance;
  -- Expect empty
  ```

## Related Code Files
**Modify**
- `.env.example` — add 3 vars
- `docs/project-changelog.md` — release notes
- `docs/system-architecture.md` — wallet model section

## Implementation Steps
1. Append to `.env.example`:
   ```
   # Gems transfer-to-owner model (Phase: escrow rollout)
   GEMS_TRANSFER_ENABLED=false
   GEMS_REFUND_WINDOW_DAYS=1
   GEMS_PLATFORM_FEE_PERCENT=0
   ```
2. Write changelog entry noting: new transfer model, 24h refund window, mixed-mode data reality (old burn txs coexist with new transfer txs), no backfill.
3. Update architecture doc with 2-3 paragraph section on wallet model.
4. Execute rollout sequence above.
5. Track monitoring checklist for 7 days; file follow-up task to remove legacy branch.

## Todo List
- [ ] Update .env.example
- [ ] Update project-changelog.md
- [ ] Update system-architecture.md
- [ ] Deploy + flip staging flag
- [ ] 48h staging observation
- [ ] Production deploy (flag off)
- [ ] Production flag flip
- [ ] 72h monitoring
- [ ] File follow-up task: remove legacy branch

## Success Criteria
- Production stable 7 days post-flip with no invariant violations
- Support tickets related to Gems refund <= baseline
- Release command logs show continuous operation

## Risk Assessment
- **Silent corruption during rollout window**: mitigated by invariant query in monitoring checklist
- **Dev phase, no legacy data**: no need for counterparty_transaction_id IS NULL fallback. `refund()` throws `LogicException` if encountered (data corruption signal).
- **Clock drift between app + DB**: use `NOW()` server-side in release scan
- **Multi-server scheduler**: `withoutOverlapping` only guards local file cache. If scaling to multiple servers, switch `CACHE_DRIVER=database` and enable `->onOneServer()` in Kernel schedule.
- **Platform fee semantics**: fee is burned in phase 1 (not credited to any wallet). Document in changelog so ops understand `SUM(platform_fee)` represents revenue captured but not wallet-materialized.

## Security Considerations
- Flag change is deploy-gated (env var), not runtime-toggleable by end users

## Appendix: Future Withdraw Stub Design

Schema already prepared for withdraw feature without breaking changes:

- New `gem_transactions.type` values to add later: `withdraw_request`, `withdraw_complete`, `withdraw_reject`
- `gem_wallets.locked_balance` semantics extend naturally: withdraw requests decrement spendable (NOT locked_balance, as withdraw-reserved funds differ from escrow). Introduce `pending_withdraw` column at that time OR reuse `locked_balance` with typed reservation tracking via a new `gem_wallet_reservations` table.
- `available_at` on withdraw_request rows represents payout eligibility, not refund window.
- Current `transfer()` signature accommodates fees via `platformFeePercent` — same mechanism applies to withdraw conversion fees.
- No DB changes required in current phase; design confirmed feasible on existing schema.

## Next Steps
- Follow-up task: remove legacy deduct() branch after 30 days stable
- Follow-up task: admin refund override UI (separate plan)
- Follow-up task: owner withdraw-to-VND feature (separate plan)
