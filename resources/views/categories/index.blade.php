@extends('layouts.app')
@section('title', 'ប្រភេទសៀវភៅ')
@section('content')
<div class="page-head">
    <div>
        <h1>🏷️ ប្រភេទសៀវភៅ</h1>
        <p>គ្រប់គ្រងប្រភេទ ដើម្បីរៀបចំ និងត្រងសៀវភៅបានងាយ</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="bk-card">
            <div class="bk-card-header"><h6 class="mb-0 fw-bold">បន្ថែមប្រភេទថ្មី</h6></div>
            <div class="bk-card-body">
                <form action="{{ route('categories.store') }}" method="POST" data-once novalidate>
                    @csrf
                    <label class="form-label" for="name">ឈ្មោះប្រភេទ</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" maxlength="100"
                           class="form-control mb-3 @error('name') is-invalid @enderror" placeholder="ឧ. ប្រលោមលោក" required>
                    @error('name') <div class="invalid-feedback mb-3 d-block">{{ $message }}</div> @enderror
                    <button class="btn btn-primary w-100" type="submit"><i class="bi bi-plus-lg me-1"></i> បន្ថែម</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="bk-card">
            <div class="bk-card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">បញ្ជីប្រភេទ</h6>
                <span class="badge-soft muted">{{ $categories->count() }} ប្រភេទ</span>
            </div>

            @if($categories->isEmpty())
                <div class="empty"><i class="bi bi-tags"></i><p class="mt-3 mb-0">មិនទាន់មានប្រភេទទេ</p></div>
            @else
                <div class="table-responsive">
                    <table class="table bk-table">
                        <thead><tr><th>ឈ្មោះ</th><th>ចំនួនសៀវភៅ</th><th class="text-end">សកម្មភាព</th></tr></thead>
                        <tbody>
                            @foreach($categories as $c)
                                <tr>
                                    <td>
                                        <form action="{{ route('categories.update', $c) }}" method="POST" class="d-flex gap-2 m-0">
                                            @csrf @method('PUT')
                                            <input type="text" name="name" value="{{ $c->name }}" maxlength="100" class="form-control form-control-sm" style="max-width: 260px" required>
                                            <button class="btn btn-soft btn-sm" type="submit" title="រក្សាទុក"><i class="bi bi-check-lg"></i></button>
                                        </form>
                                    </td>
                                    <td><a href="{{ route('books.index', ['category' => $c->id]) }}" class="text-decoration-none">{{ $c->books_count }} ក្បាល</a></td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-outline-danger btn-icon" data-bs-toggle="modal" data-bs-target="#confirmDelete"
                                                data-action="{{ route('categories.destroy', $c) }}" data-name="{{ $c->name }}"><i class="bi bi-trash3"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
