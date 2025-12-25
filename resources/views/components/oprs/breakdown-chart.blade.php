@props(['breakdown'])

<div class="oprs-breakdown-chart">
    <h3 class="chart-title">Phân Tích OPS</h3>

    <div class="chart-components">
        {{-- Elo Component --}}
        <div class="chart-component">
            <div class="component-header">
                <span class="component-label">Điểm Elo (70%)</span>
                <span class="component-value">{{ number_format($breakdown['elo']['weighted'], 2) }}</span>
            </div>
            <div class="component-bar">
                <div class="component-fill elo-fill" style="width: {{ min(100, ($breakdown['elo']['weighted'] / max(1, $breakdown['total'])) * 100) }}%"></div>
            </div>
            <p class="component-raw">Gốc: {{ $breakdown['elo']['raw'] }} điểm</p>
        </div>

        {{-- Challenge Component --}}
        <div class="chart-component">
            <div class="component-header">
                <span class="component-label">Điểm Thử Thách (20%)</span>
                <span class="component-value">{{ number_format($breakdown['challenge']['weighted'], 2) }}</span>
            </div>
            <div class="component-bar">
                <div class="component-fill challenge-fill" style="width: {{ min(100, ($breakdown['challenge']['weighted'] / max(1, $breakdown['total'])) * 100) }}%"></div>
            </div>
            <p class="component-raw">Gốc: {{ $breakdown['challenge']['raw'] }} điểm</p>
        </div>

        {{-- Community Component --}}
        <div class="chart-component">
            <div class="component-header">
                <span class="component-label">Điểm Cộng Đồng (10%)</span>
                <span class="component-value">{{ number_format($breakdown['community']['weighted'], 2) }}</span>
            </div>
            <div class="component-bar">
                <div class="component-fill community-fill" style="width: {{ min(100, ($breakdown['community']['weighted'] / max(1, $breakdown['total'])) * 100) }}%"></div>
            </div>
            <p class="component-raw">Gốc: {{ $breakdown['community']['raw'] }} điểm</p>
        </div>
    </div>

    {{-- Total --}}
    <div class="chart-total">
        <span class="total-label">Tổng OPS</span>
        <span class="total-value">{{ number_format($breakdown['total'], 0) }}</span>
    </div>
</div>

<style>
.oprs-breakdown-chart {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    padding: 1.5rem;
}

.chart-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 1rem 0;
}

.chart-components {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.chart-component {
    margin-bottom: 0.5rem;
}

.component-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.25rem;
}

.component-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
}

.component-value {
    font-size: 0.875rem;
    color: #64748b;
}

.component-bar {
    width: 100%;
    height: 12px;
    background: #e2e8f0;
    border-radius: 6px;
    overflow: hidden;
}

.component-fill {
    height: 100%;
    border-radius: 6px;
    transition: width 0.3s;
}

.elo-fill {
    background: linear-gradient(90deg, #22c55e, #16a34a);
}

.challenge-fill {
    background: linear-gradient(90deg, #3b82f6, #1d4ed8);
}

.community-fill {
    background: linear-gradient(90deg, #a855f7, #7c3aed);
}

.component-raw {
    font-size: 0.75rem;
    color: #94a3b8;
    margin-top: 0.25rem;
}

.chart-total {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.total-label {
    font-weight: 600;
    color: #1e293b;
}

.total-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #3b82f6;
}
</style>
