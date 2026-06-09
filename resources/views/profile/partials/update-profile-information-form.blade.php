<section>
    <header>
        <h2 class="text-xl font-black font-orbitron text-lunar-accent uppercase italic">
            {{ __('Profile Identity') }}
        </h2>

        <p class="mt-1 text-sm text-gray-500 font-medium italic">
            {{ __("Update your account's profile information and digital signature.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-8 space-y-8" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <!-- Profile Photo -->
        <div class="flex items-center gap-8 bg-lunar-base/30 p-6 rounded-[30px] border border-lunar-border">
            <div class="relative group flex-shrink-0">
                <div class="w-28 h-28 rounded-full overflow-hidden border-4 border-lunar-accent shadow-lg shadow-lunar-accent/10 group-hover:border-white transition-soft">
                    @if($user->profile_photo)
                        <img id="avatar-preview" src="{{ asset($user->profile_photo) }}" class="w-full h-full object-cover">
                    @else
                        <div id="avatar-placeholder" class="w-full h-full bg-lunar-card flex items-center justify-center text-4xl font-black text-lunar-accent font-orbitron">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <img id="avatar-preview" src="" class="w-full h-full object-cover hidden">
                    @endif
                </div>
                <label for="profile_photo" class="absolute bottom-1 right-1 bg-lunar-accent text-white p-2 rounded-full cursor-pointer shadow-xl hover:scale-110 transition-soft border-2 border-lunar-card">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </label>
            </div>
            <div>
                <h4 class="text-sm font-black font-orbitron text-gray-300 uppercase tracking-widest mb-1">Avatar Transmission</h4>
                <input id="profile_photo" name="profile_photo" type="file" class="hidden" accept="image/*" 
                    onchange="document.getElementById('avatar-preview').src = window.URL.createObjectURL(this.files[0]); document.getElementById('avatar-preview').classList.remove('hidden'); if(document.getElementById('avatar-placeholder')) document.getElementById('avatar-placeholder').classList.add('hidden');">
                <p class="text-[10px] text-gray-600 font-bold uppercase tracking-[0.2em] mb-3">Square, Max 2MB, JPG/PNG/WEBP</p>
                <button type="button" onclick="document.getElementById('profile_photo').click()" class="text-[10px] bg-white/5 border border-white/10 text-white px-4 py-2 rounded-lg font-black uppercase hover:bg-lunar-accent transition-soft">
                    Choose Image
                </button>
                <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
            </div>
        </div>

        <div class="space-y-6">
            <div>
                <x-input-label for="name" :value="__('Name')" class="font-bold text-gray-400 uppercase tracking-widest text-[10px] mb-2" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full bg-lunar-base border-lunar-border focus:border-lunar-accent rounded-2xl py-4 px-6" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="email" :value="__('Email')" class="font-bold text-gray-400 uppercase tracking-widest text-[10px] mb-2" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full bg-lunar-base border-lunar-border focus:border-lunar-accent rounded-2xl py-4 px-6" :value="old('email', $user->email)" required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
