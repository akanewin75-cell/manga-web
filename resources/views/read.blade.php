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

            <!-- Comments Section (Chapter End) -->
            <div class="max-w-4xl mx-auto px-6 mt-10 mb-32" @click.stop>
                <div class="flex items-center gap-4 mb-8">
                    <h2 class="text-2xl font-black font-orbitron uppercase italic">Chapter <span class="text-lunar-accent">Intel</span></h2>
                </div>

                <div class="bg-lunar-card/30 border border-lunar-border rounded-[32px] p-8 md:p-10">
                    @auth
                        <form action="{{ route('comments.store') }}" method="POST" class="flex gap-5 mb-10 text-left">
                            @csrf
                            <input type="hidden" name="source_type" value="{{ $type }}">
                            <input type="hidden" name="source_id" value="{{ $mangaId }}">
                            <input type="hidden" name="chapter_id" value="{{ $chapterId }}">

                            <div class="w-12 h-12 rounded-xl bg-lunar-accent flex-shrink-0 flex items-center justify-center font-bold overflow-hidden shadow-lg shadow-lunar-accent/20">
                                @if(auth()->user()->profile_photo)
                                    <img src="{{ asset(auth()->user()->profile_photo) }}" class="w-full h-full object-cover">
                                @else
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                @endif
                            </div>
                            <div class="flex-grow">
                                <textarea name="content" required placeholder="Leave a transmission for this chapter..." 
                                    class="w-full bg-lunar-base border border-lunar-border rounded-2xl p-4 focus:border-lunar-accent outline-none transition-soft text-sm font-medium min-h-[80px] resize-none"></textarea>
                                <div class="flex justify-end mt-3">
                                    <button type="submit" class="bg-lunar-accent text-white px-8 py-2.5 rounded-xl font-black uppercase tracking-widest text-[10px] shadow-lg shadow-lunar-accent/20 hover:scale-105 transition-soft">
                                        Send Transmission
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endauth

                    <div class="space-y-8 text-left">
                        @forelse($comments as $comment)
                            <div class="flex gap-4" x-data="{ editing: false }">
                                <div class="w-10 h-10 rounded-lg bg-lunar-accent/10 border border-lunar-accent/20 flex-shrink-0 flex items-center justify-center text-lunar-accent font-black text-xs overflow-hidden">
                                    @if($comment->user->profile_photo)
                                        <img src="{{ asset($comment->user->profile_photo) }}" class="w-full h-full object-cover">
                                    @else
                                        {{ substr($comment->user->name, 0, 1) }}
                                    @endif
                                </div>
                                <div class="flex-grow">
                                    <div class="flex items-center justify-between mb-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-black text-gray-300 uppercase text-[10px] tracking-wider">{{ $comment->user->name }}</span>
                                            <span class="text-[8px] text-gray-600 font-bold uppercase">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>

                                        @auth
                                            @if($comment->user_id === auth()->id())
                                                <div class="flex items-center gap-2">
                                                    <button @click="editing = !editing" 
                                                        class="p-1.5 rounded-lg bg-white/5 border border-white/10 text-gray-600 hover:text-lunar-accent hover:border-lunar-accent/50 transition-soft"
                                                        title="Edit Transmission">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                    </button>
                                                    <form action="{{ route('comments.destroy', $comment) }}" method="POST" onsubmit="return confirm('Delete this transmission?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                            class="p-1.5 rounded-lg bg-white/5 border border-white/10 text-gray-600 hover:text-red-500 hover:border-red-500/50 transition-soft"
                                                            title="Delete Intel">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        @endauth
                                    </div>

                                    <div x-show="!editing">
                                        <p class="text-gray-500 text-xs leading-relaxed">{{ $comment->content }}</p>
                                    </div>

                                    <div x-show="editing" x-cloak class="mt-2">
                                        <form action="{{ route('comments.update', $comment) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <textarea name="content" required class="w-full bg-lunar-base border border-lunar-border rounded-xl p-3 focus:border-lunar-accent outline-none transition-soft text-xs font-medium min-h-[60px] resize-none mb-2 text-gray-300">{{ $comment->content }}</textarea>
                                            <div class="flex justify-end">
                                                <button type="submit" class="bg-lunar-accent text-white px-5 py-1.5 rounded-lg font-black uppercase tracking-widest text-[8px] shadow-lg shadow-lunar-accent/20 hover:scale-105 transition-soft">
                                                    Update Intel
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="py-6 text-center">
                                <p class="text-gray-700 font-bold uppercase tracking-widest text-[10px] italic">No intel reported for this chapter yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
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
