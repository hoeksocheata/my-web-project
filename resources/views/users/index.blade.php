@extends('layouts.app')
@section('title', 'អ្នកប្រើប្រាស់')
@section('content')
<div class="page-head">
    <div><h1>👥 គ្រប់គ្រងអ្នកប្រើប្រាស់</h1><p>កំណត់តួនាទី Admin / អតិថិជន ឬលុបគណនី</p></div>
</div>

<div class="bk-card">
    <div class="bk-card-header toolbar">
        <form method="GET" action="{{ route('users.index') }}" class="row g-2">
            <div class="col-12 col-md-6">
                <div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="ស្វែងរកឈ្មោះ ឬអ៊ីមែល..."></div>
            </div>
            <div class="col-12 col-md-auto"><button class="btn btn-primary" type="submit">ស្វែងរក</button></div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table bk-table">
            <thead><tr><th>អ្នកប្រើ</th><th>តួនាទី</th><th>ចុះឈ្មោះ</th><th class="text-end">សកម្មភាព</th></tr></thead>
            <tbody>
            @forelse($users as $u)
                @php $me = $u->id === auth()->id(); @endphp
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <span class="avatar">{{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}</span>
                            <div><div class="fw-semibold">{{ $u->name }} @if($me)<span class="badge-soft muted ms-1">អ្នក</span>@endif</div>
                                <small class="text-muted">{{ $u->email }}</small></div>
                        </div>
                    </td>
                    <td><span class="role-pill role-{{ $u->isAdmin() ? 'admin' : 'user' }}">{{ $u->role }}</span></td>
                    <td>{{ $u->created_at?->format('d/m/Y') }}</td>
                    <td class="text-end text-nowrap">
                        @unless($me)
                            <form action="{{ route('users.role', $u) }}" method="POST" class="d-inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="role" value="{{ $u->isAdmin() ? 'user' : 'admin' }}">
                                <button type="submit" class="btn btn-soft btn-sm">
                                    <i class="bi bi-arrow-left-right me-1"></i> ប្តូរជា {{ $u->isAdmin() ? 'user' : 'admin' }}
                                </button>
                            </form>
                            <button type="button" class="btn btn-outline-danger btn-icon" data-bs-toggle="modal" data-bs-target="#confirmDelete"
                                    data-action="{{ route('users.destroy', $u) }}" data-name="{{ $u->name }}"><i class="bi bi-trash3"></i></button>
                        @endunless
                    </td>
                </tr>
            @empty
                <tr><td colspan="4"><div class="empty py-4">រកមិនឃើញអ្នកប្រើប្រាស់</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
        <div class="bk-card-body border-top d-flex justify-content-end" style="border-color: var(--bk-border) !important"><div class="bk-pager">{{ $users->links() }}</div></div>
    @endif
</div>
@endsection
