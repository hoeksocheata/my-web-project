{{-- $items: ការជូនដំណឹង (DatabaseNotification collection) --}}
@forelse($items as $n)
    <a href="{{ route('admin.notifications.open', $n->id) }}" class="dropdown-item d-flex gap-2 py-2 {{ $n->read_at ? '' : 'bg-primary-subtle' }}" style="white-space: normal">
        <i class="bi {{ $n->data['icon'] ?? 'bi-bell' }} mt-1"></i>
        <div>
            <div class="fw-semibold small">{{ $n->data['title'] ?? '' }}</div>
            <div class="small text-muted">{{ $n->data['body'] ?? '' }}</div>
            <div class="small text-muted">{{ $n->created_at->diffForHumans() }}</div>
        </div>
    </a>
@empty
    <div class="text-center text-muted py-4 small">មិនទាន់មានការជូនដំណឹង</div>
@endforelse
