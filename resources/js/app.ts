import './sentry';
import './bootstrap';
import './alpine';
import Alpine from 'alpinejs';

// @ts-expect-error If we are within filament we dont need to start alpine again
if (!window.Livewire) {
    window.Alpine = Alpine;
    Alpine.start();
}
