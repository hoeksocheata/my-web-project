@extends('layouts.guest')
@section('title', 'ចូលគណនី')
@section('content')
    <h2 class="fw-bold mb-1">សូមស្វាគមន៍ត្រឡប់មកវិញ 👋</h2>
    <p class="text-muted mb-4">ចូលគណនីដើម្បីទិញសៀវភៅ និងមើលការកម្មង់របស់អ្នក</p>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center py-2 small"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
    @endif

    <form action="{{ route('login.post') }}" method="POST" data-once novalidate>
        @csrf
        <div class="mb-3">
            <label class="form-label" for="email">អ៊ីមែល</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" autofocus
                   class="form-control @error('email') is-invalid @enderror" placeholder="you@example.com" required>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">លេខសម្ងាត់</label>
            <div class="pw-wrap">
                <input type="password" id="password" name="password" autocomplete="current-password"
                       class="form-control @error('password') is-invalid @enderror" placeholder="••••••••" required>
                <button type="button" class="pw-toggle" data-pw-toggle tabindex="-1"><i class="bi bi-eye"></i></button>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label small" for="remember">ចងចាំខ្ញុំ</label>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">ចូលប្រព័ន្ធ <i class="bi bi-arrow-right ms-1"></i></button>

        <p class="text-center mt-4 mb-0 small text-muted">មិនទាន់មានគណនី? <a href="{{ route('register') }}" class="fw-semibold text-decoration-none">ចុះឈ្មោះនៅទីនេះ</a></p>
    </form>

    @if(config('bookstore.demo'))
        <div class="mt-4 p-3 rounded-3" style="border:1px dashed var(--bk-border)">
            <div class="small fw-semibold mb-2"><i class="bi bi-rocket-takeoff me-1"></i> Demo — ចូលសាកល្បងលឿន</div>
            <div class="d-flex gap-2">
                @foreach(['admin@example.com' => ['Admin', 'bi-shield-lock', 'btn-outline-primary'], 'user@example.com' => ['អតិថិជន', 'bi-person', 'btn-outline-success']] as $mail => [$label, $icon, $cls])
                    <form action="{{ route('login.post') }}" method="POST" class="flex-fill m-0">@csrf
                        <input type="hidden" name="email" value="{{ $mail }}"><input type="hidden" name="password" value="password">
                        <button class="btn {{ $cls }} w-100 btn-sm"><i class="bi {{ $icon }} me-1"></i> {{ $label }}</button>
                    </form>
                @endforeach
            </div>
        </div>
    @endif
@endsection
