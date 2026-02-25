import { AlpineComponent } from 'alpinejs';

export interface ModalData {
    modalOpen: boolean;
    openModal(): void;
    closeModal(): void;
    handleCancel(e: Event): void;
}

export default (): AlpineComponent<ModalData> => ({
    modalOpen: false,

    openModal() {
        (this.$refs.dialog as HTMLDialogElement).showModal();
        this.modalOpen = true;
    },

    closeModal() {
        this.modalOpen = false;
        setTimeout(() => {
            (this.$refs.dialog as HTMLDialogElement).close();
        }, 300);
    },

    handleCancel(e: Event) {
        e.preventDefault();
        this.closeModal();
    },
});
