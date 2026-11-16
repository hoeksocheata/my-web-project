@extends('layouts.guest')
@section('title', 'ចុះឈ្មោះ')
@section('content')
    <h2 class="fw-bold mb-1">បង្កើតគណនីថ្មី ✨</h2>
    <p class="text-muted mb-4">បង្កើតគណនីឥតគិតថ្លៃ ដើម្បីចាប់ផ្តើមទិញសៀវភៅ</p>

    <form action="{{ route('register.post') }}" method="POST" data-once novalidate>
        @csrf
        <div class="mb-3">
            <label class="form-label" for="name">ឈ្មោះ</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" autocomplete="name" autofocus
                   class="form-control @error('name') is-invalid @enderror" required>
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="email">អ៊ីមែល</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email"
                   class="form-control @error('email') is-invalid @enderror" placeholder="you@example.com" required>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">លេខសម្ងាត់</label>
            <div class="pw-wrap">
                <input type="password" id="password" name="password" autocomplete="new-password"
                       class="form-control @error('password') is-invalid @enderror" required>
                <button type="button" class="pw-toggle" data-pw-toggle tabindex="-1"><i class="bi bi-eye"></i></button>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="form-text">យ៉ាងតិច ៦ តួអក្សរ</div>
        </div>

        <div class="mb-4">
            <label class="form-label" for="password_confirmation">បញ្ជាក់លេខសម្ងាត់</label>
            <div class="pw-wrap">
                <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" class="form-control" required>
                <button type="button" class="pw-toggle" data-pw-toggle tabindex="-1"><i class="bi bi-eye"></i></button>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">បង្កើតគណនី</button>

        <p class="text-center mt-4 mb-0 small text-muted">មានគណនីរួចហើយ? <a href="{{ route('login') }}" class="fw-semibold text-decoration-none">ចូលគណនី</a></p>
    </form>
@endsection
