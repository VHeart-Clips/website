<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use JsonException;

class TeamController extends Controller
{
    /**
     * @throws JsonException
     */
    public function __invoke(Request $request): View
    {
        $roles = Role::query()
            ->where('public', true)
            ->orderBy('weight', 'desc')
            ->orderBy('id')
            ->with([
                // i think this is the fairest way to sort it
                'users' => fn (Relation $builder): Relation => $builder->orderByRaw('LOWER(name)')]
            )
            ->get();

        return view('team.index', [
            'teamSchema' => json_encode($this->getTeamSchema($roles), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'roles' => $roles,
        ]);
    }

    /**
     * Generates a schema.org thing for the entire team, can be quite large though.
     * We will force it to only return german strings to prevent confusion for SEO related stuff
     * (we would need localized urls for it to work properly)
     *
     * @param  Collection<Role>  $roles
     */
    public function getTeamSchema(Collection $roles): array
    {
        $schemaItems = [];
        $rolePosition = 1;

        foreach ($roles as $role) {
            $members = [];
            $userPosition = 1;

            foreach ($role->users as $user) {
                $members[] = [
                    '@type' => 'ListItem',
                    'position' => $userPosition++,
                    'item' => [
                        '@type' => 'Person',
                        '@id' => 'https://vheart.net/team#'.$user->id,
                        'name' => e($user->name),
                        'jobTitle' => $role->getTranslation('name', 'de'),
                        'image' => $user->avatar_url ?? null,
                        'sameAs' => ['https://twitch.tv/'.e($user->name)],
                        'memberOf' => ['@id' => 'https://vheart.net/#organization'],
                    ],
                ];
            }

            if ($members === []) {
                continue;
            }

            $roleGroup = [
                '@type' => 'ItemList',
                'name' => e($role->getTranslation('name', 'de')),
                'itemListElement' => $members,
            ];

            if (! empty($role->desc) && $role->hasTranslation('desc', 'de')) {
                $roleGroup['description'] = e($role->getTranslation('desc', 'de'));
            }

            $schemaItems[] = [
                '@type' => 'ListItem',
                'position' => $rolePosition++,
                'item' => $roleGroup,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => 'VHeart Clips Teammitglieder',
            'itemListElement' => $schemaItems,
        ];
    }
}
