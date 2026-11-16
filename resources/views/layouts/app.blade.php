<!DOCTYPE html>
<html lang="km" data-bs-theme="light">
<head>
    @include('layouts._head')
</head>
<body>
    @php
        $navLinks = [['route' => 'books.index', 'match' => 'books.*', 'icon' => 'bi-grid-1x2', 'label' => 'សៀវភៅ']];
        if (auth()->user()?->isAdmin()) {
            $navLinks[] = ['route' => 'sales.index', 'match' => 'sales.*', 'icon' => 'bi-receipt', 'label' => 'ការលក់'];
            $navLinks[] = ['route' => 'admin.orders.index', 'match' => 'admin.orders.*', 'icon' => 'bi-bag-check', 'label' => 'ការកម្មង់',
                           'badge' => \App\Models\Order::where('status', 'pending')->count()];
            $navLinks[] = ['icon' => 'bi-boxes', 'label' => 'ស្តុក', 'children' => [
                ['route' => 'purchases.index', 'match' => 'purchases.*', 'icon' => 'bi-box-arrow-in-down', 'label' => 'ទិញចូល / បំពេញស្តុក'],
                ['route' => 'suppliers.index', 'match' => 'suppliers.*', 'icon' => 'bi-truck', 'label' => 'អ្នកផ្គត់ផ្គង់'],
                ['route' => 'categories.index', 'match' => 'categories.*', 'icon' => 'bi-tags', 'label' => 'ប្រភេទ'],
            ]];
            $navLinks[] = ['route' => 'reports.index', 'match' => 'reports.*', 'icon' => 'bi-bar-chart-line', 'label' => 'របាយការណ៍'];
            $navLinks[] = ['route' => 'users.index', 'match' => 'users.*', 'icon' => 'bi-people', 'label' => 'អ្នកប្រើ'];
        } elseif (auth()->check()) {
            $navLinks[] = ['route' => 'wishlist.index', 'match' => 'wishlist.*', 'icon' => 'bi-heart', 'label' => 'ចូលចិត្ត'];
            $navLinks[] = ['route' => 'cart.index', 'match' => 'cart.*', 'icon' => 'bi-cart3', 'label' => 'កន្ត្រក', 'badge' => count(session('cart', []))];
            $navLinks[] = ['route' => 'orders.index', 'match' => 'orders.*', 'icon' => 'bi-bag', 'label' => 'ការកម្មង់របស់ខ្ញុំ'];
        }
        // បញ្ចូលតំណរងទៅក្នុងបញ្ជីតែមួយ សម្រាប់ម៉ឺនុយទូរស័ព្ទ
        $flatLinks = collect($navLinks)->flatMap(fn ($l) => $l['children'] ?? [$l])->all();
    @endphp
    <nav class="bk-nav">
        <div class="container py-2 d-flex align-items-center gap-3">
            <a class="bk-brand" href="{{ route('books.index') }}">
                <span class="logo"><i class="bi bi-book-half"></i></span>
                <span class="d-none d-xl-inline">ហាងសៀវភៅរបស់ខ្ញុំ</span>
            </a>

            <ul class="nav ms-2 d-none d-lg-flex">
                @foreach($navLinks as $l)
                    @if(isset($l['children']))
                        @php $open = collect($l['children'])->contains(fn ($c) => request()->routeIs($c['match'])); @endphp
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ $open ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi {{ $l['icon'] }} me-1"></i> {{ $l['label'] }}
                            </a>
                            <ul class="dropdown-menu shadow border-0 p-2">
                                @foreach($l['children'] as $c)
                                    <li><a class="dropdown-item rounded {{ request()->routeIs($c['match']) ? 'active' : '' }}" href="{{ route($c['route']) }}"><i class="bi {{ $c['icon'] }} me-2"></i>{{ $c['label'] }}</a></li>
                                @endforeach
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs($l['match']) ? 'active' : '' }}" href="{{ route($l['route']) }}">
                                <i class="bi {{ $l['icon'] }} me-1"></i> {{ $l['label'] }}
                                @if(!empty($l['badge']) || $l['route'] === 'admin.orders.index')<span class="badge rounded-pill bg-danger ms-1 {{ empty($l['badge']) ? 'd-none' : '' }}" @if($l['route'] === 'admin.orders.index') data-pending-badge @endif>{{ $l['badge'] ?? 0 }}</span>@endif
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>

            <div class="ms-auto d-flex align-items-center gap-2">
                @if(auth()->user()?->isAdmin())
                    @php $unread = auth()->user()->unreadNotifications()->count(); @endphp
                    <div class="dropdown" id="bell" data-poll-url="{{ route('admin.notifications.poll') }}">
                        <button type="button" class="btn btn-soft btn-icon rounded-circle position-relative" data-bs-toggle="dropdown" data-bs-auto-close="outside" title="ការជូនដំណឹង">
                            <i class="bi bi-bell"></i>
                            <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle {{ $unread ? '' : 'd-none' }}" data-bell-count>{{ $unread }}</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow border-0 p-0" style="width: 340px; max-width: 92vw">
                            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                <span class="fw-bold">ការជូនដំណឹង</span>
                                <form action="{{ route('admin.notifications.readAll') }}" method="POST" class="m-0">@csrf<button class="btn btn-link btn-sm p-0 text-decoration-none">អានទាំងអស់</button></form>
                            </div>
                            <div data-bell-list style="max-height: 360px; overflow-y: auto">
                                @include('admin.notifications._items', ['items' => auth()->user()->notifications()->limit(6)->get()])
                            </div>
                            <a class="dropdown-item text-center small py-2 border-top" href="{{ route('admin.notifications.index') }}">មើលទាំងអស់</a>
                        </div>
                    </div>
                @endif
                <button type="button" class="btn btn-soft btn-icon rounded-circle" data-theme-toggle title="ប្តូរពណ៌ផ្ទាំង">
                    <i class="bi bi-moon-stars-fill" data-theme-icon></i>
                </button>

                @auth
                    <div class="dropdown">
                        <button class="btn p-0 d-flex align-items-center gap-2 border-0" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                            <span class="d-none d-xl-flex flex-column text-start lh-sm">
                                <span class="fw-semibold small">{{ auth()->user()->name }}</span>
                                <span class="role-pill role-{{ auth()->user()->isAdmin() ? 'admin' : 'user' }} align-self-start mt-1">{{ auth()->user()->role }}</span>
                            </span>
                            <i class="bi bi-chevron-down small text-muted"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="min-width: 220px">
                            <li class="px-2 py-1">
                                <div class="fw-semibold">{{ auth()->user()->name }}</div>
                                <div class="small text-muted">{{ auth()->user()->email }}</div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            @foreach($flatLinks as $l)
                                <li class="d-lg-none">
                                    <a class="dropdown-item rounded" href="{{ route($l['route']) }}"><i class="bi {{ $l['icon'] }} me-2"></i>{{ $l['label'] }}@if(!empty($l['badge']) || $l['route'] === 'admin.orders.index')<span class="badge rounded-pill bg-danger ms-2 {{ empty($l['badge']) ? 'd-none' : '' }}" @if($l['route'] === 'admin.orders.index') data-pending-badge @endif>{{ $l['badge'] ?? 0 }}</span>@endif</a>
                                </li>
                            @endforeach
                            <li><a class="dropdown-item rounded" href="{{ route('profile.edit') }}"><i class="bi bi-person-gear me-2"></i>ព័ត៌មានគណនី</a></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="dropdown-item rounded text-danger"><i class="bi bi-box-arrow-right me-2"></i>ចាកចេញ</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    <main class="container py-4">
        @yield('content')
    </main>

    <footer class="bk-foot container">© {{ date('Y') }} ហាងសៀវភៅរបស់ខ្ញុំ · BookStore Management System</footer>

    {{-- Toasts (សារជូនដំណឹង) --}}
    <div class="toast-wrap">
        @foreach(['success' => ['text-bg-success', 'bi-check-circle-fill'], 'error' => ['text-bg-danger', 'bi-exclamation-triangle-fill']] as $key => [$cls, $icon])
            @if(session($key))
                <div class="toast align-items-center {{ $cls }}" role="alert">
                    <div class="d-flex">
                        <div class="toast-body"><i class="bi {{ $icon }} me-2"></i>{{ session($key) }}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    {{-- Modal បញ្ជាក់ការលុប (ប្រើរួមគ្នា) --}}
    <div class="modal fade" id="confirmDelete" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form method="POST" action="">
                    @csrf @method('DELETE')
                    <div class="modal-body text-center p-4">
                        <div class="stat-icon tone-red mx-auto mb-3"><i class="bi bi-trash3"></i></div>
                        <h5 class="fw-bold">តើអ្នកពិតជាចង់លុបមែនទេ?</h5>
                        <p class="text-muted mb-4">«<span data-name></span>» នឹងត្រូវលុបជាអចិន្ត្រៃយ៍ ហើយមិនអាចយកមកវិញបានទេ។</p>
                        <div class="d-flex gap-2 justify-content-center">
                            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">បោះបង់</button>
                            <button type="submit" class="btn btn-danger px-4">លុប</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/bookstore.js') }}"></script>
    @stack('scripts')
</body>
</html>
