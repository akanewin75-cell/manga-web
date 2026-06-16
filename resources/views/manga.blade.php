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
            <div class="w-full md:w-80 flex-shrink-0 flex justify-center">
                <div class="max-w-[280px] md:max-w-none rounded-[30px] md:rounded-[40px] overflow-hidden border-4 border-lunar-card shadow-2xl sticky top-28">
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
                            @auth
                                @php
                                    $continueChapterId = $lastReadChapterId;
                                    if (!$continueChapterId && count($chapters) > 0) {
                                        // If no read history, start from the first chapter (last in array usually for comicaso)
                                        $continueChapterId = end($chapters)['id']; 
                                    }
                                @endphp

                                @if($continueChapterId)
                                    <a href="{{ route('manga.read', ['type' => $info->source_type, 'mangaId' => $info->source_id, 'chapterId' => $continueChapterId]) }}" 
                                        class="w-full bg-white/5 text-gray-200 py-4 rounded-2xl font-black uppercase tracking-widest text-sm border border-white/5 hover:bg-white/10 transition-soft flex items-center justify-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-lunar-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ $lastReadChapterId ? 'CONTINUE READING' : 'START READING' }}
                                    </a>
                                @endif
                            @endauth
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Info -->
            <div class="flex-grow">
                <div class="mb-12">
                    <div class="flex items-center gap-4 mb-6">
                        @if($info->source_type != 'comicaso')
                        <span class="bg-lunar-accent text-white text-[10px] font-black px-3 py-1 rounded-md uppercase tracking-[0.2em]">{{ $info->source_type }}</span>
                        @endif
                        <span class="text-gray-500 font-bold text-sm tracking-widest uppercase">ID: {{ $info->source_id }}</span>
                    </div>
                    <h1 class="text-5xl md:text-7xl font-black font-orbitron mb-8 leading-tight italic uppercase text-glow-purple">
                        {{ $info->title }}
                    </h1>
                    
                    <div class="flex flex-wrap gap-3 mb-12">
                        @foreach(explode(',', $info->genre) as $genre)
                            @php $trimmedGenre = trim($genre); @endphp
                            <a href="{{ route('explore', ['genre' => $trimmedGenre]) }}" class="bg-lunar-card border border-lunar-border text-gray-400 px-5 py-2 rounded-full text-xs font-bold uppercase tracking-widest hover:border-lunar-accent hover:text-white transition-soft">
                                {{ $trimmedGenre }}
                            </a>
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
                                @php
                                    $isRead = in_array($ch['id'], $readChapterIds);
                                @endphp
                                <a href="{{ route('manga.read', ['type' => $info->source_type, 'mangaId' => $info->source_id, 'chapterId' => $ch['id']]) }}" 
                                    x-show="'{{ strtolower(addslashes($ch['title'])) }}'.includes(search.toLowerCase())"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 transform -translate-y-2"
                                    x-transition:enter-end="opacity-100 transform translate-y-0"
                                    class="chapter-item flex items-center justify-between p-6 rounded-3xl group {{ $isRead ? 'opacity-60 hover:opacity-100' : '' }}">
                                    <div class="flex items-center gap-6">
                                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black border border-lunar-border transition-soft {{ $isRead ? 'bg-lunar-neon text-black' : 'bg-lunar-base text-lunar-accent group-hover:bg-lunar-accent group-hover:text-white' }}">
                                            @if($isRead)
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @else
                                                {{ $ch['chapter_num'] }}
                                            @endif
                                        </div>
                                        <div>
                                            <h4 class="font-black text-gray-200 group-hover:text-white transition-soft uppercase tracking-tight">{{ $ch['title'] }}</h4>
                                            @if($isRead)
                                                <p class="text-[9px] text-lunar-neon uppercase tracking-[0.2em] mt-1 font-black">Memory Sync Complete</p>
                                            @elseif($info->source_type != 'comicaso')
                                                <p class="text-[10px] text-gray-500 uppercase tracking-[0.2em] mt-1 font-bold group-hover:text-lunar-accent">Transmitted via {{ $info->source_type }}</p>
                                            @endif
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

                    <!-- Comments Section -->
                    <div class="mt-20">
                        <div class="flex items-center gap-4 mb-10">
                            <h2 class="text-3xl font-black font-orbitron uppercase italic">Community <span class="text-lunar-accent">Discussion</span></h2>
                            <span class="bg-lunar-accent/10 text-lunar-accent text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest border border-lunar-accent/20">Beta</span>
                        </div>

                        <div class="bg-lunar-card/30 border border-lunar-border rounded-[40px] p-8 md:p-12">
                            @auth
                                <form action="{{ route('comments.store') }}" method="POST" class="flex gap-6 mb-12">
                                    @csrf
                                    <input type="hidden" name="source_type" value="{{ $info->source_type }}">
                                    <input type="hidden" name="source_id" value="{{ $info->source_id }}">
                                    
                                    <div class="w-14 h-14 rounded-2xl bg-lunar-accent flex-shrink-0 flex items-center justify-center font-bold text-xl overflow-hidden shadow-lg shadow-lunar-accent/20">
                                        @if(auth()->user()->profile_photo)
                                            <img src="{{ asset(auth()->user()->profile_photo) }}" class="w-full h-full object-cover">
                                        @else
                                            {{ substr(auth()->user()->name, 0, 1) }}
                                        @endif
                                    </div>
                                    <div class="flex-grow">
                                        <textarea name="content" required placeholder="Share your thoughts on this story..." 
                                            class="w-full bg-lunar-base border border-lunar-border rounded-[30px] p-6 focus:border-lunar-accent outline-none transition-soft text-sm font-medium min-h-[120px] resize-none"></textarea>
                                        <div class="flex justify-end mt-4">
                                            <button type="submit" class="bg-lunar-accent text-white px-10 py-3 rounded-2xl font-black uppercase tracking-widest text-xs shadow-lg shadow-lunar-accent/20 hover:scale-105 transition-soft">
                                                Post Comment
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @else
                                <div class="bg-lunar-base/50 rounded-[30px] p-8 text-center mb-12 border border-dashed border-lunar-border">
                                    <p class="text-gray-500 font-bold uppercase tracking-widest text-xs mb-4">Transmission restricted to authorized units</p>
                                    <a href="/login" class="inline-block bg-white/5 text-white px-8 py-3 rounded-xl font-bold uppercase tracking-widest text-xs border border-white/10 hover:bg-lunar-accent transition-soft">
                                        Login to join the discussion
                                    </a>
                                </div>
                            @endauth

                            <!-- Actual Comments -->
                            <div class="space-y-10">
                                @forelse($comments as $comment)
                                    <div class="flex gap-6" x-data="{ editing: false }">
                                        <div class="w-12 h-12 rounded-xl bg-lunar-accent/20 border border-lunar-accent/30 flex-shrink-0 flex items-center justify-center text-lunar-accent font-black overflow-hidden">
                                            @if($comment->user->profile_photo)
                                                <img src="{{ asset($comment->user->profile_photo) }}" class="w-full h-full object-cover">
                                            @else
                                                {{ substr($comment->user->name, 0, 1) }}
                                            @endif
                                        </div>
                                        <div class="flex-grow">
                                            <div class="flex items-center justify-between mb-2">
                                                <div class="flex items-center gap-3">
                                                    <span class="font-black text-gray-200 uppercase text-xs tracking-wider">{{ $comment->user->name }}</span>
                                                    <span class="text-[10px] text-gray-600 font-bold uppercase">{{ $comment->created_at->diffForHumans() }}</span>
                                                </div>
                                                
                                                @auth
                                                    @if($comment->user_id === auth()->id())
                                                        <div class="flex items-center gap-2">
                                                            <button @click="editing = !editing" 
                                                                class="p-2 rounded-lg bg-white/5 border border-white/10 text-gray-500 hover:text-lunar-accent hover:border-lunar-accent/50 hover:bg-lunar-accent/5 transition-soft"
                                                                title="Edit Transmission">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                                </svg>
                                                            </button>
                                                            <form action="{{ route('comments.destroy', $comment) }}" method="POST" onsubmit="return confirm('Delete this transmission permanently?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" 
                                                                    class="p-2 rounded-lg bg-white/5 border border-white/10 text-gray-500 hover:text-red-500 hover:border-red-500/50 hover:bg-red-500/5 transition-soft"
                                                                    title="Delete Intel">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                    </svg>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @endif
                                                @endauth
                                            </div>

                                            <div x-show="!editing">
                                                <p class="text-gray-400 text-sm leading-relaxed">{{ $comment->content }}</p>
                                            </div>

                                            <div x-show="editing" x-cloak>
                                                <form action="{{ route('comments.update', $comment) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <textarea name="content" required class="w-full bg-lunar-base border border-lunar-border rounded-2xl p-4 focus:border-lunar-accent outline-none transition-soft text-sm font-medium min-h-[80px] resize-none mb-3">{{ $comment->content }}</textarea>
                                                    <div class="flex justify-end">
                                                        <button type="submit" class="bg-lunar-accent text-white px-6 py-2 rounded-xl font-black uppercase tracking-widest text-[10px] shadow-lg shadow-lunar-accent/20 hover:scale-105 transition-soft">
                                                            Update Intel
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-10 text-center">
                                        <p class="text-gray-600 font-bold uppercase tracking-widest text-xs italic">No transmissions recorded in this sector yet.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
