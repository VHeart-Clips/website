import { useEffect, useMemo, useRef, useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ChevronDown, Tag as TagIcon, X } from 'lucide-react';

type TagOption = {
    id: number;
    name: string;
};

type TagSelectProps = {
    tags: TagOption[];
    label: string;
    name?: string;
    maxSelections?: number;
    placeholder: string;
    filterPlaceholder: string;
    noResultsText: string;
    selectedCountText: (count: number, max: number) => string;
    maxErrorMessage: (max: number) => string;
    removeLabel: (tagName: string) => string;
    selectedIds: number[];
    onChange: (selectedIds: number[]) => void;
    errorMessage?: string | null;
};

export function TagSelect({
    tags,
    label,
    name = 'tags[]',
    maxSelections = 3,
    placeholder,
    filterPlaceholder,
    noResultsText,
    selectedCountText,
    maxErrorMessage,
    removeLabel,
    selectedIds,
    onChange,
    errorMessage,
}: TagSelectProps) {
    const dropdownRef = useRef<HTMLDivElement | null>(null);
    const [isOpen, setIsOpen] = useState(false);
    const [query, setQuery] = useState('');
    const [localError, setLocalError] = useState<string | null>(null);

    const selectedTags = useMemo(() => {
        return tags.filter((tag) => selectedIds.includes(tag.id));
    }, [selectedIds, tags]);

    const filteredTags = useMemo(() => {
        const lowered = query.trim().toLowerCase();
        return tags.filter((tag) => {
            if (selectedIds.includes(tag.id)) return false;
            if (!lowered) return true;
            return tag.name.toLowerCase().includes(lowered);
        });
    }, [query, selectedIds, tags]);

    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (!dropdownRef.current) return;
            if (!dropdownRef.current.contains(event.target as Node)) {
                setIsOpen(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, []);

    const handleSelect = (tagId: number) => {
        setLocalError(null);

        if (selectedIds.includes(tagId)) return;
        if (selectedIds.length >= maxSelections) {
            setLocalError(maxErrorMessage(maxSelections));
            return;
        }

        const nextSelected = [...selectedIds, tagId];
        onChange(nextSelected);
        setQuery('');
        if (nextSelected.length >= maxSelections) {
            setIsOpen(false);
        }
    };

    const handleRemove = (tagId: number) => {
        setLocalError(null);
        onChange(selectedIds.filter((id) => id !== tagId));
    };

    return (
        <div className="space-y-4" ref={dropdownRef}>
            <Label className="flex items-center gap-2">
                <TagIcon className="h-4 w-4" />
                {label} *
            </Label>

            <div className="space-y-2">
                <button
                    type="button"
                    className="border-input bg-background focus-visible:ring-ring/50 flex h-9 w-full items-center justify-between rounded-md border px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                    onClick={() => setIsOpen((prev) => !prev)}
                >
                    <span className="text-muted-foreground">
                        {selectedIds.length > 0
                            ? selectedCountText(
                                  selectedIds.length,
                                  maxSelections,
                              )
                            : placeholder}
                    </span>
                    <ChevronDown className="size-4 opacity-50" />
                </button>

                {selectedTags.length > 0 && (
                    <div className="flex flex-wrap gap-2">
                        {selectedTags.map((tag) => (
                            <Badge
                                key={`selected-tag-${tag.id}`}
                                variant="secondary"
                                className="gap-1 px-3 py-2"
                            >
                                {tag.name}
                                <button
                                    type="button"
                                    className="text-muted-foreground hover:text-foreground hover:cursor-pointer"
                                    onClick={() => handleRemove(tag.id)}
                                        aria-label={removeLabel(tag.name)}
                                >
                                    <X className="size-4" />
                                </button>
                            </Badge>
                        ))}
                    </div>
                )}

                {isOpen && (
                    <div className="bg-popover text-popover-foreground border-input space-y-2 rounded-md border p-2 shadow-md">
                        <Input
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder={filterPlaceholder}
                            autoComplete="off"
                            className="h-8"
                        />
                        <div className="max-h-56 overflow-y-auto">
                            {filteredTags.length > 0 ? (
                                <div className="space-y-1">
                                    {filteredTags.map((tag) => (
                                        <button
                                            key={`tag-option-${tag.id}`}
                                            type="button"
                                            className="hover:bg-accent hover:text-accent-foreground flex w-full items-center rounded-sm px-2 py-1.5 text-left text-sm"
                                            onClick={() => handleSelect(tag.id)}
                                            disabled={
                                                selectedIds.length >=
                                                maxSelections
                                            }
                                        >
                                            {tag.name}
                                        </button>
                                    ))}
                                </div>
                            ) : (
                                <div className="text-muted-foreground px-2 py-1 text-sm">
                                    {noResultsText}
                                </div>
                            )}
                        </div>
                    </div>
                )}

                {selectedIds.map((id) => (
                    <input key={`tag-hidden-${id}`} type="hidden" name={name} value={id} />
                ))}

                {(errorMessage || localError) && (
                    <p className="text-destructive text-sm">
                        {errorMessage || localError}
                    </p>
                )}
            </div>
        </div>
    );
}
