@extends('layouts.app')
@section('title', 'ការជូនដំណឹង')
@section('content')
<div class="page-head">
    <div><h1>🔔 ការជូនដំណឹង</h1><p>ការកម្មង់ថ្មី និងការបង់ប្រាក់ពីអតិថិជន</p></div>
    <form action="{{ route('admin.notifications.readAll') }}" method="POST">@csrf
        <button class="btn btn-outline-secondary"><i class="bi bi-check2-all me-1"></i> សម្គាល់ថាបានអានទាំងអស់</button></form>
</div>
<div class="bk-card">
    @forelse($notifications as $n)
        <a href="{{ route('admin.notifications.open', $n->id) }}" class="pos-item text-decoration-none text-reset {{ $n->read_at ? '' : 'bg-primary-subtle' }}">
            <span class="stat-icon tone-indigo" style="width:2.4rem;height:2.4rem;font-size:1.1rem"><i class="bi {{ $n->data['icon'] ?? 'bi-bell' }}"></i></span>
            <div class="flex-grow-1"><div class="fw-semibold">{{ $n->data['title'] ?? '' }}</div><small class="text-muted">{{ $n->data['body'] ?? '' }}</small></div>
            <small class="text-muted text-nowrap">{{ $n->created_at->diffForHumans() }}</small>
        </a>
    @empty
        <div class="empty"><i class="bi bi-bell-slash"></i><h5 class="mt-3 text-body">មិនទាន់មានការជូនដំណឹង</h5></div>
    @endforelse
    @if($notifications->hasPages())<div class="bk-card-body border-top d-flex justify-content-end" style="border-color: var(--bk-border) !important"><div class="bk-pager">{{ $notifications->links() }}</div></div>@endif
</div>
@endsection
