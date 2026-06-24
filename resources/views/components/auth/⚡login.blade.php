<?php

use App\Livewire\Forms\LoginForm;
use Livewire\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app')] class extends Component
{
    public LoginForm $form;

    public function login()
    {
        $this->form->authenticate();

        $user = auth()->user();

        if ($user->isPlatformAdmin()) {
            $centralDomains = config('tenancy.central_domains', ['localhost']);
            if (in_array(request()->getHost(), $centralDomains)) {
                return redirect()->intended('/admin/dashboard');
            }

            $centralDomain = reset($centralDomains);
            $scheme = request()->getScheme();
            $port = request()->getPort();
            $portSuffix = ($port && !in_array($port, [80, 443])) ? ":{$port}" : "";

            return redirect()->away("{$scheme}://{$centralDomain}{$portSuffix}/admin/dashboard");
        }

        $primaryTenant = $user->tenants()->wherePivotIn('role', ['shop_admin', 'shop_staff'])->first();

        if ($primaryTenant && $primaryTenant->domains->count() > 0) {
            $domain = $primaryTenant->domains->first()->domain;
            $scheme = request()->getScheme();
            $port = request()->getPort();
            $portSuffix = ($port && !in_array($port, [80, 443])) ? ":{$port}" : "";

            return redirect()->away("{$scheme}://{$domain}{$portSuffix}/seller/dashboard");
        }

        return redirect()->intended('/');
    }
};
?>

<div class="min-h-[80dvh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white/5 p-10 rounded-[2rem] border border-white/10 backdrop-blur-xl">
        <div>
            <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-[#d4a574]">Sign in to Rituals</h2>
            <p class="mt-2 text-center text-sm text-[#9a9590]">
                Or <a href="/register" class="font-medium text-[#c9b896] hover:text-[#d4a574]">create a new account</a>
            </p>
        </div>
        <form class="mt-8 space-y-6" wire:submit="login">
            <div class="space-y-4">
                <div>
                    <label for="email-address" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Email address</label>
                    <input wire:model="form.email" id="email-address" name="email" type="email" autocomplete="email" required class="appearance-none rounded-full relative block w-full px-6 py-3 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] placeholder-[#9a9590] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm" placeholder="Email address">
                    @error('form.email') <span class="text-sm text-red-400 mt-1 block font-accent">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="password" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Password</label>
                    <input wire:model="form.password" id="password" name="password" type="password" autocomplete="current-password" required class="appearance-none rounded-full relative block w-full px-6 py-3 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] placeholder-[#9a9590] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm" placeholder="Password">
                    @error('form.password') <span class="text-sm text-red-400 mt-1 block font-accent">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input wire:model="form.remember" id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-[#d4a574] focus:ring-[#d4a574] border-white/10 rounded bg-[#0f0f0f]">
                    <label for="remember-me" class="ml-2 block text-sm text-[#9a9590] font-accent uppercase tracking-widest">Remember me</label>
                </div>

                <div class="text-sm">
                    <a href="#" class="font-medium text-[#c9b896] hover:text-[#d4a574] font-accent uppercase tracking-widest">Forgot password?</a>
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-sm font-bold rounded-full text-[#0f0f0f] bg-[#d4a574] hover:brightness-110 active:scale-95 transition-all uppercase tracking-widest">
                    Sign in
                </button>
            </div>
        </form>
    </div>
</div>
