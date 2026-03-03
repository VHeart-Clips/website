<x-layout :title="__('team.page_title')">
    <div class="mx-auto px-4 py-16 sm:py-24">
        <div class="mb-24 text-center">
            <h1 class="mb-6 text-6xl font-black tracking-tighter sm:text-7xl md:text-8xl">
                <span
                    class="bg-linear-to-b bg-clip-text text-transparent  from-zinc-950 via-zinc-800 to-purple-600  dark:from-white dark:via-white dark:to-purple-500/50"
                >
                    {{ __('team.our_team') }}
                </span>
            </h1>
            <div class="mx-auto h-1.5 w-24 rounded-full bg-linear-to-r from-purple-500 to-cyan-500"></div>
            <p class="mt-8 text-sm font-bold tracking-[0.3em] text-purple-600 uppercase dark:text-cyan-400">
                {{ __('team.total_members', ['count' => $total]) }}
            </p>
        </div>
        <div class="space-y-32">
            @forelse($roles as $role)
                <x-team.role :role="$role"/>
            @empty
                <div
                    class="flex h-64 flex-col items-center justify-center rounded-3xl border-2 border-dashed border-purple-100 bg-white/50 backdrop-blur-xl dark:border-white/5 dark:bg-white/5">
                    <h3 class="text-xl font-bold tracking-widest text-zinc-400 uppercase dark:text-white/40">
                        {{ __('team.no_team_data') }}
                    </h3>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-white/30">
                        {{ __('team.no_team_description') }}
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</x-layout>
