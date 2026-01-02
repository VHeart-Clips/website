import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useTranslation } from 'react-i18next';

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export default function TermsDialog({ open, onOpenChange }: Props) {
    const { t } = useTranslation('footer');

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{t('terms.title')}</DialogTitle>
                    <DialogDescription>{t('terms.short')}</DialogDescription>
                </DialogHeader>

                <div className="flex justify-end gap-2 pt-4">
                    <Button
                        variant="outline"
                        onClick={() => onOpenChange(false)}
                    >
                        {t('close')}
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
}
