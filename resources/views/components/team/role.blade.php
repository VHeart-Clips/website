@props(['role' => null])
<section class="space-y-8">
    <div class="flex items-center gap-4">
        <x-ui.badge class="rounded-full border-2 px-6 py-2 text-sm font-bold backdrop-blur-md  border-purple-100 bg-white text-purple-900 shadow-sm  dark:border-purple-500/30 dark:bg-zinc-900/60 dark:text-purple-300">
            {{ $role->name }}
        </x-ui.badge>
        <div class="h-0.5 flex-1 bg-linear-to-r from-purple-100 via-transparent to-transparent dark:from-white/10"></div>
        <span class="font-mono text-[10px] font-bold tracking-[0.2em] text-zinc-400 uppercase dark:text-white/30">
            {{ trans_choice('team.members', count($role->users), ['value' => count($role->users)]) }}
        </span>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach($role->users as $user)
            <x-team.member :user="$user" />
        @endforeach
    </div>
</section>
