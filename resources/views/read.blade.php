@extends('layouts.ruana')

@section('title', 'Reading - Ruana Manwha')

@section('styles')
<style>
    body {
        overflow-y: auto !important;
    }
    .reader-container img {
        width: 100%;
        max-width: 900px;
        margin: 0 auto;
        display: block;
        box-shadow: 0 0 50px rgba(0,0,0,0.5);
    }
    .reader-nav {
        position: fixed;
        bottom: 40px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 50;
    }
</style>
@endsection

@section('content')
<div class="bg-black min-h-screen pt-10 pb-40">
    
    <!-- Reader Header -->
    <div class="max-w-4xl mx-auto px-6 mb-12 flex items-center justify-between">
        <a href="{{ route('manga.show', ['type' => $type, 'id' => $mangaId]) }}" class="flex items-center gap-3 text-gray-500 hover:text-white transition-soft font-bold">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            BACK TO STORY
        </a>
        <div class="text-center">
            <span class="text-lunar-accent font-black tracking-widest text-xs uppercase block mb-1">{{ $mangaTitle }}</span>
            <span class="text-2xl font-black font-orbitron">CHAPTER {{ $chapterId }}</span>
        </div>
        <div class="w-24"></div> <!-- Spacer -->
    </div>

    <!-- Images -->
    <div class="reader-container flex flex-col">
        @if(isset($error))
            <div class="max-w-4xl mx-auto px-6 py-20 text-center">
                <div class="mb-8 inline-flex items-center justify-center w-24 h-24 rounded-full bg-red-500/10 border border-red-500/20 text-red-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h2 class="text-3xl font-black font-orbitron text-white mb-4">ACCESS DENIED</h2>
                <p class="text-gray-400 text-lg mb-8 max-w-md mx-auto">{{ $error }}</p>
                <div class="flex flex-col gap-4">
                    <a href="{{ route('manga.show', ['type' => $type, 'id' => $mangaId]) }}" class="bg-white/5 border border-white/10 text-white font-black py-4 px-8 rounded-2xl hover:bg-white/10 transition-soft">
                        RETURN TO STORY
                    </a>
                </div>
            </div>
        @endif

        @foreach($images as $image)
            @php
                $imgUrl = $image;
                if((str_contains($imgUrl, 'comicazen.com') || str_contains($imgUrl, 'lunaranime.ru') || str_starts_with($imgUrl, '/')) && !str_contains($imgUrl, asset(''))) {
                    $imgUrl = route('proxy.image', ['url' => $imgUrl]);
                }
            @endphp
            <img src="{{ $imgUrl }}" class="loading-lazy" alt="Page">
        @endforeach
    </div>

    <!-- Bottom Nav -->
    <div class="reader-nav flex items-center gap-4 bg-lunar-card/80 backdrop-blur-xl border border-white/10 p-2 rounded-[24px] shadow-2xl">
        @if($prevChapter)
            <a href="{{ route('manga.read', ['type' => $type, 'mangaId' => $mangaId, 'chapterId' => $prevChapter['id']]) }}" 
               class="w-14 h-14 rounded-2xl bg-white/5 flex items-center justify-center hover:bg-lunar-accent transition-soft group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
        @else
            <div class="w-14 h-14 rounded-2xl bg-white/5 flex items-center justify-center opacity-20 cursor-not-allowed">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </div>
        @endif
        
        <div class="px-6 py-2">
            <span class="font-black font-orbitron text-sm tracking-widest uppercase">CHAPTER {{ $chapterId }}</span>
        </div>

        @if($nextChapter)
            <a href="{{ route('manga.read', ['type' => $type, 'mangaId' => $mangaId, 'chapterId' => $nextChapter['id']]) }}" 
               class="w-14 h-14 rounded-2xl bg-lunar-accent flex items-center justify-center hover:opacity-90 transition-soft">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        @else
            <div class="w-14 h-14 rounded-2xl bg-white/5 flex items-center justify-center opacity-20 cursor-not-allowed">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        @endif
    </div>

</div>
@endsection
