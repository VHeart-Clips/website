<div
    x-load
    x-data="appearanceSlider"
    class="relative inline-flex items-center rounded-lg w-23 sm:w-26 bg-neutral-100 p-1 dark:bg-neutral-800"
    role="group"
    aria-label="Select appearance mode"
>
    <div
        x-show="appearance"
        style="display: none;"
        class="pointer-events-none absolute top-1 left-1 h-[calc(100%-0.5rem)] w-[calc((100%-0.5rem)/3)] rounded-md bg-white shadow-sm ring-1 ring-neutral-200 transition-transform duration-200 ease-out dark:bg-neutral-700 dark:shadow-none dark:ring-neutral-600"
        :style="{ transform: `translateX(calc(${activeIndex} * 100%))` }"
        aria-hidden="true"
    ></div>

    <x-ui.appearance.slider.button state="light" icon="lucide-sun" />
    <x-ui.appearance.slider.button state="dark" icon="lucide-moon" />
    <x-ui.appearance.slider.button state="system" icon="lucide-monitor" />
</div>
