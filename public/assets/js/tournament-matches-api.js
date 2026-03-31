/**
 * tournament-matches-api.js
 * Fetch helpers for match management (load, score, delete, generate).
 */

const MatchesApi = {
    async loadMatches(indexUrl, categoryId, filter) {
        const params = new URLSearchParams();
        if (categoryId) params.set('category', categoryId);
        if (filter && filter !== 'all') params.set('status', filter);

        const res = await fetch(`${indexUrl}?${params}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        });
        return res.json();
    },

    async submitScore(scoreUrl, csrf, sets, status) {
        const res = await fetch(scoreUrl, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ sets, status }),
        });
        return res.json();
    },

    async deleteMatch(deleteUrl, csrf) {
        const res = await fetch(deleteUrl, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        return res.json();
    },

    async updateSchedule(url, csrf, matchDate, matchTime) {
        const res = await fetch(url, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ match_date: matchDate || null, match_time: matchTime || null }),
        });
        return res.json();
    },

    async updateMatchNumber(url, csrf, matchNumber) {
        const res = await fetch(url, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ match_number: matchNumber }),
        });
        return res.json();
    },

    async generateMatches(createGroupsUrl, csrf, categoryId, bestOf) {
        const res = await fetch(createGroupsUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ category_id: categoryId, best_of: bestOf }),
        });
        return res.json();
    },
};
