@props(['user' => null])
<div
    class="group relative flex items-center gap-4 rounded-2xl border p-4 transition-all duration-300  border-white bg-white/95 shadow-[0_8px_30px_rgb(0,0,0,0.04)] backdrop-blur-xl  dark:border-white/10 dark:bg-zinc-900/40 dark:shadow-2xl dark:shadow-purple-900/20  group-hover:-translate-y-1 hover:shadow-purple-500/10 hover:border-purple-500/30"
>
    <x-ui.avatar
        class="h-14 w-14 shrink-0 border-2 border-white shadow-sm dark:border-white/10"
        :force="true"
        :src="$user->avatar_url"
        :name="$user->name"
    />

    <div class="min-w-0 flex-1">
        <p class="truncate text-lg font-bold tracking-tight text-zinc-950 transition-colors group-hover:text-purple-600 dark:text-white dark:group-hover:text-purple-400">
            {{ $user?->name }}
        </p>
    </div>
</div>
