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
                <h2 class="text-2xl font-black font-orbitron mb-2 uppercase">{{ $user->name }}</h2>
                <p class="text-lunar-accent font-bold tracking-widest text-xs uppercase mb-8">Level 1 Reader</p>
                
                <div class="flex flex-col gap-4">
                    <div class="bg-lunar-base/50 p-4 rounded-2xl border border-lunar-border text-left">
                        <span class="text-[10px] text-gray-600 font-bold uppercase tracking-widest block mb-1">Email Signature</span>
                        <span class="text-gray-300 text-sm font-medium">{{ $user->email }}</span>
                    </div>
                    <div class="bg-lunar-base/50 p-4 rounded-2xl border border-lunar-border text-left">
                        <span class="text-[10px] text-gray-600 font-bold uppercase tracking-widest block mb-1">Account Role</span>
                        <span class="text-lunar-neon text-sm font-black uppercase">{{ $user->role ?? 'User' }}</span>
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
