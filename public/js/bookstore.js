/* BookStore — small UI helpers (no build step needed) */
(function () {
    'use strict';

    /* ---- Theme (light / dark) ---- */
    var root = document.documentElement;
    function applyTheme(t) {
        root.setAttribute('data-bs-theme', t);
        document.querySelectorAll('[data-theme-icon]').forEach(function (i) {
            i.className = 'bi ' + (t === 'dark' ? 'bi-sun-fill' : 'bi-moon-stars-fill');
        });
    }
    document.addEventListener('DOMContentLoaded', function () {
        applyTheme(root.getAttribute('data-bs-theme') || 'light');
        document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var next = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
                applyTheme(next);
                try { localStorage.setItem('bk-theme', next); } catch (e) {}
            });
        });

        /* ---- Toasts from flash messages ---- */
        document.querySelectorAll('.toast').forEach(function (el) {
            new bootstrap.Toast(el, { delay: 4500 }).show();
        });

        /* ---- Delete confirmation modal ---- */
        var modalEl = document.getElementById('confirmDelete');
        if (modalEl) {
            modalEl.addEventListener('show.bs.modal', function (ev) {
                var b = ev.relatedTarget;
                if (!b) return;
                modalEl.querySelector('form').setAttribute('action', b.getAttribute('data-action'));
                modalEl.querySelector('[data-name]').textContent = b.getAttribute('data-name') || '';
            });
        }

        /* ---- Auto-submit filter selects ---- */
        document.querySelectorAll('[data-autosubmit]').forEach(function (el) {
            el.addEventListener('change', function () { el.form.submit(); });
        });

        /* ---- Show / hide password ---- */
        document.querySelectorAll('[data-pw-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = btn.parentElement.querySelector('input');
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.querySelector('i').className = 'bi ' + (show ? 'bi-eye-slash' : 'bi-eye');
            });
        });

        /* ---- Image upload preview + drag & drop ---- */
        document.querySelectorAll('[data-upload]').forEach(function (box) {
            var input = box.querySelector('input[type=file]');
            var preview = box.querySelector('[data-preview]');
            var hint = box.querySelector('[data-hint]');
            function show(file) {
                if (!file || !file.type.startsWith('image/')) return;
                var r = new FileReader();
                r.onload = function (e) {
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                    if (hint) hint.classList.add('d-none');
                };
                r.readAsDataURL(file);
            }
            box.addEventListener('click', function (e) { if (e.target !== input) input.click(); });
            input.addEventListener('change', function () { show(input.files[0]); });
            ['dragenter', 'dragover'].forEach(function (n) {
                box.addEventListener(n, function (e) { e.preventDefault(); box.classList.add('drag'); });
            });
            ['dragleave', 'drop'].forEach(function (n) {
                box.addEventListener(n, function (e) { e.preventDefault(); box.classList.remove('drag'); });
            });
            box.addEventListener('drop', function (e) {
                if (e.dataTransfer.files.length) {
                    input.files = e.dataTransfer.files;
                    show(input.files[0]);
                }
            });
        });


        /* ---- កណ្ដឹងជូនដំណឹងសម្រាប់ Admin: ពិនិត្យរាល់ ១៥ វិនាទី + toast + សំឡេង ---- */
        var bell = document.getElementById('bell');
        if (bell) {
            var seen = null, baseTitle = document.title;
            var esc = function (t) { var d = document.createElement('div'); d.textContent = t == null ? '' : t; return d.innerHTML; };
            var beep = function () {
                try {
                    var C = window.AudioContext || window.webkitAudioContext, c = new C(), o = c.createOscillator(), g = c.createGain();
                    o.type = 'sine'; o.frequency.value = 880; g.gain.value = 0.08;
                    o.connect(g); g.connect(c.destination); o.start();
                    setTimeout(function () { o.frequency.value = 1175; }, 140);
                    setTimeout(function () { o.stop(); c.close(); }, 320);
                } catch (e) {}
            };
            var popToast = function (n) {
                var wrap = document.querySelector('.toast-wrap'); if (!wrap) return;
                var el = document.createElement('div');
                el.className = 'toast align-items-center text-bg-primary'; el.setAttribute('role', 'alert');
                el.innerHTML = '<div class="d-flex"><a href="' + esc(n.url) + '" class="toast-body text-white text-decoration-none flex-grow-1"><i class="bi ' + esc(n.icon) + ' me-2"></i><b>' + esc(n.title) + '</b><br><small>' + esc(n.body) + '</small></a><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
                wrap.appendChild(el); new bootstrap.Toast(el, { delay: 9000 }).show();
            };
            var render = function (d) {
                var badge = bell.querySelector('[data-bell-count]');
                badge.textContent = d.unread; badge.classList.toggle('d-none', !d.unread);
                document.querySelectorAll('[data-pending-badge]').forEach(function (b) { b.textContent = d.pending; b.classList.toggle('d-none', !d.pending); });
                var list = bell.querySelector('[data-bell-list]');
                list.innerHTML = d.latest.length ? d.latest.map(function (n) {
                    return '<a href="' + esc(n.url) + '" class="dropdown-item d-flex gap-2 py-2 ' + (n.read ? '' : 'bg-primary-subtle') + '" style="white-space:normal"><i class="bi ' + esc(n.icon) + ' mt-1"></i><div><div class="fw-semibold small">' + esc(n.title) + '</div><div class="small text-muted">' + esc(n.body) + '</div><div class="small text-muted">' + esc(n.time) + '</div></div></a>';
                }).join('') : '<div class="text-center text-muted py-4 small">មិនទាន់មានការជូនដំណឹង</div>';
                document.title = (d.unread ? '(' + d.unread + ') ' : '') + baseTitle;
            };
            var poll = function () {
                fetch(bell.getAttribute('data-poll-url'), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                    .then(function (r) { return r.ok ? r.json() : null; })
                    .then(function (d) {
                        if (!d) return;
                        var fresh = d.latest.filter(function (n) { return !n.read && seen && !seen.has(n.id); });
                        render(d);
                        if (fresh.length) { fresh.forEach(popToast); beep(); }
                        seen = new Set(d.latest.map(function (n) { return n.id; }));
                    }).catch(function () {});
            };
            poll();
            setInterval(function () { if (!document.hidden) poll(); }, 15000);
            document.addEventListener('visibilitychange', function () { if (!document.hidden) poll(); });
        }

        /* ---- Prevent double submit ---- */
        document.querySelectorAll('form[data-once]').forEach(function (f) {
            f.addEventListener('submit', function () {
                var b = f.querySelector('button[type=submit]');
                if (b) { b.disabled = true; b.insertAdjacentHTML('afterbegin', '<span class="spinner-border spinner-border-sm me-2"></span>'); }
            });
        });
    });
})();
