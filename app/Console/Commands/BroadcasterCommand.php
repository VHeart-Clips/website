<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Broadcaster\BroadcasterConsent;
use App\Models\Broadcaster\Broadcaster;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\search;

#[Signature('app:broadcaster')]
#[Description('Create or Update a broadcaster profile for a user')]
class BroadcasterCommand extends Command
{
    public function handle(): int
    {
        $id = search(
            label: 'User',
            options: fn (string $value) => $value !== ''
                ? User::whereLike('name', "%{$value}%")
                    ->orWhere('id', (int) $value)
                    ->limit(50)
                    ->pluck('name', 'id')
                    ->all()
                : []
        );

        $broadcaster = Broadcaster::createOrFirst(['id' => $id]);

        if ($broadcaster->wasRecentlyCreated) {
            $this->info('Created Broadcaster profile.');
        }

        // Consent
        $prefix = 'consent_';

        $currentConsent = $broadcaster->consent
            ?->map(fn (BroadcasterConsent $c): string => $prefix.$c->value)
            ->all() ?? [];

        $consent = multiselect(
            label: 'Consent',
            options: collect(BroadcasterConsent::cases())
                ->mapWithKeys(fn (BroadcasterConsent $c): array => [$prefix.$c->value => $c->name])
                ->all(),
            default: $currentConsent
        );

        $broadcaster->consent = collect($consent)
            ->map(fn (string $value) => BroadcasterConsent::from((int) str_replace($prefix, '', $value)))
            ->all();

        // Submit Permissions
        $booleanFields = [
            'submit_user_allowed' => 'Everyone',
            'submit_vip_allowed' => 'VIPs',
            'submit_mods_allowed' => 'Mods',
        ];
        $currentSelections = collect($booleanFields)
            ->filter(fn (string $label, string $field): bool => $broadcaster->{$field} === true)
            ->keys()
            ->all();

        $selectedFields = multiselect(
            label: 'Submit Permissions',
            options: $booleanFields,
            default: $currentSelections,
            hint: 'Unselected will be disabled, Everyone will override other options.'
        );

        foreach (array_keys($booleanFields) as $field) {
            $broadcaster->{$field} = in_array($field, $selectedFields, true);
        }

        $broadcaster->save();

        return CommandAlias::SUCCESS;
    }
}
