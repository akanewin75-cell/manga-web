@extends('layouts.ruana')

@section('title', 'Profile - Ruana Manwha')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-20">
    <div class="mb-16">
        <h1 class="text-5xl font-black font-orbitron mb-4 uppercase italic">YOUR <span class="text-lunar-accent">PROFILE</span></h1>
        <p class="text-gray-500 text-lg font-medium italic">Manage your realm access and personal identity.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        <!-- Sidebar/Photo Info -->
        <div class="lg:col-span-1">
            <div class="bg-lunar-card border border-lunar-border rounded-[40px] p-10 text-center sticky top-28">
                <div class="relative w-48 h-48 mx-auto mb-8 group">
                    <div class="w-full h-full rounded-full overflow-hidden border-4 border-lunar-accent shadow-2xl shadow-lunar-accent/20 transition-soft group-hover:scale-105">
                        @if($user->profile_photo)
                            <img src="{{ asset($user->profile_photo) }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-lunar-base flex items-center justify-center text-7xl font-black text-lunar-accent font-orbitron">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                </div>
                @php
                    $level = floor($user->chapters_read / 10) + 1;
                    $progress = ($user->chapters_read % 10) * 10;
                @endphp
                <h2 class="text-2xl font-black font-orbitron mb-2 uppercase">{{ $user->name }}</h2>
                <p class="text-lunar-accent font-bold tracking-widest text-xs uppercase mb-2">Level {{ $level }} Reader</p>
                
                <!-- Progress Bar -->
                <div class="w-full bg-lunar-base/50 h-2 rounded-full mb-8 overflow-hidden border border-lunar-border">
                    <div class="bg-lunar-accent h-full shadow-[0_0_10px_rgba(123,123,255,0.5)] transition-all duration-1000" style="width: {{ $progress }}%"></div>
                </div>
                
                <div class="flex flex-col gap-4">
                    <div class="bg-lunar-base/50 p-4 rounded-2xl border border-lunar-border text-left">
                        <span class="text-[10px] text-gray-600 font-bold uppercase tracking-widest block mb-1">Email Signature</span>
                        <span class="text-gray-300 text-sm font-medium">{{ $user->email }}</span>
                    </div>
                    <div class="bg-lunar-base/50 p-4 rounded-2xl border border-lunar-border text-left">
                        <span class="text-[10px] text-gray-600 font-bold uppercase tracking-widest block mb-1">Account Role</span>
                        <span class="text-lunar-neon text-sm font-black uppercase">{{ $user->role ?? 'User' }}</span>
                    </div>
                    @php
                        $readerTitle = 'Citizen';
                        if ($user->role === 'admin') {
                            $readerTitle = 'Grand Duke';
                        } elseif ($level >= 180) {
                            $readerTitle = 'Grand Duke';
                        } elseif ($level >= 150) {
                            $readerTitle = 'Archduke';
                        } elseif ($level >= 120) {
                            $readerTitle = 'Duke';
                        } elseif ($level >= 90) {
                            $readerTitle = 'Viscount';
                        } elseif ($level >= 60) {
                            $readerTitle = 'Marques';
                        } elseif ($level >= 30) {
                            $readerTitle = 'Baron';
                        }
                    @endphp
                    <div class="bg-lunar-base/50 p-4 rounded-2xl border border-lunar-border text-left">
                        <span class="text-[10px] text-gray-600 font-bold uppercase tracking-widest block mb-1">Reader Title</span>
                        <span class="text-white text-sm font-black uppercase italic tracking-tighter">{{ $readerTitle }}</span>
                    </div>
                    <div class="bg-lunar-base/50 p-4 rounded-2xl border border-lunar-border text-left">
                        <span class="text-[10px] text-gray-600 font-bold uppercase tracking-widest block mb-1">Transmission Data</span>
                        <span class="text-white text-sm font-black uppercase">{{ $user->chapters_read }} Chapters Read</span>
                    </div>

                    <!-- 18+ Toggle -->
                    <div class="bg-lunar-base/50 p-4 rounded-2xl border border-lunar-border text-left"
                        x-data="{ 
                            nsfw: {{ session('nsfw_enabled', false) ? 'true' : 'false' }},
                            toggle() {
                                fetch('{{ route('toggle.nsfw') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({ enabled: !this.nsfw })
                                })
                                .then(res => res.json())
                                .then(data => {
                                    this.nsfw = data.nsfw_enabled;
                                });
                            }
                        }">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] text-gray-600 font-bold uppercase tracking-widest block">Restricted Content (18+)</span>
                            <button @click="toggle()" 
                                class="relative inline-flex h-5 w-10 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                :class="nsfw ? 'bg-red-600' : 'bg-lunar-border'">
                                <span class="sr-only">Toggle 18+ Content</span>
                                <span aria-hidden="true" 
                                    class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                    :class="nsfw ? 'translate-x-5' : 'translate-x-0'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Forms -->
        <div class="lg:col-span-2 space-y-12">
            <!-- Update Profile -->
            <div class="bg-lunar-card border border-lunar-border rounded-[40px] p-10">
                @include('profile.partials.update-profile-information-form')
            </div>

            <!-- Update Password -->
            <div class="bg-lunar-card border border-lunar-border rounded-[40px] p-10">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- Delete Account -->
            <div class="bg-red-500/5 border border-red-500/10 rounded-[40px] p-10">
                <div class="max-w-xl text-red-500">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
