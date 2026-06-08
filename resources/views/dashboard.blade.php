@extends('layouts.ruana')

@section('title', 'Your Library - Ruana Manwha')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-20">
    
    <div class="flex flex-col md:flex-row gap-12">
        
        <!-- Sidebar -->
        <div class="w-full md:w-80 flex-shrink-0">
            <div class="bg-lunar-card border border-lunar-border rounded-[32px] p-8 sticky top-32">
                <div class="flex flex-col items-center text-center mb-10">
                    <div class="w-24 h-24 rounded-full bg-lunar-accent flex items-center justify-center text-3xl font-black mb-6 shadow-xl shadow-lunar-accent/20">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <h2 class="text-xl font-black font-orbitron">{{ auth()->user()->name }}</h2>
                    <p class="text-gray-500 text-sm mt-2 uppercase tracking-widest font-bold">{{ auth()->user()->role }}</p>
                </div>

                <div class="flex flex-col gap-2">
                    <a href="/dashboard" class="bg-white/5 text-white font-bold p-4 rounded-2xl flex items-center gap-4 hover:bg-white/10 transition-soft">
                        🏠 DASHBOARD
                    </a>
                    <a href="/profile" class="text-gray-500 font-bold p-4 rounded-2xl flex items-center gap-4 hover:text-white transition-soft">
                        👤 PROFILE
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left text-red-500/60 font-bold p-4 rounded-2xl flex items-center gap-4 hover:text-red-500 hover:bg-red-500/5 transition-soft">
                            🚪 LOGOUT
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow">
            <h1 class="text-4xl font-black font-orbitron mb-12">WELCOME BACK, <span class="text-lunar-accent">PIONEER</span></h1>

            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 mb-12">
                <div class="bg-lunar-card border border-lunar-border p-8 rounded-[32px]">
                    <span class="text-gray-500 text-xs font-black uppercase tracking-widest block mb-4">Bookmarks</span>
                    <span class="text-4xl font-black font-orbitron">0</span>
                </div>
                <div class="bg-lunar-card border border-lunar-border p-8 rounded-[32px]">
                    <span class="text-gray-500 text-xs font-black uppercase tracking-widest block mb-4">Likes</span>
                    <span class="text-4xl font-black font-orbitron">0</span>
                </div>
                <div class="bg-lunar-card border border-lunar-border p-8 rounded-[32px]">
                    <span class="text-gray-500 text-xs font-black uppercase tracking-widest block mb-4">Rank</span>
                    <span class="text-xl font-black font-orbitron text-lunar-accent uppercase">Moon Walker</span>
                </div>
            </div>

            <!-- Migration Tool (Admin Only) -->
            @if(auth()->user()->role === 'admin')
                <div class="bg-lunar-neon/10 border border-lunar-neon/20 p-10 rounded-[40px] mb-12">
                    <h3 class="text-2xl font-black font-orbitron text-lunar-neon mb-4 uppercase">System Rebirth Panel</h3>
                    <p class="text-gray-400 mb-8 leading-relaxed">
                        You have successfully deployed the new Ruana Architecture. Please initialize the database migration to transfer all existing manga realms from the old file system to the new SQLite core.
                    </p>
                    <a href="/admin/migrate-data" class="inline-block bg-lunar-neon text-lunar-base font-black px-10 py-4 rounded-2xl hover:opacity-90 transition-soft">
                        START DATA MIGRATION
                    </a>
                </div>
            @endif

            <!-- Placeholder for Bookmarks -->
            <div class="bg-lunar-card/50 border border-lunar-border border-dashed p-20 rounded-[40px] text-center">
                <div class="text-5xl mb-6">🔭</div>
                <h3 class="text-xl font-bold text-gray-600 uppercase tracking-widest mb-4">Your Library is Empty</h3>
                <p class="text-gray-700">Explore the realms and bookmark your favorite stories to see them here.</p>
                <a href="/explore" class="inline-block mt-8 text-lunar-accent font-black text-sm uppercase tracking-widest hover:underline">Start Exploring</a>
            </div>

        </div>

    </div>

</div>
@endsection
