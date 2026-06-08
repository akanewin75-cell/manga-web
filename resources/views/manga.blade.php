@extends('layouts.ruana')

@section('title', ($info->title ?? 'Manga') . ' - Ruana Manwha')

@section('styles')
<style>
    .manga-banner {
        height: 600px;
        position: relative;
        overflow: hidden;
    }
    
    .manga-banner img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        filter: brightness(30%) blur(5px);
    }

    .banner-overlay {
        background: linear-gradient(to top, #050505 10%, transparent 100%);
        pointer-events: none;
    }

    .info-card {
        margin-top: -300px;
        position: relative;
        z-index: 20;
    }

    .chapter-row:hover {
        background: rgba(123, 123, 255, 0.05);
        border-color: rgba(123, 123, 255, 0.3);
    }

    .status-badge {
        font-size: 10px;
        padding: 2px 10px;
        border-radius: 6px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
</style>
@endsection

@section('content')
<div class="manga-banner">
    @php
        $cover = $info->cover;
        if(str_contains($cover, 'comicazen.com') || str_contains($cover, 'lunaranime.ru') || str_starts_with($cover, '/')) {
            $cover = route('proxy.image', ['url' => $cover]);
        }
    @endphp
    <img src="{{ $cover }}" alt="Banner">
    <div class="banner-overlay absolute inset-0"></div>
</div>

<div class="max-w-7xl mx-auto px-6 info-card">
    <div class="flex flex-col md:flex-row gap-12">
        
        <!-- Left: Poster -->
        <div class="w-full md:w-80 flex-shrink-0">
            <div class="rounded-[40px] overflow-hidden border-4 border-lunar-base shadow-2xl aspect-[3/4]">
                <img src="{{ $cover }}" class="w-full h-full object-cover">
            </div>
            
            <div class="mt-8 flex flex-col gap-4">
                @auth
                    @if(auth()->user()->role === 'admin' && !$localManga)
                        <form action="{{ route('manga.import', ['type' => $info->source_type, 'id' => $info->source_id]) }}" method="POST">
                            @csrf
                            <button class="w-full bg-lunar-neon text-lunar-base font-black py-4 rounded-2xl hover:opacity-90 transition-soft flex items-center justify-center gap-3">
                                📥 IMPORT TO LIBRARY
                            </button>
                        </form>
                    @endif
                @endauth

                <button class="w-full bg-lunar-accent text-white font-black py-4 rounded-2xl hover:shadow-lunar-accent/20 transition-soft">
                    ❤️ ADD TO FAVORITES
                </button>
            </div>
        </div>

        <!-- Right: Info -->
        <div class="flex-grow pt-10 md:pt-20">
            <div class="flex items-center gap-4 mb-6">
                <span class="bg-white/10 text-white text-[10px] font-bold px-4 py-1.5 rounded-full uppercase tracking-widest border border-white/10">
                    {{ $info->source_type }}
                </span>
                <span class="text-gray-500 font-bold text-sm uppercase tracking-widest">{{ $info->genre ?? 'Various Genres' }}</span>
            </div>

            <h1 class="text-5xl md:text-7xl font-black font-orbitron mb-8 leading-none text-glow-purple">
                {{ $info->title }}
            </h1>

            <p class="text-gray-400 text-lg leading-relaxed mb-12 max-w-3xl">
                {{ $info->description }}
            </p>

            <!-- Tabs / Sections -->
            <div class="border-b border-lunar-border mb-10 flex gap-10">
                <button class="border-b-2 border-lunar-accent pb-4 font-bold text-sm tracking-widest text-lunar-accent uppercase">Chapters</button>
                <button class="pb-4 font-bold text-sm tracking-widest text-gray-600 uppercase hover:text-gray-400 transition-soft">Discussions</button>
                <button class="pb-4 font-bold text-sm tracking-widest text-gray-600 uppercase hover:text-gray-400 transition-soft">Related</button>
            </div>

            <!-- Chapter List -->
            <div class="bg-lunar-card/50 border border-lunar-border rounded-[32px] overflow-hidden">
                <div class="p-4 bg-white/5 border-b border-lunar-border flex justify-between px-8">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Name</span>
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Status</span>
                </div>
                
                <div class="max-h-[600px] overflow-y-auto">
                    @forelse($chapters as $ch)
                        @php
                            $isLocal = in_array($ch['chapter_num'], $localChapters);
                        @endphp
                        <a href="{{ route('manga.read', ['type' => $info->source_type, 'mangaId' => $info->source_id, 'chapterId' => $ch['id']]) }}" 
                           class="flex items-center justify-between px-8 py-6 border-b border-lunar-border/50 chapter-row transition-soft group">
                            <div class="flex items-center gap-6">
                                <div class="w-12 h-12 rounded-xl bg-lunar-base border border-lunar-border flex items-center justify-center font-black group-hover:text-lunar-accent transition-soft">
                                    {{ $ch['chapter_num'] }}
                                </div>
                                <span class="font-bold text-gray-300 group-hover:text-white">{{ $ch['title'] }}</span>
                            </div>
                            <div class="flex items-center gap-6">
                                <span class="status-badge {{ $isLocal ? 'bg-lunar-neon/20 text-lunar-neon border border-lunar-neon/30' : 'bg-lunar-accent/20 text-lunar-accent border border-lunar-accent/30' }}">
                                    {{ $isLocal ? 'Local' : 'Cloud' }}
                                </span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700 group-hover:text-lunar-accent transition-soft" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </a>
                    @empty
                        <div class="p-20 text-center text-gray-600 font-bold uppercase tracking-widest">
                            No chapters discovered for this story 🌌
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
