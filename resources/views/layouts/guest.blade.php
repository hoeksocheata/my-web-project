<!DOCTYPE html>
<html lang="km" data-bs-theme="light">
<head>
    @include('layouts._head')
</head>
<body>
    <div class="auth-shell">
        <aside class="auth-hero">
            <a class="bk-brand text-white" href="{{ route('login') }}">
                <span class="logo" style="background: rgba(255,255,255,.18); box-shadow:none"><i class="bi bi-book-half"></i></span>
                ហាងសៀវភៅរបស់ខ្ញុំ
            </a>

            <div>
                <h1 class="fw-bold mb-3" style="font-size: 2.1rem">ទិញសៀវភៅអនឡាញ<br>ងាយស្រួល និងរហ័ស</h1>
                <p class="mb-4" style="opacity:.85">រើសសៀវភៅដែលអ្នកចូលចិត្ត ហើយបង់លុយតាម QR ក្នុងប៉ុន្មានជំហាន។</p>
                <div class="feat"><i class="bi bi-search"></i><div><div class="fw-semibold">ស្វែងរកសៀវភៅងាយ</div><small style="opacity:.8">តាមចំណងជើង អ្នកនិពន្ធ និងប្រភេទ</small></div></div>
                <div class="feat"><i class="bi bi-lightning-charge"></i><div><div class="fw-semibold">ទិញភ្លាម</div><small style="opacity:.8">ចុចតែម្តង ឃើញ QR បង់លុយភ្លាម</small></div></div>
                <div class="feat"><i class="bi bi-bag-check"></i><div><div class="fw-semibold">តាមដានការកម្មង់</div><small style="opacity:.8">មើលប្រវត្តិ និងស្ថានភាពរាល់ការទិញ</small></div></div>
            </div>

            <small style="opacity:.7">© {{ date('Y') }} ហាងសៀវភៅរបស់ខ្ញុំ</small>
        </aside>

        <section class="auth-form">
            <button type="button" class="btn btn-soft btn-icon rounded-circle auth-theme" data-theme-toggle title="ប្តូរពណ៌ផ្ទាំង">
                <i class="bi bi-moon-stars-fill" data-theme-icon></i>
            </button>
            <div class="inner">
                @yield('content')
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/bookstore.js') }}"></script>
</body>
</html>
