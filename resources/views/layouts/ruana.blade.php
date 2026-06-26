<!DOCTYPE html>
<html lang="en" class="dark scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Ruana Manwha')</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind (Using CDN for quick prototype/rebuild as requested) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        lunar: {
                            base: '#050505',
                            card: '#0d0d0d',
                            accent: '#7b7bff',
                            neon: '#00d573',
                            border: '#1a1a1a'
                        }
                    },
                    fontFamily: {
                        orbitron: ['Orbitron', 'sans-serif'],
                        inter: ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #050505;
            color: #ffffff;
            font-family: 'Inter', sans-serif;
        }

        .glass {
            background: rgba(13, 13, 13, 0.6);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .lunar-gradient {
            background: linear-gradient(135deg, #7b7bff 0%, #4b4bff 100%);
        }

        .neon-glow {
            box-shadow: 0 0 15px rgba(0, 213, 115, 0.3);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #050505;
        }
        ::-webkit-scrollbar-thumb {
            background: #1a1a1a;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #252525;
        }

        .text-glow-purple {
            text-shadow: 0 0 20px rgba(123, 123, 255, 0.5);
        }

        .transition-soft {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .liquid-glass {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px) saturate(160%);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .liquid-glass-success {
            box-shadow: 0 8px 32px 0 rgba(0, 213, 115, 0.15), inset 0 0 20px rgba(0, 213, 115, 0.05);
            border-color: rgba(0, 213, 115, 0.2);
        }

        .liquid-glass-error {
            box-shadow: 0 8px 32px 0 rgba(239, 68, 68, 0.15), inset 0 0 20px rgba(239, 68, 68, 0.05);
            border-color: rgba(239, 68, 68, 0.2);
        }

        .liquid-glass-info {
            box-shadow: 0 8px 32px 0 rgba(123, 123, 255, 0.15), inset 0 0 20px rgba(123, 123, 255, 0.05);
            border-color: rgba(123, 123, 255, 0.2);
        }
    </style>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    @yield('styles')
</head>
<body class="overflow-x-hidden" x-data="{ 
    notifications: [],
    addNotification(message, type = 'info') {
        const id = Date.now();
        this.notifications.push({ id, message, type });
        setTimeout(() => {
            this.notifications = this.notifications.filter(n => n.id !== id);
        }, 5000);
    },
    init() {
        window.addNotification = (message, type = 'info') => this.addNotification(message, type);
        @if(session('success'))
            this.addNotification('{{ session('success') }}', 'success');
        @endif
        @if(session('error'))
            this.addNotification('{{ session('error') }}', 'error');
        @endif
        @if(session('status'))
            this.addNotification('{{ session('status') }}', 'success');
        @endif
    }
}">

    <!-- Notifications -->
    <div class="fixed bottom-10 right-10 z-[1000] flex flex-col gap-4">
        <template x-for="n in notifications" :key="n.id">
            <div x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="translate-y-10 opacity-0 scale-90"
                 x-transition:enter-end="translate-y-0 opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="translate-x-0 opacity-100"
                 x-transition:leave-end="translate-x-full opacity-0"
                 class="px-8 py-5 rounded-[2rem] flex items-center gap-5 liquid-glass relative overflow-hidden group"
                 :class="{
                    'liquid-glass-success': n.type === 'success',
                    'liquid-glass-error': n.type === 'error',
                    'liquid-glass-info': n.type === 'info'
                 }">
                <!-- Reflection Highlight -->
                <div class="absolute -top-1/2 -left-1/2 w-full h-full bg-white/5 rotate-45 pointer-events-none"></div>

                <div class="relative">
                    <div class="w-3 h-3 rounded-full shadow-[0_0_15px_rgba(255,255,255,0.5)]" :class="{
                        'bg-green-500 shadow-green-500/50': n.type === 'success',
                        'bg-red-500 shadow-red-500/50': n.type === 'error',
                        'bg-lunar-accent shadow-lunar-accent/50': n.type === 'info'
                    }"></div>
                    <div class="absolute inset-0 w-3 h-3 rounded-full animate-ping opacity-50" :class="{
                        'bg-green-500': n.type === 'success',
                        'bg-red-500': n.type === 'error',
                        'bg-lunar-accent': n.type === 'info'
                    }"></div>
                </div>

                <span class="text-[10px] font-black uppercase tracking-[0.2em] relative z-10" :class="{
                    'text-green-400': n.type === 'success',
                    'text-red-400': n.type === 'error',
                    'text-lunar-accent': n.type === 'info'
                }" x-text="n.message"></span>

                <button @click="notifications = notifications.filter(notif => notif.id !== n.id)" class="ml-4 opacity-30 hover:opacity-100 transition-soft relative z-10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </template>
    </div>

    <!-- Navbar -->
    <nav class="fixed top-0 left-0 w-full z-[100] glass transition-transform duration-500">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            
            <a href="/" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl lunar-gradient flex items-center justify-center text-white shadow-lg group-hover:rotate-12 transition-soft">
                    <span class="text-xl font-bold">R</span>
                </div>
                <span class="text-2xl font-black font-orbitron tracking-tighter group-hover:text-lunar-accent transition-soft">
                    RUANA<span class="text-lunar-accent italic">MANWHA</span>
                </span>
            </a>

            <div class="hidden md:flex items-center gap-8 font-medium text-gray-400">
                <a href="/" class="hover:text-white transition-soft">Home</a>
                <a href="/explore" class="hover:text-white transition-soft">Explore</a>
                <a href="/bookmarks" class="hover:text-white transition-soft">Bookmark</a>
            </div>

            <div class="flex items-center gap-5">
                <form action="/search" method="GET" class="relative group hidden sm:block">
                    <input type="text" name="q" placeholder="Search realms..." 
                        class="bg-lunar-base border border-lunar-border rounded-full py-2.5 px-6 w-64 focus:w-80 focus:border-lunar-accent outline-none transition-soft text-sm">
                    <button class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 group-focus-within:text-lunar-accent">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </form>

                @auth
                    <div class="flex items-center gap-3">
                        <a href="/profile" class="flex items-center gap-3 bg-lunar-card border border-lunar-border p-1 pr-4 rounded-full hover:border-lunar-accent transition-soft">
                            <div class="w-8 h-8 rounded-full bg-lunar-accent flex items-center justify-center font-bold overflow-hidden">
                                @if(auth()->user()->profile_photo)
                                    <img src="{{ asset(auth()->user()->profile_photo) }}" class="w-full h-full object-cover">
                                @else
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                @endif
                            </div>
                            <span class="text-sm font-medium hidden sm:inline">{{ explode(' ', auth()->user()->name)[0] }}</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="p-3 rounded-full bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white transition-soft" title="Logout">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </button>
                        </form>
                    </div>
                @else
                    <a href="/login" class="bg-lunar-accent hover:bg-opacity-80 text-white px-6 py-2.5 rounded-full font-bold text-sm transition-soft">
                        JOIN NOW
                    </a>
                @endauth
            </div>

        </div>
    </nav>

    <main class="pt-20">
        @yield('content')
    </main>

    <footer class="bg-lunar-card border-t border-lunar-border mt-20 py-20">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-12">
            <div class="col-span-2">
                <span class="text-2xl font-black font-orbitron tracking-tighter mb-6 block">
                    RUANA<span class="text-lunar-accent italic">MANWHA</span>
                </span>
                <p class="text-gray-500 leading-relaxed max-w-md">
                    The ultimate destination for manga enthusiasts. Explore thousands of stories, track your progress, and join a thriving community of readers.
                </p>
            </div>
            <div>
                <h4 class="font-bold mb-6 text-lunar-accent">PLATFORM</h4>
                <ul class="space-y-4 text-gray-500 text-sm">
                    <li><a href="#" class="hover:text-white">Discover</a></li>
                    <li><a href="#" class="hover:text-white">Trending</a></li>
                    <li><a href="#" class="hover:text-white">New Releases</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold mb-6 text-lunar-accent">COMMUNITY</h4>
                <ul class="space-y-4 text-gray-500 text-sm">
                    <li><a href="#" class="hover:text-white">Discord</a></li>
                    <li><a href="#" class="hover:text-white">Twitter</a></li>
                    <li><a href="#" class="hover:text-white">Terms of Service</a></li>
                </ul>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-6 mt-20 pt-8 border-t border-lunar-border flex justify-between items-center text-xs text-gray-600">
            <p>&copy; 2026 Ruana Manwha Platform. All rights reserved.</p>
            <p>Built for the Ruana Community.</p>
        </div>
    </footer>

    @yield('scripts')
</body>
</html>
