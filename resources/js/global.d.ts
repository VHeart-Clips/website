import Alpine from 'alpinejs';
import { axios } from 'axios';

declare global {
    interface Window {
        Alpine: Alpine;
        axios: axios;
        FilamentNotification: typeof import('../../vendor/filament/notifications/dist/index').Notification;
    }
}
