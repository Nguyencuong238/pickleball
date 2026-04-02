{{-- Matches panel styles --}}
<style>
.matches-panel {
    background: #f0fdf4;
    border-radius: 12px;
    padding: 24px;
    margin-top: 24px;
    border: 1px solid #bbf7d0;
}
.matches-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 16px;
}
.matches-section {
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid #bbf7d0;
}
.matches-heading {
    font-size: 0.9rem;
    font-weight: 700;
    color: #166534;
    margin: 0 0 12px 0;
}
.matches-empty {
    color: #9ca3af;
    font-size: 0.9rem;
    font-style: italic;
    text-align: center;
    padding: 24px 0;
}
.btn-matches {
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.85rem;
    white-space: nowrap;
}
.btn-matches:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-matches-primary { background: #006646; color: white; }
.btn-matches-primary:hover:not(:disabled) { background: #0db89d; }
.btn-matches-secondary { background: #e0f2fe; color: #0369a1; }
.btn-matches-secondary:hover:not(:disabled) { background: #bae6fd; }
.btn-matches-danger { background: #fee2e2; color: #b91c1c; padding: 6px 12px; font-size: 0.8rem; }
.btn-matches-danger:hover:not(:disabled) { background: #fecaca; }

/* Round sections */
.round-section {
    margin-bottom: 16px;
}
.round-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}
.round-title {
    font-weight: 700;
    color: #166534;
    font-size: 0.9rem;
}
.round-status {
    font-size: 0.75rem;
    padding: 2px 8px;
    border-radius: 10px;
    font-weight: 600;
}
.round-status-pending { background: #fef3c7; color: #92400e; }
.round-status-in_progress { background: #dbeafe; color: #1e40af; }
.round-status-completed { background: #dcfce7; color: #166534; }

/* Match cards */
.match-card {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    background: white;
    border-radius: 8px;
    margin-bottom: 6px;
    border: 1px solid #e5e7eb;
    font-size: 0.9rem;
    flex-wrap: wrap;
}
.match-court {
    background: #f3f4f6;
    color: #6b7280;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 6px;
    white-space: nowrap;
}
.match-teams {
    flex: 1;
    font-weight: 500;
    color: #374151;
}
.match-teams .team-separator {
    color: #9ca3af;
    margin: 0 6px;
    font-weight: 400;
}
.match-bye {
    color: #9ca3af;
    font-style: italic;
}
.match-score-display {
    font-weight: 700;
    color: #1f2937;
    font-size: 0.95rem;
}
.match-score-display .score-separator {
    color: #9ca3af;
    margin: 0 4px;
}

/* Score inputs */
.score-inputs {
    display: flex;
    align-items: center;
    gap: 4px;
}
.score-input {
    width: 45px;
    padding: 5px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    text-align: center;
    font-size: 0.85rem;
}
.btn-save-score {
    background: #006646;
    color: white;
    padding: 5px 10px;
    border: none;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
}
.btn-save-score:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-edit-score {
    background: none;
    border: none;
    color: #0369a1;
    font-size: 0.8rem;
    cursor: pointer;
    text-decoration: underline;
}

/* Standings table */
.matches-standings-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}
.matches-standings-table th {
    background: #dcfce7;
    color: #166534;
    padding: 8px 10px;
    text-align: left;
    font-weight: 700;
    font-size: 0.8rem;
}
.matches-standings-table td {
    padding: 8px 10px;
    border-bottom: 1px solid #e5e7eb;
    color: #374151;
}
.matches-standings-table tr:hover td { background: #f0fdf4; }
.rank-1 td:first-child { color: #d97706; font-weight: 700; }
.rank-2 td:first-child { color: #6b7280; font-weight: 700; }
.rank-3 td:first-child { color: #92400e; font-weight: 700; }

/* Modals */
.matches-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
.matches-modal-overlay.active { display: flex; }
.matches-modal {
    background: white;
    border-radius: 12px;
    padding: 24px;
    width: 90%;
    max-width: 480px;
    max-height: 80vh;
    overflow-y: auto;
}
.matches-modal h3 {
    margin: 0 0 16px;
    font-size: 1.1rem;
    color: #1f2937;
}
.matches-modal label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 4px;
}
.matches-modal select,
.matches-modal input[type="number"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.9rem;
    margin-bottom: 12px;
}
.matches-modal-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    margin-top: 16px;
}
.btn-modal-cancel {
    padding: 10px 18px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: white;
    color: #374151;
    font-weight: 600;
    cursor: pointer;
}
.player-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}
.player-grid.singles-mode > :nth-child(2),
.player-grid.singles-mode > :nth-child(4) {
    display: none;
}
</style>
