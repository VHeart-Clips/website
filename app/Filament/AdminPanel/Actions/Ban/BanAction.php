<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Actions\Ban;

use App\Actions\Ban\BanAction as BanModelAction;
use App\Enums\Filament\LucideIcon;
use App\Filament\AdminPanel\Resources\Bans\BanResource;
use App\Models\Traits\Bannable;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use RuntimeException;

class BanAction extends Action
{
    protected ?string $banRelationship = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->authorize(fn (?Model $record) => auth()->user()->can('ban', $this->getBannable($record)))
            ->modalWidth(Width::FiveExtraLarge)
            ->icon(LucideIcon::Ban)
            ->color('danger')
            ->label('Ban')
            ->schema([
                DateTimePicker::make('until')
                    ->hint('Permanent if empty')
                    ->columnSpanFull(),
                MarkdownEditor::make('reason')
                    ->required()
                    ->minLength(10)
                    ->maxLength(1000 * 50)
                    ->columnSpanFull(),
            ])
            ->action(function (Model $record, array $data, BanModelAction $banModelAction): void {
                $bannable = $this->getBannable($record);

                if (! $bannable) {
                    Notification::make()
                        ->title('Could not Ban '.Str::title($this->banRelationship ?? 'User'))
                        ->body('The '.Str::title($this->banRelationship ?? 'User').' could not be resolved.')
                        ->danger()
                        ->send();

                    $this->halt();
                }

                try {
                    $ban = $banModelAction->execute(
                        bannable: $bannable,
                        bannedByUserId: auth()->id(),
                        reason: $data['reason'],
                        until: $data['until'],
                    );

                    Notification::make('banned-'.$bannable->getKey())
                        ->title('Successfully Banned')
                        ->success()
                        ->actions([
                            Action::make('view')
                                ->label('View Ban')
                                ->url(BanResource::getUrl('view', ['record' => $ban]))
                                ->button(),
                        ])
                        ->send();
                } catch (Exception $e) {
                    report($e);

                    Notification::make('error-banning-'.$bannable->getKey())
                        ->title('Could not Ban Entity')
                        ->body('Error while banning entity, please try again later.')
                        ->danger()
                        ->send();

                    $this->halt();
                }
            });
    }

    public static function make(?string $name = null): static
    {

        $actionName = $name ? "ban_$name" : static::getDefaultName();
        $instance = parent::make($actionName);
        $instance->banRelationship = $name;

        return $instance;
    }

    public static function getDefaultName(): ?string
    {
        return 'ban';
    }

    private function getBannable(?Model $record): ?Model
    {
        $related = $this->banRelationship && $record?->relationLoaded($this->banRelationship)
            ? $record->{$this->banRelationship}
            : null;

        $target = $related instanceof Model ? $related : $record;

        if ($target && ! in_array(Bannable::class, class_uses_recursive($target))) {
            if (app()->isLocal()) {
                $class = $target::class;
                $recordClass = $record::class;
                $name = $this->getName();

                throw new RuntimeException(
                    "Model '$class' does not use the Bannable trait but was used within the '$name' filament action for the '$recordClass' record."
                );
            }

            return null;
        }

        return $target;
    }
}
