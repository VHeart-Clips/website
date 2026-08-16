@props(['submittedClips' => []])
@foreach($submittedClips as $submittedClip)
    <x-ui.card variant="glassNoShadow" >
        <x-ui.card.content class="overflow-hidden">
            <x-clips.preview :clip="$submittedClip" class="aspect-video"></x-clips.preview>
        </x-ui.card.content>
        <x-ui.card.footer class="flex-col gap-4 py-1 md:py-1 xl:py-1">
            <section class="sticky gap-1 bottom-18 w-full max-w-3xl mx-auto flex flex-row justify-end items-center rounded-2xl" >
                <div class="flex justify-end gap-4 items-center w-100 mr-0.5" >
                    <x-ui.tooltip>
                        <x-ui.tooltip.trigger>
                            {{ $submittedClip->created_at->diffForHumans() }}
                        </x-ui.tooltip.trigger>
                        <x-ui.tooltip.content side="bottom">
                            {{ $submittedClip->created_at->format('d.m.Y H:i:s') }}
                        </x-ui.tooltip.content>
                    </x-ui.tooltip>
                    <x-lucide-clock class="size-5" defer/>
                </div>
            </section>
        </x-ui.card.footer>
    </x-ui.card>
@endforeach
