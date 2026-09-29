<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Finclude-AI') — Alternative AI Credit Scoring & Micro-Lending</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/finclude_logo.png') }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
    
    <!-- Icons (Lucide / Boxicons / SVGs) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Finclude-AI Master Design System -->
    <link rel="stylesheet" href="{{ asset('css/finclude.css') }}">
    
    <script>
        // Synchronize and apply theme before DOM renders to prevent flicker
        (function() {
            const savedTheme = localStorage.getItem('finclude_theme_mode') || 'dark';
            if (savedTheme === 'light') {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light');
            } else {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
            }
        })();
    </script>
    @stack('styles')
</head>
<body>
    <div class="app-container @yield('container-class')">
        <!-- Optional App Bar / Header -->
        @hasSection('header')
            @yield('header')
        @endif

        <!-- Flash Messages -->
        @if(session('success'))
            <div style="padding: 14px 16px 0 16px;">
                <div class="alert-banner alert-success animate-fade-in">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div style="padding: 14px 16px 0 16px;">
                <div class="alert-banner alert-danger animate-fade-in">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div style="padding: 14px 16px 0 16px;">
                <div class="alert-banner alert-danger animate-fade-in">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <div>
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Main View Content -->
        @yield('content')
    </div>

    <!-- Toast Notification Container -->
    <div id="toast-container" style="position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); z-index: 999; display: flex; flex-direction: column; gap: 8px; width: 90%; max-width: 420px; pointer-events: none;"></div>

    <script>
        // Global Theme Toggle Function
        function toggleTheme() {
            const isDark = document.documentElement.classList.contains('dark');
            if (isDark) {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light');
                localStorage.setItem('finclude_theme_mode', 'light');
                updateThemeIcons('light');
            } else {
                document.documentElement.classList.remove('light');
                document.documentElement.classList.add('dark');
                localStorage.setItem('finclude_theme_mode', 'dark');
                updateThemeIcons('dark');
            }
        }

        function updateThemeIcons(theme) {
            document.querySelectorAll('.theme-toggle-icon').forEach(icon => {
                if (theme === 'light') {
                    icon.classList.remove('fa-sun');
                    icon.classList.add('fa-moon');
                } else {
                    icon.classList.remove('fa-moon');
                    icon.classList.add('fa-sun');
                }
            });
        }

        // Initialize Theme Toggle Icons on Load
        document.addEventListener('DOMContentLoaded', () => {
            const currentTheme = localStorage.getItem('finclude_theme_mode') || 'dark';
            updateThemeIcons(currentTheme);
        });

        // Toast Notification System
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `alert-banner ${type === 'success' ? 'alert-success' : (type === 'error' ? 'alert-danger' : 'alert-info')} animate-fade-in`;
            toast.style.pointerEvents = 'auto';
            toast.style.boxShadow = '0 10px 25px rgba(0,0,0,0.3)';
            
            const iconClass = type === 'success' ? 'fa-circle-check' : (type === 'error' ? 'fa-circle-exclamation' : 'fa-info-circle');
            toast.innerHTML = `<i class="fa-solid ${iconClass}"></i><span style="flex:1;">${message}</span>`;
            
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
                toast.style.transition = 'all 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }

        // Modal Helpers
        function openModal(id) {
            const modal = document.getElementById(id);
            if (modal) modal.classList.add('active');
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            if (modal) modal.classList.remove('active');
        }

        // Close on backdrop click
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-overlay')) {
                e.target.classList.remove('active');
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
