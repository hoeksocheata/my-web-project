@extends('layouts.app')
@section('title', 'របាយការណ៍')
@section('content')
<div class="page-head">
    <div><h1>📈 របាយការណ៍លក់</h1><p>ចំណូល និងសៀវភៅលក់ដាច់បំផុត ក្នុងរយៈពេល {{ $days }} ថ្ងៃចុងក្រោយ</p></div>
    <div class="btn-group">
        @foreach([7 => '៧ ថ្ងៃ', 30 => '៣០ ថ្ងៃ', 90 => '៩០ ថ្ងៃ'] as $d => $label)
            <a href="{{ route('reports.index', ['days' => $d]) }}" class="btn btn-outline-primary {{ $days === $d ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="bk-card stat"><span class="stat-icon tone-green"><i class="bi bi-cash-stack"></i></span>
        <div><p class="stat-label">ចំណូលសរុប</p><p class="stat-value">${{ number_format($stats['revenue'], 2) }}</p></div></div></div>
    <div class="col-6 col-lg-3"><div class="bk-card stat"><span class="stat-icon tone-indigo"><i class="bi bi-receipt"></i></span>
        <div><p class="stat-label">ចំនួនវិក្កយបត្រ</p><p class="stat-value">{{ number_format($stats['orders']) }}</p></div></div></div>
    <div class="col-6 col-lg-3"><div class="bk-card stat"><span class="stat-icon tone-amber"><i class="bi bi-journal-check"></i></span>
        <div><p class="stat-label">សៀវភៅបានលក់</p><p class="stat-value">{{ number_format($stats['items']) }} <small>ក្បាល</small></p></div></div></div>
    <div class="col-6 col-lg-3"><div class="bk-card stat"><span class="stat-icon tone-red"><i class="bi bi-graph-up-arrow"></i></span>
        <div><p class="stat-label">មធ្យមក្នុង១វិក្កយបត្រ</p><p class="stat-value">${{ number_format($stats['average'], 2) }}</p></div></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="bk-card">
            <div class="bk-card-header"><h6 class="mb-0 fw-bold">ចំណូលប្រចាំថ្ងៃ ($)</h6></div>
            <div class="bk-card-body"><div style="position:relative;height:320px"><canvas id="revChart"></canvas></div></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="bk-card h-100">
            <div class="bk-card-header"><h6 class="mb-0 fw-bold">🏆 លក់ដាច់ទាំង ៥</h6></div>
            @forelse($topBooks as $i => $b)
                <div class="pos-item">
                    <span class="stat-icon tone-indigo" style="width:2.2rem;height:2.2rem;font-size:1rem;font-weight:700">{{ $i + 1 }}</span>
                    <div class="flex-grow-1"><div class="fw-semibold line-clamp-2">{{ $b->title }}</div><small class="text-muted">${{ number_format($b->revenue, 2) }}</small></div>
                    <span class="badge-soft success">{{ $b->qty }} ក្បាល</span>
                </div>
            @empty
                <div class="empty py-5"><i class="bi bi-bar-chart"></i><p class="mt-2 mb-0">មិនទាន់មានទិន្នន័យលក់</p></div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    var labels = @json($labels), revenue = @json($revenue), orders = @json($orders);
    var dark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    var grid = dark ? 'rgba(255,255,255,.08)' : 'rgba(0,0,0,.06)', text = dark ? '#9aa1b9' : '#6b7280';
    new Chart(document.getElementById('revChart'), {
        type: 'bar',
        data: { labels: labels, datasets: [{ label: 'ចំណូល ($)', data: revenue, backgroundColor: '#4f46e5', borderRadius: 6, maxBarThickness: 28 }] },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { callbacks: {
                label: function (c) { return ' $' + c.parsed.y.toFixed(2) + ' · ' + orders[c.dataIndex] + ' វិក្កយបត្រ'; } } } },
            scales: { x: { grid: { display: false }, ticks: { color: text, maxTicksLimit: 12 } },
                      y: { beginAtZero: true, grid: { color: grid }, ticks: { color: text, callback: function (v) { return '$' + v; } } } }
        }
    });
})();
</script>
@endpush
