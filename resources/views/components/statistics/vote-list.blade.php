@props(['allowedVoteToChange' => null, 'votes' => []])
@foreach($votes as $vote)
    @php
        $allowVoteTochange = $allowedVoteToChange && $vote->id === $allowedVoteToChange->id;
    @endphp

    @if ($allowVoteTochange)
        <form action="{{ route('user.statistics.votes') }}" method="POST">
            <input type="hidden" value="{{ $vote->id }}" name="voteId">
            <input type="hidden" value="{{ $vote->voted ? '0' : '1' }}" name="voted">
    @endif

    <x-ui.card variant="glassNoShadow" >
        <x-ui.card.content class="overflow-hidden">
            <x-clips.preview :clip="$vote->clip" class="aspect-video"></x-clips.preview>
        </x-ui.card.content>
        <x-ui.card.footer class="flex-col gap-4 p-1 md:p-1 xl:p-1">
            <section
                    class="sticky gap-1 bottom-18 w-full max-w-3xl mx-auto px-3 flex flex-row justify-center items-center rounded-2xl"
                >

                <div class="flex shrink-0 items-center justify-center gap-3 py-2 px-3 sm:gap-4 sm:py-3 border-muted rounded-full bg-white/75 dark:bg-black/80 ring-black/5 ring-1 dark:ring-0">
                    <div class="flex items-center gap-3 sm:gap-4">
                        <div
                            class="relative flex items-center gap-3 sm:gap-4"
                        >
                            <x-ui.button
                                :data-armed="$vote->voted ? 'true' : 'false'"
                                variant="icon"
                                type="submitt"
                                :disabled="!$allowVoteTochange || $vote->voted"
                                :title="__('clips.vote.form.fields.vote.label')"
                                class="inline size-9 place-items-center rounded-full bg-accent/25 dark:bg-black ring-1 ring-white/10 sm:size-11 transition-all duration-150 ease-out active:scale-95 sm:hover:scale-110 sm:hover:text-destructive group relative before:absolute before:-inset-2 before:content-[''] before:rounded-full data-[armed=true]:scale-110 data-[armed=true]:ring-2 data-[armed=true]:ring-destructive data-[armed=true]:bg-destructive/10"
                            >
                                <x-lucide-heart defer
                                                class="size-4 sm:size-5 text-accent-foreground group-hover:text-destructive transition-colors group-data-[armed=true]:text-destructive group-data-[armed=true]:scale-110 group-data-[armed=true]:fill-current"/>
                                <span class="sr-only">{{ __('clips.vote.form.fields.vote.label') }}</span>
                            </x-ui.button>

                            <x-ui.button
                                :data-armed="$vote->voted ? 'true' : 'false'"
                                variant="icon"
                                type="submitt"
                                :disabled="!$allowVoteTochange || !$vote->voted"
                                :title="__('clips.vote.form.fields.skip.label')"
                                class="inline size-9 place-items-center rounded-full bg-accent/25 dark:bg-black ring-1 ring-white/10 sm:size-11 transition-all duration-150 ease-out active:scale-95 sm:hover:scale-110 group relative before:absolute before:-inset-2 before:content-[''] before:rounded-full data-[armed=false]:scale-110 data-[armed=false]:ring-2 data-[armed=false]:ring-muted-foreground data-[armed=false]:bg-muted/30"
                            >
                                <x-lucide-circle-x defer
                                                class="size-4 sm:size-5 text-accent-foreground group-hover:text-muted-foreground transition-colors group-data-[armed=false]:text-muted-foreground group-data-[armed=false]:scale-110"/>
                                <span class="sr-only">{{ __('clips.vote.form.fields.skip.label') }}</span>
                            </x-ui.button>
                        </div>
                    </div>
                </div>


                <div class="flex justify-end gap-4 items-center w-100" >
                    <x-ui.report.button
                        :items="[[ 'type' => 'clip','id' => '$vote->clip->id' ]]"
                    />
                    <div class="flex justify-end gap-2 items-center">
                        <x-ui.tooltip>
                            <x-ui.tooltip.trigger>
                                {{ $vote->created_at->diffForHumans() }}
                            </x-ui.tooltip.trigger>
                            <x-ui.tooltip.content side="bottom">
                                {{ $vote->created_at->format('d.m.Y H:i:s') }}
                            </x-ui.tooltip.content>
                        </x-ui.tooltip>
                        <x-lucide-clock class="size-5" defer/>
                    </div>
                </div>

            </section>

            @if ($allowVoteTochange)
                <x-ui.input.error>
                    @error('voteId') {{ $message }} @enderror
                </x-ui.input.error>
                <x-ui.input.error>
                    {{ session('error') }}
                </x-ui.input.error>
            @endif

        </x-ui.card.footer>
    </x-ui.card>

    @if ($allowVoteTochange)
        </form>
    @endif
@endforeach
