@extends('layouts.ruana')

@section('title', $info->title . ' - Ruana Manwha')

@section('styles')
<style>
    .manga-header {
        height: 500px;
        position: relative;
    }
    .manga-header-overlay {
        background: linear-gradient(to top, #050505 0%, rgba(5,5,5,0.8) 50%, rgba(5,5,5,0.4) 100%);
    }
    .chapter-list {
        max-height: 600px;
        overflow-y: auto;
    }
    .chapter-item {
        background: #0d0d0d;
        border: 1px solid #1a1a1a;
        transition: all 0.3s ease;
    }
    .chapter-item:hover {
        border-color: #7b7bff;
        background: #1a1a1a;
        transform: translateX(10px);
    }
</style>
@endsection

@section('content')
<div class="relative">
    <!-- Header Background -->
    <div class="manga-header">
        <img src="@proxy($info->cover)" class="w-full h-full object-cover opacity-30 blur-sm">
        <div class="manga-header-overlay absolute inset-0"></div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-6 -mt-80 relative z-10">
        <div class="flex flex-col md:flex-row gap-12">
            <!-- Sidebar -->
            <div class="w-full md:w-80 flex-shrink-0">
                <div class="rounded-[40px] overflow-hidden border-4 border-lunar-card shadow-2xl sticky top-28">
                    <img src="@proxy($info->cover)" class="w-full h-auto">
                    <div class="p-6 bg-lunar-card border-t border-lunar-border">
                        <div class="flex flex-col gap-3">
                            @auth
                                @if($localManga)
                                    <button class="w-full bg-lunar-neon/10 text-lunar-neon border border-lunar-neon/20 py-4 rounded-2xl font-black uppercase tracking-widest text-sm">
                                        ✓ IN LIBRARY
                                    </button>
                                @else
                                    <form action="{{ route('manga.import', ['type' => $info->source_type, 'id' => $info->source_id]) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full bg-lunar-accent text-white py-4 rounded-2xl font-black uppercase tracking-widest text-sm shadow-lg shadow-lunar-accent/20 hover:scale-105 transition-soft">
                                            + ADD TO LIBRARY
                                        </button>
                                    </form>
                                @endif
                            @else
                                <a href="/login" class="w-full bg-lunar-accent text-white py-4 rounded-2xl font-black uppercase tracking-widest text-sm text-center">
                                    LOGIN TO TRACK
                                </a>
                            @endauth
                            <button class="w-full bg-white/5 text-gray-400 py-4 rounded-2xl font-black uppercase tracking-widest text-sm border border-white/5 hover:bg-white/10 transition-soft">
                                SHARE STORY
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Info -->
            <div class="flex-grow">
                <div class="mb-12">
                    <div class="flex items-center gap-4 mb-6">
                        <span class="bg-lunar-accent text-white text-[10px] font-black px-3 py-1 rounded-md uppercase tracking-[0.2em]">{{ $info->source_type }}</span>
                        <span class="text-gray-500 font-bold text-sm tracking-widest uppercase">ID: {{ $info->source_id }}</span>
                    </div>
                    <h1 class="text-5xl md:text-7xl font-black font-orbitron mb-8 leading-tight italic uppercase text-glow-purple">
                        {{ $info->title }}
                    </h1>
                    
                    <div class="flex flex-wrap gap-3 mb-12">
                        @foreach(explode(',', $info->genre) as $genre)
                            <span class="bg-lunar-card border border-lunar-border text-gray-400 px-5 py-2 rounded-full text-xs font-bold uppercase tracking-widest hover:border-lunar-accent hover:text-white transition-soft cursor-default">
                                {{ trim($genre) }}
                            </span>
                        @endforeach
                    </div>

                    <div class="bg-lunar-card/50 backdrop-blur-md border border-lunar-border rounded-[40px] p-10 mb-12">
                        <h3 class="text-xl font-black font-orbitron mb-6 text-lunar-accent uppercase italic">Synopsis</h3>
                        <p class="text-gray-400 leading-relaxed text-lg font-medium">
                            {!! $info->description ?: 'No description available for this title in the current realm.' !!}
                        </p>
                    </div>

                    <!-- Chapters Section -->
                    <div class="mb-20" x-data="{ search: '' }">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
                            <div>
                                <h2 class="text-3xl font-black font-orbitron uppercase italic">Available <span class="text-lunar-accent">Chapters</span></h2>
                                <span class="text-gray-500 font-bold uppercase tracking-widest text-sm">{{ count($chapters) }} Chapters Found</span>
                            </div>
                            
                            <!-- Chapter Search -->
                            <div class="relative w-full md:w-72">
                                <input type="text" x-model="search" placeholder="Find chapter..." 
                                    class="w-full bg-lunar-base border border-lunar-border rounded-2xl py-3 px-12 focus:border-lunar-accent outline-none transition-soft text-sm font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-4 top-1/2 -translate-y-1/2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 chapter-list pr-4">
                            @foreach($chapters as $ch)
                                <a href="{{ route('manga.read', ['type' => $info->source_type, 'mangaId' => $info->source_id, 'chapterId' => $ch['id']]) }}" 
                                    x-show="'{{ strtolower(addslashes($ch['title'])) }}'.includes(search.toLowerCase())"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 transform -translate-y-2"
                                    x-transition:enter-end="opacity-100 transform translate-y-0"
                                    class="chapter-item flex items-center justify-between p-6 rounded-3xl group">
                                    <div class="flex items-center gap-6">
                                        <div class="w-12 h-12 rounded-2xl bg-lunar-base flex items-center justify-center font-black text-lunar-accent border border-lunar-border group-hover:bg-lunar-accent group-hover:text-white transition-soft">
                                            {{ $loop->remaining + 1 }}
                                        </div>
                                        <div>
                                            <h4 class="font-black text-gray-200 group-hover:text-white transition-soft uppercase tracking-tight">{{ $ch['title'] }}</h4>
                                            <p class="text-[10px] text-gray-500 uppercase tracking-[0.2em] mt-1 font-bold group-hover:text-lunar-accent">Transmitted via {{ $info->source_type }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        @if(in_array($ch['chapter_num'], $localChapters))
                                            <span class="bg-lunar-neon/10 text-lunar-neon text-[8px] font-black px-3 py-1 rounded-full uppercase tracking-widest border border-lunar-neon/20">Downloaded</span>
                                        @endif
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700 group-hover:text-lunar-accent transition-soft" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </div>
                                </a>
                            @endforeach
                            
                            <!-- Empty State for Search -->
                            <div x-show="[...$el.parentElement.children].filter(el => el.style.display !== 'none' && !el.classList.contains('empty-msg')).length === 0" 
                                 class="empty-msg py-20 text-center bg-lunar-card/30 rounded-[40px] border border-dashed border-lunar-border">
                                <p class="text-gray-600 font-black font-orbitron uppercase tracking-widest">Frequency Mismatch</p>
                                <p class="text-gray-700 text-xs mt-2 uppercase font-bold">No chapter found with that signature.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
