import { AlpineComponent } from 'alpinejs';
import axios from 'axios';
import ReportController from '@/actions/App/Http/Controllers/ReportController';
import { ReportableItem } from '@/static/components/ui/report/button';

export interface ReportModalData {
    selectedItem: ReportableItem | null;
    isLoading: boolean;
    modalOpen: boolean;
    wasSuccessful: boolean;
    labelKey: string;
    currentLabel: string;
    form: {
        errors: Record<string, string[]>;
    };
    getLabel(item: ReportableItem): string;
    openModal(item: ReportableItem): void;
    closeModal(): void;
    submitReport(e: SubmitEvent): Promise<void>;
    handleCancel(e: Event): void;
}

export default (labelKey: string): AlpineComponent<ReportModalData> => ({
    selectedItem: null,
    isLoading: false,
    modalOpen: false,
    wasSuccessful: false,
    labelKey,
    currentLabel: '',
    form: { errors: {} },

    getLabel(item): string {
        return this.labelKey.replace(
            '__REPORTABLE_LABEL__',
            item.label || item.type,
        );
    },

    openModal(item: ReportableItem) {
        this.currentLabel = this.getLabel(item);
        this.selectedItem = item;
        (this.$refs.dialog as HTMLDialogElement).showModal();
        this.modalOpen = true;
    },

    closeModal() {
        this.modalOpen = false;
        setTimeout(() => {
            (this.$refs.dialog as HTMLDialogElement).close();
            this.selectedItem = null;
            this.wasSuccessful = false;
            this.form.errors = {};
        }, 300);
    },

    handleCancel(e: Event) {
        e.preventDefault();
        this.closeModal();
    },

    async submitReport(e: SubmitEvent) {
        this.isLoading = true;
        this.form.errors = {};

        const target = e.target as HTMLFormElement;
        const formData = new FormData(target);

        if (this.selectedItem) {
            formData.append('reportable_id', String(this.selectedItem.id));
            formData.append('reportable_type', this.selectedItem.type);
        }

        try {
            await axios.post(ReportController.store().url, formData, {
                headers: {
                    Accept: 'application/json',
                },
            });

            this.wasSuccessful = true;
        } catch (error) {
            if (axios.isAxiosError(error) && error.response?.status === 422) {
                this.form.errors = error.response.data.errors || {};
            } else {
                console.error('Submission failed:', error);
            }
        } finally {
            this.isLoading = false;
        }
    },
});
