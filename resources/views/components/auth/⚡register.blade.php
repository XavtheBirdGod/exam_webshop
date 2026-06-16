<?php

use App\Livewire\Forms\RegisterForm;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new #[Layout('components.layouts.app')] class extends Component
{
    public RegisterForm $form;

    public function register()
    {
        $user = $this->form->store();

        Auth::login($user);

        return redirect()->to('/');
    }
};
?>

<div class="min-h-[80dvh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white/5 p-10 rounded-[2rem] border border-white/10 backdrop-blur-xl">
        <div>
            <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-[#d4a574]">Create a Rituals Account</h2>
            <p class="mt-2 text-center text-sm text-[#9a9590]">
                Or <a href="/login" class="font-medium text-[#c9b896] hover:text-[#d4a574]">sign in to your account</a>
            </p>
        </div>
        <form class="mt-8 space-y-6" wire:submit="register">
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Full name</label>
                    <input wire:model="form.name" id="name" name="name" type="text" autocomplete="name" required class="appearance-none rounded-full relative block w-full px-6 py-3 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] placeholder-[#9a9590] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm" placeholder="Full name">
                    @error('form.name') <span class="text-sm text-red-400 mt-1 block font-accent">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="email-address" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Email address</label>
                    <input wire:model="form.email" id="email-address" name="email" type="email" autocomplete="email" required class="appearance-none rounded-full relative block w-full px-6 py-3 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] placeholder-[#9a9590] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm" placeholder="Email address">
                    @error('form.email') <span class="text-sm text-red-400 mt-1 block font-accent">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="password" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Password</label>
                    <input wire:model="form.password" id="password" name="password" type="password" required class="appearance-none rounded-full relative block w-full px-6 py-3 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] placeholder-[#9a9590] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm" placeholder="Password">
                    @error('form.password') <span class="text-sm text-red-400 mt-1 block font-accent">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Confirm password</label>
                    <input wire:model="form.password_confirmation" id="password_confirmation" name="password_confirmation" type="password" required class="appearance-none rounded-full relative block w-full px-6 py-3 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] placeholder-[#9a9590] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm" placeholder="Confirm password">
                    @error('form.password_confirmation') <span class="text-sm text-red-400 mt-1 block font-accent">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-sm font-bold rounded-full text-[#0f0f0f] bg-[#d4a574] hover:brightness-110 active:scale-95 transition-all uppercase tracking-widest">
                    Create Account
                </button>
            </div>
        </form>
    </div>
</div>