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
        background: rgba(13, 13, 13, 0.35);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }
    .btn-nav {
        background: rgba(26, 26, 26, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        transition: all 0.3s ease;
    }
    .btn-nav:hover:not(:disabled) {
        border-color: #7b7bff;
        color: #7b7bff;
        background: rgba(123, 123, 255, 0.1);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
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
        showSettings: false,
        imageWidth: localStorage.getItem('readerWidth') || 'medium',
        autoScroll: false,
        scrollSpeed: parseInt(localStorage.getItem('scrollSpeed') || '2'),
        scrollInterval: null,
        startAutoScroll() {
            if (this.scrollInterval) cancelAnimationFrame(this.scrollInterval);
            
            const scroll = () => {
                if (!this.autoScroll) return;
                
                const speedVal = parseFloat(this.scrollSpeed) || 2;
                // Exponential scaling for pixel step per frame:
                // Speed 1: 1px (~60 px/s at 60fps)
                // Speed 5: ~7px (~420 px/s at 60fps)
                // Speed 10: ~23px (~1380 px/s at 60fps)
                const step = Math.pow(speedVal, 1.8) * 0.35 + 0.65;
                
                window.scrollBy(0, Math.round(step));
                
                const actualY = window.scrollY || window.pageYOffset || document.documentElement.scrollTop || 0;
                const maxScroll = (document.documentElement.scrollHeight || document.body.scrollHeight) - window.innerHeight;
                
                // Only stop at bottom if page is actually scrollable and we reached the bottom limit
                if (maxScroll > 300 && actualY >= maxScroll - 15) {
                    this.stopAutoScroll();
                } else {
                    this.scrollInterval = requestAnimationFrame(scroll);
                }
            };
            
            this.autoScroll = true;
            this.scrollInterval = requestAnimationFrame(scroll);
        },
        stopAutoScroll() {
            if (this.scrollInterval) {
                cancelAnimationFrame(this.scrollInterval);
                this.scrollInterval = null;
            }
            this.autoScroll = false;
        },
        toggleAutoScroll() {
            this.autoScroll = !this.autoScroll;
            if (this.autoScroll) {
                this.startAutoScroll();
            } else {
                this.stopAutoScroll();
            }
        },
        atBottom: false,
        showEndPopup: false,
        hasTriggeredPopup: false,
        nextUrl: '{{ $nextChapter ? route('manga.read', ['type' => $type, 'mangaId' => $mangaId, 'chapterId' => $nextChapter['id']]) : '' }}',
        isRedirecting: false,
        canTrigger: false,
        triggerRedirect() {
            if (this.isRedirecting || !this.autoNext || !this.nextUrl || !this.canTrigger) return;
            
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
        // Stop auto scroll when manual scrolling is detected
        const stopOnManual = () => {
            if (autoScroll) {
                stopAutoScroll();
            }
        };
        window.addEventListener('wheel', stopOnManual, { passive: true });
        window.addEventListener('touchmove', stopOnManual, { passive: true });
        window.addEventListener('keydown', (e) => {
            if (autoScroll) {
                const keys = ['Space', ' ', 'ArrowUp', 'ArrowDown', 'PageUp', 'PageDown', 'Home', 'End'];
                if (keys.includes(e.key) || keys.includes(e.code)) {
                    stopAutoScroll();
                }
            }
        }, { passive: true });

        // Enable auto-trigger only after a short delay to prevent scroll-preservation loops
        setTimeout(() => { canTrigger = true }, 2000);

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

        // Intersection Observer for Auto Next & Pop-up
        const observer = new IntersectionObserver((entries) => {
            atBottom = entries[0].isIntersecting;
            if (atBottom) {
                if (autoNext) {
                    triggerRedirect();
                } else if (!hasTriggeredPopup) {
                    showEndPopup = true;
                    hasTriggeredPopup = true;
                }
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
    <div class="reader-nav fixed top-20 left-0 w-full z-50 py-4 px-6 transition-transform duration-500"
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

                <a href="/manga/{{ $type }}/{{ $mangaId }}" class="btn-nav px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest text-lunar-accent hover:text-white">
                    CHAPTER LIST
                </a>

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
                        :style="imageWidth === 'narrow' ? 'max-width: 700px !important' : (imageWidth === 'medium' ? 'max-width: 1000px !important' : (imageWidth === 'wide' ? 'max-width: 1300px !important' : 'max-width: 100% !important'))"
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
                    <h2 class="text-2xl font-black font-orbitron uppercase italic">Comment <span class="text-lunar-accent">Chapter</span></h2>
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
                                <p class="text-gray-700 font-bold uppercase tracking-widest text-[10px] italic">masih kosong 🥲</p>
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

    <!-- Bottom Reader Bar (Floating Pill Menu) -->
    <div class="reader-nav fixed bottom-6 left-1/2 -translate-x-1/2 w-[calc(100%-3rem)] max-w-md z-50 border border-lunar-border py-3 px-5 rounded-[24px] transition-all duration-500 shadow-2xl flex items-center justify-between"
         :class="!showUI ? 'translate-y-28 opacity-0 pointer-events-none' : 'translate-y-0 opacity-100'"
         @click.stop>
        <!-- Left: Prev Chapter -->
        <div>
            @if($prevChapter)
                <a href="{{ route('manga.read', ['type' => $type, 'mangaId' => $mangaId, 'chapterId' => $prevChapter['id']]) }}" 
                    class="btn-nav px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest block">
                    PREV
                </a>
            @else
                <button disabled class="btn-nav px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest block">PREV</button>
            @endif
        </div>

        <!-- Center: Chapter List/Exit & Auto Next & Settings -->
        <div class="flex items-center gap-2">
            <!-- Auto Next Toggle -->
            <button 
                @click.stop="autoNext = !autoNext" 
                class="flex items-center gap-2 px-3 py-2 rounded-xl border transition-soft"
                :class="autoNext ? 'bg-lunar-neon/10 border-lunar-neon text-lunar-neon' : 'bg-white/5 border-white/10 text-gray-500'">
                <div class="w-1.5 h-1.5 rounded-full" :class="autoNext ? 'bg-lunar-neon animate-pulse' : 'bg-gray-500'"></div>
                <span class="text-[9px] font-black uppercase tracking-widest">AUTO</span>
            </button>
            <a href="/manga/{{ $type }}/{{ $mangaId }}" 
               class="bg-white/5 text-gray-400 border border-white/5 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-white/10 hover:text-white transition-soft block">
                EXIT
            </a>
            <button 
                @click.stop="showSettings = !showSettings" 
                class="bg-white/5 text-gray-400 border border-white/5 px-3 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-white/10 hover:text-white transition-soft block"
                title="Settings">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </button>
        </div>

        <!-- Right: Next Chapter -->
        <div>
            @if($nextChapter)
                <a href="{{ route('manga.read', ['type' => $type, 'mangaId' => $mangaId, 'chapterId' => $nextChapter['id']]) }}" 
                    class="btn-nav px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border-lunar-accent text-lunar-accent block">
                    NEXT
                </a>
            @else
                <button disabled class="btn-nav px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest block">NEXT</button>
            @endif
        </div>
    </div>

    <!-- Settings Panel -->
    <div x-show="showSettings"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-10"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-10"
         class="fixed bottom-24 left-1/2 -translate-x-1/2 z-50 w-[90%] max-w-sm bg-black/20 backdrop-blur-2xl border border-white/10 p-6 rounded-[30px] shadow-2xl"
         @click.stop
         x-cloak>
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-black font-orbitron text-xs uppercase tracking-wider text-lunar-accent">Reader Settings</h3>
            <button @click="showSettings = false" class="text-gray-500 hover:text-white transition-soft">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="space-y-6">
            <!-- Reading Width -->
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">Reading Width</label>
                <div class="grid grid-cols-4 gap-2">
                    <template x-for="w in ['narrow', 'medium', 'wide', 'full']">
                        <button @click="imageWidth = w; localStorage.setItem('readerWidth', w)"
                                :class="imageWidth === w ? 'bg-lunar-accent text-white border-lunar-accent' : 'bg-white/5 text-gray-400 border-white/5 hover:bg-white/10'"
                                class="py-2.5 rounded-xl border text-[9px] font-black uppercase tracking-widest transition-soft"
                                x-text="w"></button>
                    </template>
                </div>
            </div>

            <!-- Auto Next -->
            <div class="flex items-center justify-between pt-4 border-t border-white/5">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400">Auto Next Chapter</label>
                    <span class="text-[9px] text-gray-500 font-medium">Load next chapter automatically</span>
                </div>
                <button @click="autoNext = !autoNext"
                        :class="autoNext ? 'bg-lunar-neon text-black' : 'bg-white/10 text-gray-400'"
                        class="px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest transition-soft"
                        x-text="autoNext ? 'ON' : 'OFF'"></button>
            </div>

            <!-- Auto Scroll Option -->
            <div class="pt-4 border-t border-white/5 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400">Auto Scroll</label>
                        <span class="text-[9px] text-gray-500 font-medium">Scroll down automatically</span>
                    </div>
                    <button @click="toggleAutoScroll()"
                            :class="autoScroll ? 'bg-lunar-neon text-black' : 'bg-white/10 text-gray-400'"
                            class="px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest transition-soft"
                            x-text="autoScroll ? 'ON' : 'OFF'"></button>
                </div>
                <!-- Speed Slider -->
                <div x-show="autoScroll" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="space-y-2">
                    <div class="flex justify-between items-center text-[9px] font-black uppercase tracking-widest text-gray-500">
                        <span>Speed</span>
                        <span class="text-lunar-accent" x-text="scrollSpeed + 'x'"></span>
                    </div>
                    <input type="range" min="1" max="10" x-model.number="scrollSpeed" @input="localStorage.setItem('scrollSpeed', scrollSpeed); if(autoScroll) startAutoScroll();"
                           class="w-full accent-lunar-accent bg-white/10 rounded-lg appearance-none h-1.5 cursor-pointer">
                </div>
            </div>
        </div>
    </div>

    <!-- End of Chapter Pop-up Modal -->
    <div x-show="showEndPopup" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-[150] flex items-center justify-center p-6 bg-black/75 backdrop-blur-sm"
         @click="showEndPopup = false"
         x-cloak>
        <div class="bg-lunar-card border border-lunar-border rounded-[32px] p-8 max-w-sm w-full text-center shadow-2xl relative"
             @click.stop>
            <!-- Close button -->
            <button @click="showEndPopup = false" class="absolute top-4 right-4 text-gray-500 hover:text-white transition-soft">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Icon / Graphic -->
            <div class="w-16 h-16 bg-lunar-accent/10 border border-lunar-accent/20 text-lunar-accent rounded-2xl flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <h3 class="text-2xl font-black font-orbitron mb-6 uppercase tracking-wide">
                End of <span class="text-lunar-accent">Chapter</span>
            </h3>

            <div class="flex flex-col gap-3">
                @if($nextChapter)
                    <a href="{{ route('manga.read', ['type' => $type, 'mangaId' => $mangaId, 'chapterId' => $nextChapter['id']]) }}" 
                        class="w-full bg-lunar-accent text-white py-3.5 rounded-xl font-black uppercase tracking-widest text-xs shadow-lg shadow-lunar-accent/20 hover:scale-[1.02] transition-soft">
                        Next Chapter
                    </a>
                @endif
                <div class="flex gap-3">
                    <a href="/manga/{{ $type }}/{{ $mangaId }}" 
                       class="flex-1 bg-white/5 text-gray-300 py-3 rounded-xl font-black uppercase tracking-widest text-[10px] border border-white/5 hover:bg-white/10 hover:border-white/10 transition-soft">
                        Exit
                    </a>
                    <button @click="showEndPopup = false" 
                       class="flex-grow bg-white/5 text-gray-500 py-3 rounded-xl font-black uppercase tracking-widest text-[10px] border border-white/5 hover:text-white transition-soft">
                        Stay Here
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
