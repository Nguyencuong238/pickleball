/**
 * bracket-manager.js
 * Alpine.js component for knockout bracket display and generation.
 * Assembles mixins: bracket-data-fetcher, bracket-score-entry, bracket-swap-editor.
 */

function bracketManager(config) {
    return {
        // Config
        tournamentSlug:   config.tournamentSlug,
        dataUrl:          config.dataUrl,
        generateUrl:      config.generateUrl,
        categories:       config.categories || [],
        bracketData:      config.bracketData || {},
        csrf:             config.csrf,

        // State
        activeCategoryId: null,
        rounds:           [],
        loading:          false,
        generating:       false,
        enableThirdPlace: false,
        currentRoundIdx:  0,
        scoreMatchId:     null,
        scoreSets:        [{ s1: 0, s2: 0 }],
        scoreSaving:      false,
        editMode:         false,
        swapState:        { active: false, matchId1: null, slot1: null, name1: '', matchId2: null, slot2: null, name2: '' },

        // Computed
        get hasBracket() {
            return this.rounds.length > 0;
        },

        get mainRounds() {
            return this.rounds.filter(r => r.type !== 'bronze');
        },

        get thirdPlaceMatch() {
            const bronze = this.rounds.find(r => r.type === 'bronze');
            if (!bronze || !bronze.matches || bronze.matches.length === 0) return null;
            return bronze.matches[0];
        },

        get currentRoundName() {
            const rounds = this.mainRounds;
            if (rounds.length === 0) return '';
            const round = rounds[this.currentRoundIdx];
            return round ? round.name : '';
        },

        // Lifecycle
        init() {
            if (this.categories.length > 0) {
                this.selectCategory(this.categories[0].category_id);
            }
        },

        selectCategory(id) {
            this.activeCategoryId = id;
            this.currentRoundIdx  = 0;
            this.rounds           = [];
            this.fetchBracket();
        },

        // Navigation
        prevRound() {
            if (this.currentRoundIdx > 0) {
                this.currentRoundIdx--;
            }
        },

        nextRound() {
            if (this.currentRoundIdx < this.mainRounds.length - 1) {
                this.currentRoundIdx++;
            }
        },

        // Mixins
        ...bracketDataFetcherMixin(),
        ...bracketScoreEntryMixin(),
        ...bracketSwapEditorMixin(),
    };
}
