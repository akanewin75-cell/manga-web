@extends('layouts.ruana')

@section('title', 'Ruana Manwha - Your Gateway to Stories')

@section('styles')
<style>
    .hero-slide {
        height: 600px;
        position: relative;
        overflow: hidden;
        border-radius: 40px;
    }
    
    .hero-overlay {
        background: linear-gradient(to right, rgba(5,5,5,0.95) 20%, rgba(5,5,5,0.4) 60%, transparent 100%);
    }

    .card-zoom:hover img {
        transform: scale(1.1);
    }
</style>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-6 py-12">
    
    <!-- Hero Section -->
    <div class="hero-slide mb-20 relative bg-lunar-card border border-lunar-border shadow-2xl">
        @if(count($trending) > 0)
            @php 
                $featured = $trending[0]; 
                $heroCover = $featured->cover_url;
                if(str_contains($heroCover, 'comicazen.com') || str_contains($heroCover, 'lunaranime.ru') || str_starts_with($heroCover, '/')) {
                    $heroCover = route('proxy.image', ['url' => $heroCover]);
                }
            @endphp
            <img src="{{ $heroCover }}" class="absolute inset-0 w-full h-full object-cover opacity-40">
            <div class="hero-overlay absolute inset-0 flex flex-col justify-center px-12 md:px-20">
                <span class="text-lunar-accent font-bold tracking-widest text-sm mb-4 uppercase">Trending Now</span>
                <h1 class="text-5xl md:text-7xl font-black font-orbitron mb-6 leading-tight max-w-2xl text-glow-purple">
                    {{ $featured->title }}
                </h1>
                <p class="text-gray-400 text-lg max-w-xl mb-10 line-clamp-3">
                    {{ $featured->description }}
                </p>
                <div class="flex items-center gap-4">
                    <a href="/manga/{{ $featured->slug }}" class="bg-lunar-accent text-white px-10 py-4 rounded-2xl font-bold shadow-lg hover:shadow-lunar-accent/20 transition-soft">
                        START READING
                    </a>
                    <button class="bg-white/5 border border-white/10 text-white px-6 py-4 rounded-2xl font-bold hover:bg-white/10 transition-soft">
                        DETAILS
                    </button>
                </div>
            </div>
        @else
            <!-- Placeholder if no trending -->
            <div class="absolute inset-0 flex flex-col justify-center items-center text-center p-12">
                <h1 class="text-4xl font-black font-orbitron mb-4">Welcome to Ruana</h1>
                <p class="text-gray-500 max-w-md">The most advanced manga platform for fans. Search for your favorite stories to begin.</p>
            </div>
        @endif
    </div>

    <!-- Discovery Grid -->
    <div class="mb-20">
        <div class="flex items-center justify-between mb-10">
            <h2 class="text-3xl font-black font-orbitron">RECENT <span class="text-lunar-accent">DISCOVERIES</span></h2>
            <a href="/explore" class="text-gray-500 hover:text-lunar-accent transition-soft text-sm font-bold flex items-center gap-2">
                EXPLORE ALL
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-8">
            @foreach($discoveries as $manga)
                @php
                    $discoveryCover = $manga['cover'] ?? 'https://via.placeholder.com/300x400?text=No+Cover';
                    if(str_contains($discoveryCover, 'comicazen.com') || str_contains($discoveryCover, 'lunaranime.ru') || str_starts_with($discoveryCover, '/')) {
                        $discoveryCover = route('proxy.image', ['url' => $discoveryCover]);
                    }
                @endphp
                <a href="/manga/{{ $manga['source_type'] }}/{{ $manga['source_id'] }}" class="group block">
                    <div class="relative aspect-[3/4] rounded-3xl overflow-hidden mb-4 border border-lunar-border group-hover:border-lunar-accent transition-soft card-zoom">
                        <img src="{{ $discoveryCover }}" 
                            class="w-full h-full object-cover transition-soft">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-soft flex items-end p-6">
                            <span class="bg-lunar-accent text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-tighter">
                                {{ $manga['source_type'] }}
                            </span>
                        </div>
                    </div>
                    <h3 class="font-bold text-gray-200 group-hover:text-lunar-accent transition-soft line-clamp-1">
                        {{ $manga['title'] }}
                    </h3>
                    <p class="text-xs text-gray-500 mt-1 uppercase tracking-widest font-medium">Source: {{ $manga['source_type'] }}</p>
                </a>
            @endforeach
        </div>
    </div>

</div>
@endsection
