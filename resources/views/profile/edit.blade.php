@extends('layouts.app')
@section('title', 'ព័ត៌មានគណនី')
@section('content')
<div class="page-head"><div><h1>👤 ព័ត៌មានគណនី</h1><p>កែឈ្មោះ អ៊ីមែល និងលេខសម្ងាត់</p></div></div>
<div class="row"><div class="col-lg-6">
<div class="bk-card"><form action="{{ route('profile.update') }}" method="POST" class="bk-card-body" data-once novalidate>
    @csrf @method('PUT')
    <div class="d-flex align-items-center gap-3 mb-4">
        <span class="avatar" style="width:3.2rem;height:3.2rem;font-size:1.3rem">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
        <div><div class="fw-bold">{{ $user->name }}</div><span class="role-pill role-{{ $user->isAdmin() ? 'admin' : 'user' }}">{{ $user->role }}</span></div>
    </div>
    <label class="form-label" for="name">ឈ្មោះ</label>
    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="form-control mb-1 @error('name') is-invalid @enderror" required>
    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    <label class="form-label mt-3" for="email">អ៊ីមែល</label>
    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-control mb-1 @error('email') is-invalid @enderror" required>
    @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

    <hr class="my-4" style="border-color: var(--bk-border)">
    <div class="fw-semibold mb-2">ប្តូរលេខសម្ងាត់ <small class="text-muted fw-normal">(ទុកទទេ បើមិនចង់ប្តូរ)</small></div>
    <label class="form-label" for="current_password">លេខសម្ងាត់បច្ចុប្បន្ន</label>
    <input type="password" id="current_password" name="current_password" autocomplete="current-password" class="form-control mb-1 @error('current_password') is-invalid @enderror">
    @error('current_password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    <label class="form-label mt-3" for="password">លេខសម្ងាត់ថ្មី</label>
    <input type="password" id="password" name="password" autocomplete="new-password" class="form-control mb-1 @error('password') is-invalid @enderror">
    @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    <label class="form-label mt-3" for="password_confirmation">បញ្ជាក់លេខសម្ងាត់ថ្មី</label>
    <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" class="form-control mb-4">
    <button class="btn btn-primary px-4"><i class="bi bi-check2-circle me-1"></i> រក្សាទុក</button>
</form></div></div></div>
@endsection
