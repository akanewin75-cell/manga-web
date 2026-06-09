@extends('layouts.ruana')

@section('title', 'Ruana Manwha - Gateway to the Multiverse')

@section('styles')
<style>
    .hero-slide {
        height: 300px;
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        background: #0a0a0a;
    }
    
    @media (min-width: 768px) {
        .hero-slide {
            height: 450px;
            border-radius: 40px;
        }
    }
    
    .hero-overlay {
        background: linear-gradient(to bottom, rgba(5,5,5,0.4) 0%, rgba(5,5,5,1) 100%);
    }

    @media (min-width: 768px) {
        .hero-overlay {
            background: linear-gradient(to right, rgba(5,5,5,1) 10%, rgba(5,5,5,0.6) 50%, transparent 100%);
        }
    }

    .manga-card {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .manga-card:hover {
        transform: translateY(-8px);
    }

    .manga-card:hover .card-img {
        transform: scale(1.1);
        filter: brightness(1.2);
    }

    .btn-neon {
        background: #7b7bff;
        box-shadow: 0 0 20px rgba(123, 123, 255, 0.4);
    }

    .btn-neon:hover {
        box-shadow: 0 0 30px rgba(123, 123, 255, 0.6);
        transform: scale(1.05);
    }
    
    .badge-source {
        background: rgba(123, 123, 255, 0.2);
        border: 1px solid rgba(123, 123, 255, 0.3);
        color: #7b7bff;
    }
</style>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">
    
    <!-- Hero Section (Carousel) -->
    @php
        $carouselItems = $trending->count() > 0 ? $trending : collect($discoveries)->take(5);
    @endphp

    <div class="mb-12 relative" x-data="{ 
        activeSlide: 0, 
        slides: {{ count($carouselItems) }},
        init() {
            setInterval(() => {
                this.activeSlide = (this.activeSlide + 1) % this.slides;
            }, 5000);
        }
    }">
        <div class="hero-slide border border-lunar-border shadow-2xl relative overflow-hidden">
            @foreach($carouselItems as $index => $item)
                @php
                    $isModel = $item instanceof \App\Models\Manga;
                    $cCover = $isModel ? $item->cover_url : ($item['cover'] ?? '');
                    $cTitle = $isModel ? $item->title : ($item['title'] ?? '');
                    $cDesc = $isModel ? $item->description : 'New release available in the multiverse.';
                    $cType = $isModel ? $item->source_type : ($item['source_type'] ?? 'local');
                    $cId = $isModel ? $item->source_id : ($item['source_id'] ?? '');
                @endphp
                
                <div x-show="activeSlide === {{ $index }}" 
                     x-transition:enter="transition ease-out duration-1000"
                     x-transition:enter-start="opacity-0 transform scale-110"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-1000"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="absolute inset-0">
                    
                    <img src="@proxy($cCover)" class="absolute inset-0 w-full h-full object-cover opacity-40">
                    <div class="hero-overlay absolute inset-0 flex flex-col justify-center px-8 md:px-20">
                        <div class="flex items-center gap-3 mb-4 md:mb-6">
                            <span class="bg-lunar-accent text-white text-[8px] md:text-[10px] font-black px-2 md:px-3 py-1 rounded-md uppercase tracking-[0.2em]">Latest Update</span>
                            <span class="text-lunar-accent font-bold text-[10px] md:text-sm tracking-widest uppercase hidden md:inline">★ Featured Discovery</span>
                        </div>
                        <h1 class="text-3xl md:text-7xl font-black font-orbitron mb-4 md:mb-6 leading-tight max-w-3xl text-glow-purple italic uppercase line-clamp-2">
                            {{ $cTitle }}
                        </h1>
                        <p class="text-gray-300 text-sm md:text-lg max-w-xl mb-6 md:mb-10 line-clamp-2 md:line-clamp-3 font-medium opacity-80">
                            {{ $cDesc }}
                        </p>
                        <div class="flex items-center gap-4 md:gap-6">
                            <a href="/manga/{{ $cType }}/{{ $cId }}" class="btn-neon text-white px-6 md:px-12 py-3 md:py-5 rounded-xl md:rounded-2xl font-black tracking-widest transition-soft text-[10px] md:text-base">
                                READ NOW
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach

            @if(count($carouselItems) == 0)
                <div class="absolute inset-0 flex flex-col justify-center items-center text-center p-8 bg-gradient-to-br from-lunar-card to-lunar-base">
                    <div class="w-16 h-16 mb-4 rounded-2xl lunar-gradient flex items-center justify-center text-white shadow-2xl animate-pulse">
                        <span class="text-2xl font-black">R</span>
                    </div>
                    <h1 class="text-2xl md:text-4xl font-black font-orbitron mb-2 tracking-tighter uppercase">Initializing <span class="text-lunar-accent">Ruana</span></h1>
                    <p class="text-[10px] md:text-sm text-gray-500 max-w-xs font-medium">The gates are opening. Find your next obsession.</p>
                </div>
            @endif
        </div>

        <!-- Carousel Indicators -->
        @if(count($carouselItems) > 1)
            <div class="absolute bottom-6 right-8 md:right-20 flex gap-2 z-10">
                @foreach($carouselItems as $index => $item)
                    <button @click="activeSlide = {{ $index }}" 
                            :class="activeSlide === {{ $index }} ? 'w-8 bg-lunar-accent' : 'w-2 bg-white/20'"
                            class="h-2 rounded-full transition-all duration-500 border border-white/10 hover:bg-white/40"></button>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Discovery Grid -->
    <div class="mb-16">
        <div class="flex items-end justify-between mb-8 md:mb-12">
            <div>
                <h2 class="text-2xl md:text-4xl font-black font-orbitron uppercase tracking-tighter italic">Latest <span class="text-lunar-accent">Updates</span></h2>
                <p class="text-[10px] md:text-base text-gray-500 mt-1 md:mt-2 font-medium">Fresh releases from the multiverse.</p>
            </div>
            <a href="/explore" class="group flex items-center gap-2 md:gap-3 bg-lunar-card border border-lunar-border px-4 md:px-6 py-2 md:py-3 rounded-xl md:rounded-2xl hover:border-lunar-accent transition-soft">
                <span class="text-[8px] md:text-sm font-black uppercase tracking-widest text-gray-400 group-hover:text-white transition-soft">View More</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 md:h-5 md:w-5 text-lunar-accent group-hover:translate-x-1 transition-soft" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 md:gap-8">
            @foreach($discoveries as $manga)
                <div class="manga-card group">
                    <a href="/manga/{{ $manga['source_type'] }}/{{ $manga['source_id'] }}" class="block">
                        <div class="relative aspect-[3/4.5] rounded-2xl md:rounded-3xl overflow-hidden mb-2 md:mb-5 border border-lunar-border shadow-xl group-hover:border-lunar-accent/50 group-hover:shadow-lunar-accent/10 transition-soft">
                            <img src="@proxy($manga['cover'] ?? 'https://via.placeholder.com/300x400?text=No+Cover')" 
                                class="card-img w-full h-full object-cover transition-soft duration-500"
                                loading="lazy"
                                onerror="this.onerror=null; this.src='https://via.placeholder.com/300x400?text={{ urlencode($manga['title']) }}';">
                            
                            <!-- Source Badge (Always visible) -->
                            <div class="absolute top-2 left-2 md:top-3 md:left-3">
                                <span class="bg-black/60 backdrop-blur-md text-white text-[7px] md:text-[8px] font-black px-1.5 py-0.5 md:px-2 md:py-1 rounded md:rounded-md border border-white/10 uppercase tracking-widest">
                                    {{ $manga['source_type'] }}
                                </span>
                            </div>
                        </div>
                        
                        <h3 class="font-black text-gray-200 group-hover:text-lunar-accent transition-soft line-clamp-2 leading-tight uppercase text-[10px] md:text-sm mb-1">
                            {{ $manga['title'] }}
                        </h3>
                        <div class="flex items-center gap-1 md:gap-2">
                            <div class="w-1 md:w-1.5 h-1 md:h-1.5 rounded-full bg-lunar-neon animate-pulse"></div>
                            <p class="text-[8px] md:text-[10px] text-gray-500 uppercase tracking-widest font-bold">New</p>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
