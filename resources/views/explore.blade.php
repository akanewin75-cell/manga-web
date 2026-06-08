@extends('layouts.ruana')

@section('title', 'Explore - Ruana Manwha')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-20">
    
    <div class="mb-16">
        <h1 class="text-5xl font-black font-orbitron mb-4">EXPLORE <span class="text-lunar-accent">STORIES</span></h1>
        <p class="text-gray-500 text-lg">Discover stories from across the multiverse.</p>
    </div>

    <!-- Search / Filter Bar -->
    <div class="bg-lunar-card border border-lunar-border p-8 rounded-[32px] mb-16 flex flex-col md:flex-row gap-8 items-center justify-between">
        <form action="{{ route('search') }}" method="GET" class="w-full md:w-1/2 relative">
            <input type="text" name="q" value="{{ $query ?? '' }}" placeholder="Search by title..." 
                class="w-full bg-lunar-base border border-lunar-border rounded-2xl py-4 px-6 focus:border-lunar-accent outline-none transition-soft">
            <button class="absolute right-6 top-1/2 -translate-y-1/2 text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>
        </form>

        <div class="flex gap-4">
            <button class="bg-lunar-accent text-white px-8 py-4 rounded-2xl font-bold text-sm">ALL SOURCES</button>
            <button class="bg-white/5 border border-white/10 text-gray-400 px-8 py-4 rounded-2xl font-bold text-sm hover:text-white transition-soft uppercase">Comicazen</button>
            <button class="bg-white/5 border border-white/10 text-gray-400 px-8 py-4 rounded-2xl font-bold text-sm hover:text-white transition-soft uppercase">Webtoon</button>
        </div>
    </div>

    <!-- Results Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-8">
        @forelse($results as $manga)
            <a href="{{ route('manga.show', ['type' => $manga['source_type'], 'id' => $manga['source_id']]) }}" class="group block">
                <div class="relative aspect-[3/4] rounded-3xl overflow-hidden mb-4 border border-lunar-border group-hover:border-lunar-accent transition-soft">
                    @php
                        $cover = $manga['cover'];
                        if($cover && (str_contains($cover, 'comicazen.com') || str_contains($cover, 'lunaranime.ru') || str_starts_with($cover, '/'))) {
                            $cover = route('proxy.image', ['url' => $cover]);
                        }
                    @endphp
                    <img src="{{ $cover ?? 'https://via.placeholder.com/300x400?text=No+Cover' }}" 
                        class="w-full h-full object-cover transition-soft group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-soft flex items-end p-6">
                        <span class="bg-lunar-accent text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-tighter">
                            {{ $manga['source_type'] }}
                        </span>
                    </div>
                </div>
                <h3 class="font-bold text-gray-200 group-hover:text-lunar-accent transition-soft line-clamp-1">
                    {{ $manga['title'] }}
                </h3>
            </a>
        @empty
            <div class="col-span-full py-40 text-center">
                <div class="text-6xl mb-8">🌌</div>
                <h2 class="text-2xl font-bold text-gray-600 uppercase tracking-widest">No stories found in this sector</h2>
                <p class="text-gray-700 mt-4">Try searching with different coordinates.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection
