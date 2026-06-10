@extends('layouts.ruana')

@section('title', 'Bookmarks - Ruana Manwha')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-12">
    <div class="flex items-center justify-between mb-12">
        <h1 class="text-4xl font-black font-orbitron uppercase italic">
            SAVED <span class="text-lunar-accent">REALMS</span>
        </h1>
    </div>

    @if($bookmarks->count() > 0)
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-8">
            @foreach($bookmarks as $bookmark)
                @php $manga = $bookmark->manga; @endphp
                <a href="{{ route('manga.show', ['type' => $manga->source_type, 'id' => $manga->source_id]) }}" class="group block">
                    <div class="relative aspect-[3/4.5] rounded-3xl overflow-hidden mb-5 border border-lunar-border shadow-xl group-hover:border-lunar-accent/50 group-hover:shadow-lunar-accent/10 transition-soft">
                        <img src="@proxy($manga->cover_url)" alt="{{ $manga->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-soft">
                        <div class="absolute inset-0 bg-gradient-to-t from-lunar-base via-transparent to-transparent opacity-60"></div>
                    </div>
                    <h3 class="font-black text-gray-200 group-hover:text-lunar-accent transition-soft line-clamp-2 leading-tight uppercase text-sm mb-1">
                        {{ $manga->title }}
                    </h3>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] text-gray-500 uppercase tracking-widest font-bold">{{ $manga->genre ?? 'Discovery' }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="bg-lunar-card/30 border border-dashed border-lunar-border rounded-[40px] py-32 text-center">
            <div class="w-20 h-20 bg-lunar-accent/10 text-lunar-accent rounded-3xl flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                </svg>
            </div>
            <h2 class="text-2xl font-black font-orbitron text-gray-400 uppercase tracking-widest mb-4">No Bookmarks Found</h2>
            <p class="text-gray-600 mb-10 max-w-md mx-auto">Your saved realms will appear here. Start exploring and bookmark your favorite manwha to track them easily.</p>
            <a href="/explore" class="inline-block bg-lunar-accent text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest text-sm shadow-lg shadow-lunar-accent/20 hover:scale-105 transition-soft">
                Start Exploring
            </a>
        </div>
    @endif
</div>
@endsection
