@extends('layouts.ruana')

@section('title', $mangaTitle . ' - ' . $chapterId)

@section('styles')
<style>
    .reader-image {
        max-width: 1000px;
        margin: 0 auto;
        display: block;
        width: 100%;
        height: auto;
    }
    .reader-nav {
        background: rgba(13, 13, 13, 0.9);
        backdrop-filter: blur(10px);
    }
    .btn-nav {
        background: #1a1a1a;
        border: 1px solid #333;
        transition: all 0.3s ease;
    }
    .btn-nav:hover:not(:disabled) {
        border-color: #7b7bff;
        color: #7b7bff;
        background: #0d0d0d;
    }
    .btn-nav:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }
</style>
@endsection

@section('content')
<div class="min-h-screen bg-[#050505]">
    
    <!-- Top Reader Bar -->
    <div class="reader-nav sticky top-20 z-50 border-b border-lunar-border py-4 px-6">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="/manga/{{ $type }}/{{ $mangaId }}" class="text-gray-500 hover:text-white transition-soft">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="font-black font-orbitron text-sm uppercase tracking-tighter truncate max-w-[200px] md:max-w-md">
                        {{ $mangaTitle }}
                    </h1>
                    <p class="text-[10px] text-lunar-accent font-bold uppercase tracking-widest">{{ $chapterId }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if($prevChapter)
                    <a href="{{ route('manga.read', ['type' => $type, 'mangaId' => $mangaId, 'chapterId' => $prevChapter['id']]) }}" 
                        class="btn-nav px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest">
                        PREV
                    </a>
                @else
                    <button disabled class="btn-nav px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest">PREV</button>
                @endif

                <div class="bg-lunar-base border border-lunar-border px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest text-lunar-accent">
                    CHAPTER LIST
                </div>

                @if($nextChapter)
                    <a href="{{ route('manga.read', ['type' => $type, 'mangaId' => $mangaId, 'chapterId' => $nextChapter['id']]) }}" 
                        class="btn-nav px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest border-lunar-accent text-lunar-accent">
                        NEXT
                    </a>
                @else
                    <button disabled class="btn-nav px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest">NEXT</button>
                @endif
            </div>
        </div>
    </div>

    <!-- Reader Content -->
    <div class="py-10">
        @if(isset($error))
            <div class="max-w-2xl mx-auto p-12 bg-red-500/10 border border-red-500/20 rounded-[40px] text-center">
                <h2 class="text-2xl font-black font-orbitron text-red-500 mb-4 uppercase">TRANSMISSION ERROR</h2>
                <p class="text-gray-400 font-medium mb-8">{{ $error }}</p>
                <a href="/manga/{{ $type }}/{{ $mangaId }}" class="inline-block bg-red-500 text-white px-8 py-4 rounded-2xl font-black uppercase tracking-widest text-sm">Return to Details</a>
            </div>
        @else
            <div class="flex flex-col items-center">
                @foreach($images as $image)
                    <img src="@proxy($image)" 
                        class="reader-image" 
                        loading="lazy"
                        onerror="this.onerror=null; this.src='{{ route('proxy.image', ['url' => $image]) }}'">
                @endforeach
            </div>
        @endif
    </div>

    <!-- Bottom Nav -->
    <div class="py-20 border-t border-lunar-border bg-lunar-card/30 backdrop-blur-md">
        <div class="max-w-lg mx-auto text-center px-6">
            <h3 class="text-2xl font-black font-orbitron mb-8 uppercase italic">End of <span class="text-lunar-accent">Chapter</span></h3>
            <div class="flex flex-col gap-4">
                @if($nextChapter)
                    <a href="{{ route('manga.read', ['type' => $type, 'mangaId' => $mangaId, 'chapterId' => $nextChapter['id']]) }}" 
                        class="w-full bg-lunar-accent text-white py-5 rounded-3xl font-black uppercase tracking-[0.2em] shadow-2xl shadow-lunar-accent/20 hover:scale-105 transition-soft">
                        NEXT CHAPTER
                    </a>
                @endif
                <a href="/manga/{{ $type }}/{{ $mangaId }}" class="w-full bg-white/5 text-gray-400 py-5 rounded-3xl font-black uppercase tracking-[0.2em] border border-white/5 hover:bg-white/10 transition-soft">
                    BACK TO DETAILS
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
