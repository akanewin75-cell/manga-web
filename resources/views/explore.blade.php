@extends('layouts.ruana')

@section('title', 'Explore - Ruana Manwha')

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

    .btn-neon {
        background: #7b7bff;
        box-shadow: 0 0 20px rgba(123, 123, 255, 0.4);
    }

    .btn-neon:hover {
        box-shadow: 0 0 30px rgba(123, 123, 255, 0.6);
        transform: scale(1.05);
    }
</style>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">
    
    @php
        $carouselCount = 5;
        $carouselItems = collect($results)->take($carouselCount);
        $gridItems = collect($results)->slice($carouselCount);
        
        // If results are empty on a deep page, fallback to trending for carousel
        if ($carouselItems->isEmpty() && !empty($trending)) {
            $carouselItems = $trending;
        }
    @endphp

    @if($carouselItems->count() > 0)
        <!-- Hero Section (Carousel) - Changes per Sector -->
        <div class="mb-12 relative" x-data="{ 
            activeSlide: 0, 
            slides: {{ $carouselItems->count() }},
            init() {
                if(this.slides > 1) {
                    setInterval(() => {
                        this.activeSlide = (this.activeSlide + 1) % this.slides;
                    }, 5000);
                }
            }
        }">
            <div class="hero-slide border border-lunar-border shadow-2xl relative overflow-hidden">
                @foreach($carouselItems as $index => $item)
                    @php
                        $isModel = $item instanceof \App\Models\Manga;
                        $cCover = $isModel ? $item->cover_url : ($item['cover'] ?? '');
                        $cTitle = $isModel ? $item->title : ($item['title'] ?? '');
                        $cDesc = $isModel ? $item->description : 'New release available in Sector ' . ($page ?? 1) . '.';
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
                                <span class="bg-lunar-accent text-white text-[8px] md:text-[10px] font-black px-2 md:px-3 py-1 rounded-md uppercase tracking-[0.2em]">
                                    {{ empty($query) ? 'Sector ' . ($page ?? 1) : 'Search Result' }}
                                </span>
                                <span class="text-lunar-accent font-bold text-[10px] md:text-sm tracking-widest uppercase hidden md:inline">★ Featured discovery</span>
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
            </div>

            <!-- Carousel Indicators -->
            @if($carouselItems->count() > 1)
                <div class="absolute bottom-6 right-8 md:right-20 flex gap-2 z-10">
                    @foreach($carouselItems as $index => $item)
                        <button @click="activeSlide = {{ $index }}" 
                                :class="activeSlide === {{ $index }} ? 'w-8 bg-lunar-accent' : 'w-2 bg-white/20'"
                                class="h-2 rounded-full transition-all duration-500 border border-white/10 hover:bg-white/40"></button>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <div class="mb-16">
        <h1 class="text-5xl font-black font-orbitron mb-4 uppercase italic tracking-tighter">
            {{ empty($query) ? 'Explore' : 'Results for' }} <span class="text-lunar-accent">{{ $query ?? 'Stories' }}</span>
        </h1>
        <p class="text-gray-500 text-lg font-medium italic">Navigating Sector {{ $page ?? 1 }} of the multiverse.</p>
    </div>

    <!-- Search Bar -->
    <div class="bg-lunar-card border border-lunar-border p-8 rounded-[32px] mb-16 flex justify-center">
        <form action="{{ route('search') }}" method="GET" class="w-full md:w-2/3 relative">
            <input type="text" name="q" value="{{ $query ?? '' }}" placeholder="Search by title..." 
                class="w-full bg-lunar-base border border-lunar-border rounded-2xl py-4 px-6 focus:border-lunar-accent outline-none transition-soft">
            <button class="absolute right-6 top-1/2 -translate-y-1/2 text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>
        </form>
    </div>

    <!-- Results Grid -->
    <div class="grid grid-cols-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 md:gap-8 mb-16">
        @forelse($gridItems as $manga)
            <a href="{{ route('manga.show', ['type' => $manga['source_type'], 'id' => $manga['source_id']]) }}" class="group block">
                <div class="relative aspect-[3/4] rounded-2xl md:rounded-3xl overflow-hidden mb-2 md:mb-4 border border-lunar-border group-hover:border-lunar-accent transition-soft shadow-xl">
                    <img src="@proxy($manga['cover'] ?? 'https://via.placeholder.com/300x400?text=No+Cover')" 
                        class="w-full h-full object-cover transition-soft group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-soft flex items-end p-2 md:p-6">
                        <span class="bg-lunar-accent text-white text-[7px] md:text-[10px] font-bold px-1.5 py-0.5 md:px-3 md:py-1 rounded-full uppercase tracking-tighter">
                            {{ $manga['source_type'] }}
                        </span>
                    </div>
                </div>
                <h3 class="font-bold text-gray-200 group-hover:text-lunar-accent transition-soft line-clamp-2 text-[10px] md:text-base leading-tight uppercase">
                    {{ $manga['title'] }}
                </h3>
            </a>
        @empty
            @if($carouselItems->isEmpty())
                <div class="col-span-full py-40 text-center">
                    <div class="text-6xl mb-8">🌌</div>
                    <h2 class="text-2xl font-bold text-gray-600 uppercase tracking-widest">No stories found in this sector</h2>
                    <p class="text-gray-700 mt-4">Try searching with different coordinates.</p>
                </div>
            @endif
        @endforelse
    </div>

    <!-- Pagination -->
    @if(count($results) > 0)
    <div class="flex justify-center items-center gap-6">
        @if(($page ?? 1) > 1)
            <a href="{{ url()->current() }}?q={{ $query }}&page={{ ($page ?? 1) - 1 }}" 
               class="bg-lunar-card border border-lunar-border text-white px-8 py-4 rounded-2xl font-black tracking-widest hover:border-lunar-accent transition-soft uppercase text-xs">
                PREVIOUS SECTOR
            </a>
        @endif
        
        <span class="text-lunar-accent font-black font-orbitron text-xl">
            PAGE {{ $page ?? 1 }}
        </span>

        @if(count($results) >= 12) {{-- Assuming if we got a full-ish page, there's likely more --}}
            <a href="{{ url()->current() }}?q={{ $query }}&page={{ ($page ?? 1) + 1 }}" 
               class="bg-lunar-accent text-white px-8 py-4 rounded-2xl font-black tracking-widest hover:shadow-lunar-accent/20 shadow-xl transition-soft uppercase text-xs">
                NEXT SECTOR
            </a>
        @endif
    </div>
    @endif

</div>
@endsection
