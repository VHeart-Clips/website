@use(App\Enums\Reports\ReportReason)
<x-ui.modal
    id="report-modal"
    component="reportModal('{{ __('reports.modal.title', ['reportable' => '__REPORTABLE_LABEL__']) }}')"
>
    <template x-if="wasSuccessful">
        <div class="flex flex-col items-center justify-center space-y-4 py-6 text-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                <x-lucide-check class="size-6 text-green-600 dark:text-green-500" defer />
            </div>

            <div class="space-y-1">
                <h3 class="text-lg font-semibold">{{ __('reports.modal.success.title') }}</h3>
                <p class="text-sm text-muted-foreground">{{ __('reports.modal.success.message') }}</p>
            </div>

            <button @click="closeModal()" class="w-full sm:w-auto inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors hover:bg-primary/90 bg-primary text-primary-foreground h-10 px-4 py-2">
                {{ __('reports.modal.success.ok') }}
            </button>
        </div>
    </template>

    <template x-if="!wasSuccessful">
        <div class="relative">
            <div x-show="isLoading" style="display: none;" class="absolute inset-0 z-10 flex items-center justify-center bg-background/50 backdrop-blur-[1px]">
                <x-lucide-loader-circle class="size-8 animate-spin text-primary" defer />
            </div>

            <div class="flex flex-col space-y-1.5 text-center sm:text-left mb-4">
                <h2 class="text-lg font-semibold leading-none tracking-tight capitalize" x-text="currentLabel"></h2>
                <p class="text-sm text-muted-foreground">
                    {{ __('reports.modal.subtitle') }}
                </p>
            </div>

            <form @submit.prevent="submitReport" class="relative space-y-4">
                <div>
                    <label class="text-sm leading-none font-medium">{{ __('reports.modal.inputs.reason.label') }}</label>
                    <select
                        name="reason"
                        required
                        class="mt-2 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none dark:border-gray-700 dark:bg-gray-800"
                    >
                        @foreach (ReportReason::cases() as $reason)
                            <option value="{{ $reason->value }}">{{ $reason->getLabel() }}</option>
                        @endforeach
                    </select>
                    <template x-if="form.errors.reason">
                        <p class="text-xs font-medium text-destructive mt-1" x-text="form.errors.reason[0]"></p>
                    </template>
                </div>

                <div>
                    <label class="text-sm leading-none font-medium">{{ __('reports.modal.inputs.description.label') }}</label>
                    <textarea
                        name="description"
                        rows="4"
                        placeholder="{{ __('reports.modal.inputs.description.placeholder') }}"
                        class="mt-2 flex min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none dark:border-gray-700 dark:bg-gray-800"
                    ></textarea>
                    <template x-if="form.errors.description">
                        <p class="text-xs font-medium text-destructive mt-1" x-text="form.errors.description[0]"></p>
                    </template>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button
                        type="button"
                        @click="closeModal()"
                        :disabled="isLoading"
                        class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 disabled:opacity-50"
                    >
                        {{ __('reports.modal.inputs.cancel') }}
                    </button>
                    <button
                        type="submit"
                        :disabled="isLoading"
                        class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors bg-destructive text-destructive-foreground hover:bg-destructive/90 h-10 px-4 py-2 disabled:opacity-50"
                    >
                        {{ __('reports.modal.inputs.submit') }}
                    </button>
                </div>
            </form>
        </div>
    </template>
</x-ui.modal>
