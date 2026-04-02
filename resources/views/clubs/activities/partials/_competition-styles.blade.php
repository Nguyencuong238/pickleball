{{-- Competition panel styles --}}
<style>
.competition-panel {
    background: #fffbeb;
    border-radius: 12px;
    padding: 24px;
    margin-top: 24px;
    border: 1px solid #fde68a;
}
.comp-section {
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid #fde68a;
}
.comp-section:first-of-type {
    margin-top: 0;
    padding-top: 0;
    border-top: none;
}
.comp-heading {
    font-size: 0.9rem;
    font-weight: 700;
    color: #92400e;
    margin: 0 0 12px 0;
}
.comp-empty {
    color: #9ca3af;
    font-size: 0.9rem;
    font-style: italic;
}
.btn-comp {
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.85rem;
    white-space: nowrap;
}
.btn-comp:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
.btn-add-team { background: #006646; color: white; }
.btn-add-team:hover:not(:disabled) { background: #0db89d; }
.btn-generate { background: #f59e0b; color: white; }
.btn-generate:hover:not(:disabled) { background: #d97706; }
.btn-remove-team { background: #fee2e2; color: #b91c1c; padding: 4px 10px; font-size: 0.8rem; }
.team-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 12px;
    background: white;
    border-radius: 8px;
    margin-bottom: 6px;
    border: 1px solid #e5e7eb;
}
.team-name { font-weight: 600; color: #374151; }
.match-round { font-weight: 700; color: #92400e; margin: 16px 0 8px; font-size: 0.9rem; }
.match-round:first-child { margin-top: 0; }
.match-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    background: white;
    border-radius: 8px;
    margin-bottom: 6px;
    border: 1px solid #e5e7eb;
    font-size: 0.9rem;
    flex-wrap: wrap;
}
.match-teams { flex: 1; font-weight: 500; }
.match-score { font-weight: 700; color: #1f2937; }
.match-score-input {
    width: 50px;
    padding: 6px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    text-align: center;
    font-size: 0.9rem;
}
.btn-save-score {
    background: #006646;
    color: white;
    padding: 6px 12px;
    border: none;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
}
.btn-save-score:disabled { opacity: 0.6; cursor: not-allowed; }
.standings-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
.standings-table th {
    background: #fef3c7;
    color: #92400e;
    padding: 8px 10px;
    text-align: left;
    font-weight: 700;
    font-size: 0.8rem;
}
.standings-table td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; color: #374151; }
.standings-table tr:hover td { background: #fffbeb; }
</style>
