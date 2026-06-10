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
<div class="min-h-screen bg-[#050505] relative" 
     x-data="{ 
        showUI: true, 
        autoNext: localStorage.getItem('autoNext') === 'true',
        atBottom: false,
        nextUrl: '{{ $nextChapter ? route('manga.read', ['type' => $type, 'mangaId' => $mangaId, 'chapterId' => $nextChapter['id']]) : '' }}',
        isRedirecting: false,
        triggerRedirect() {
            if (this.isRedirecting || !this.autoNext || !this.nextUrl) return;
            
            this.isRedirecting = true;
            setTimeout(() => {
                if (this.autoNext && this.isRedirecting) {
                    window.location.href = this.nextUrl;
                } else {
                    this.isRedirecting = false;
                }
            }, 3000);
        }
     }" 
     @click="showUI = !showUI"
     x-init="
        $watch('showUI', value => {
            const nav = document.querySelector('nav.fixed');
            if (nav) {
                if (value) {
                    nav.classList.remove('-translate-y-full');
                } else {
                    nav.classList.add('-translate-y-full');
                }
                nav.classList.add('transition-transform', 'duration-500');
            }
        });

        $watch('autoNext', value => {
            localStorage.setItem('autoNext', value);
            if (value && atBottom) {
                triggerRedirect();
            }
        });

        // Intersection Observer for Auto Next
        const observer = new IntersectionObserver((entries) => {
            atBottom = entries[0].isIntersecting;
            if (atBottom && autoNext) {
                triggerRedirect();
            }
        }, { threshold: 0.1 });

        if ($refs.endOfChapter) {
            observer.observe($refs.endOfChapter);
        }
     ">

    <!-- Redirect Overlay -->
    <template x-if="isRedirecting && autoNext">
        <div class="fixed inset-0 z-[200] bg-black/80 backdrop-blur-xl flex items-center justify-center text-center p-6">
            <div class="max-w-xs">
                <div class="w-16 h-16 border-4 border-lunar-accent border-t-transparent rounded-full animate-spin mx-auto mb-6"></div>
                <h2 class="text-2xl font-black font-orbitron mb-2 uppercase italic">Auto <span class="text-lunar-accent">Transmitting</span></h2>
                <p class="text-gray-400 text-sm font-medium uppercase tracking-widest mb-6">Loading next chapter in 3 seconds...</p>
                <button @click.stop="isRedirecting = false; autoNext = false" class="bg-white/10 text-white px-8 py-3 rounded-xl font-bold uppercase tracking-widest text-xs hover:bg-red-500 transition-soft">
                    Cancel Transmission
                </button>
            </div>
        </div>
    </template>
    
    <!-- Top Reader Bar -->
    <div class="reader-nav fixed top-20 left-0 w-full z-50 border-b border-lunar-border py-4 px-6 transition-transform duration-500"
         :class="!showUI ? '-translate-y-[200%]' : 'translate-y-0'"
         @click.stop>
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="/manga/{{ $type }}/{{ $mangaId }}" class="text-gray-500 hover:text-white transition-soft">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="font-black font-orbitron text-sm uppercase tracking-tighter truncate max-w-[200px] md:max-w-md">
                            {{ $mangaTitle }}
                        </h1>
                        <!-- Auto Next Toggle -->
                        <button 
                            @click.stop="autoNext = !autoNext" 
                            class="flex items-center gap-2 px-3 py-1 rounded-full border transition-soft"
                            :class="autoNext ? 'bg-lunar-neon/10 border-lunar-neon text-lunar-neon' : 'bg-white/5 border-white/10 text-gray-500'">
                            <div class="w-1.5 h-1.5 rounded-full" :class="autoNext ? 'bg-lunar-neon animate-pulse' : 'bg-gray-500'"></div>
                            <span class="text-[9px] font-black uppercase tracking-widest">Auto Next</span>
                        </button>
                    </div>
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
    <div class="py-10 pt-32">
        @if(isset($error))
            <div class="max-w-2xl mx-auto p-12 bg-red-500/10 border border-red-500/20 rounded-[40px] text-center">
                <h2 class="text-2xl font-black font-orbitron text-red-500 mb-4 uppercase">TRANSMISSION ERROR</h2>
                <p class="text-gray-400 font-medium mb-8">{{ $error }}</p>
                <a href="/manga/{{ $type }}/{{ $mangaId }}" class="inline-block bg-red-500 text-white px-8 py-4 rounded-2xl font-black uppercase tracking-widest text-sm" @click.stop>Return to Details</a>
            </div>
        @else
            <div class="flex flex-col items-center">
                @foreach($images as $image)
                    <img src="@proxy($image)" 
                        class="reader-image cursor-pointer" 
                        loading="lazy"
                        onerror="this.onerror=null; this.src='{{ route('proxy.image', ['url' => $image]) }}'">
                @endforeach
            </div>

            <!-- End of Chapter Anchor for Auto Next -->
            <div x-ref="endOfChapter" class="h-20 w-full flex items-center justify-center">
                <template x-if="autoNext && nextUrl">
                    <div class="flex items-center gap-2 text-lunar-neon opacity-50">
                        <div class="w-1.5 h-1.5 rounded-full bg-lunar-neon animate-pulse"></div>
                        <span class="text-[10px] font-black uppercase tracking-widest">Auto Next Ready</span>
                    </div>
                </template>
            </div>
        @endif
    </div>

    <!-- Quick Nav Buttons (Right Side) -->
    <div class="fixed right-6 top-1/2 -translate-y-1/2 flex flex-col gap-4 z-[100] transition-transform duration-500"
         :class="!showUI ? 'translate-x-[200%]' : 'translate-x-0'">
        <button @click.stop="window.scrollTo({top: 0, behavior: 'smooth'})" 
                class="w-12 h-12 rounded-2xl bg-lunar-card/80 backdrop-blur-xl border border-lunar-border text-gray-400 hover:text-lunar-accent hover:border-lunar-accent transition-soft shadow-2xl flex items-center justify-center group"
                title="Scroll to Top">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 group-hover:-translate-y-1 transition-soft" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7" />
            </svg>
        </button>
        <div class="h-8 w-px bg-lunar-border mx-auto"></div>
        <button @click.stop="window.scrollTo({top: document.body.scrollHeight, behavior: 'smooth'})" 
                class="w-12 h-12 rounded-2xl bg-lunar-card/80 backdrop-blur-xl border border-lunar-border text-gray-400 hover:text-lunar-accent hover:border-lunar-accent transition-soft shadow-2xl flex items-center justify-center group"
                title="Scroll to Bottom">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 group-hover:translate-y-1 transition-soft" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
    </div>

    <!-- Bottom Nav -->
    <div class="fixed bottom-0 left-0 w-full py-10 border-t border-lunar-border bg-lunar-card/30 backdrop-blur-md z-50 transition-transform duration-500"
         :class="!showUI ? 'translate-y-full' : 'translate-y-0'"
         @click.stop>
        <div class="max-w-lg mx-auto text-center px-6">
            <h3 class="text-xl font-black font-orbitron mb-6 uppercase italic">End of <span class="text-lunar-accent">Chapter</span></h3>
            <div class="flex items-center gap-4">
                @if($nextChapter)
                    <a href="{{ route('manga.read', ['type' => $type, 'mangaId' => $mangaId, 'chapterId' => $nextChapter['id']]) }}" 
                        class="flex-grow bg-lunar-accent text-white py-4 rounded-2xl font-black uppercase tracking-widest shadow-2xl shadow-lunar-accent/20 hover:scale-105 transition-soft text-sm">
                        NEXT CHAPTER
                    </a>
                @endif
                <a href="/manga/{{ $type }}/{{ $mangaId }}" class="flex-grow bg-white/5 text-gray-400 py-4 rounded-2xl font-black uppercase tracking-widest border border-white/5 hover:bg-white/10 transition-soft text-sm">
                    EXIT
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
