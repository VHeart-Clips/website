import { AlpineComponent } from 'alpinejs';

export type ReportableType = 'user' | 'clip';
export type ReportableId = number | string;

export interface ReportableItem {
    id: ReportableId;
    type: ReportableType;
    label?: string;
}

export interface ReportButtonConfig {
    items: ReportableItem[];
    label: string;
    disabled?: boolean;
}

export interface ReportButtonData {
    items: ReportableItem[];
    label: string;
    disabled: boolean;
    open: boolean;
    getLabel(item: ReportableItem): string;
    report(item: ReportableItem): void;
}

export default ({
    items,
    label,
    disabled,
}: ReportButtonConfig): AlpineComponent<ReportButtonData> => ({
    items,
    label,
    disabled: disabled || false,
    open: false,

    getLabel(item) {
        return this.label.replace(
            '__REPORTABLE_LABEL__',
            item.label || item.type,
        );
    },

    report(item) {
        this.open = false;
        this.$dispatch('report-modal', item);
    },
});
