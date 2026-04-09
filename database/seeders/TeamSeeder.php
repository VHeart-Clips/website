<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Casts\TwitchAvatarCast;
use App\Models\User;
use App\Services\Twitch\Data\UserDto;
use App\Services\Twitch\TwitchService;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    protected array $superadmins = [
        24396904, // https://www.twitch.tv/speidy674
        51870314, // https://www.twitch.tv/JaxOffTV
        54011665, // https://www.twitch.tv/justplayerde
    ];

    protected array $admins = [
        98217515, // https://www.twitch.tv/dasonkeelchen
        430455268, // https://www.twitch.tv/silentpandavt
        814959034, // https://www.twitch.tv/yurayami
        1300415386, // https://www.twitch.tv/meynhero
    ];

    protected array $moderators = [
        253386707, // https://www.twitch.tv/xayriee
        256185531, // https://www.twitch.tv/kawaiidesunevt
        417367938, // https://www.twitch.tv/einfachtamtam
        442243239, // https://www.twitch.tv/sirchaos_1337
        457724393, // https://www.twitch.tv/kayaba_sama
        734502184, // https://www.twitch.tv/stresstiantogo
        1122025118, // https://www.twitch.tv/gianthwro20
        922504633, // https://www.twitch.tv/dragonsebiii
        101023128, // https://www.twitch.tv/wonsai_
        449139812, // https://www.twitch.tv/spiritssoul08
    ];

    protected array $communityManagers = [
        814959034, // https://www.twitch.tv/yurayami
    ];

    protected array $cutter = [
        87918397, // https://www.twitch.tv/thefluuf
        98217515, // https://www.twitch.tv/dasonkeelchen
        127357369, // https://www.twitch.tv/ventylos
        145960906, // https://www.twitch.tv/heijmdall
        146350225, // https://www.twitch.tv/onkel_noxy
        444845317, // https://www.twitch.tv/einmauvt
        460292967, // https://www.twitch.tv/doktor_seb0
        814959034, // https://www.twitch.tv/yurayami
        1072060211, // https://www.twitch.tv/offscreenkill
        1181232263, // https://www.twitch.tv/einfachoka
        1300415386, // https://www.twitch.tv/meynhero
        747332255, // https://www.twitch.tv/lokinson_vtube
        165813695, // https://www.twitch.tv/sandari_exe
        817951552, // https://www.twitch.tv/keylamgracelight
        769996920, // https://www.twitch.tv/lordwobbly
        11840166, // https://www.twitch.tv/raffophantom
        868365565, // https://www.twitch.tv/shortysan
        85293388, // https://www.twitch.tv/vaenlytas
        182832594, // https://www.twitch.tv/hikavt
        882479348, // https://www.twitch.tv/xkokitv
        87902738, // https://www.twitch.tv/maggititan
        446743173, // https://www.twitch.tv/twitchlezz
        688340656, // https://www.twitch.tv/svipure
        39355546, // https://www.twitch.tv/drwurstpeter
        410735255, // https://www.twitch.tv/momotschie
        243827916, // https://www.twitch.tv/solomon_h0d
        141788057, // https://www.twitch.tv/sgtdoubleu
        739357338, // https://www.twitch.tv/zelu_melu
        123880694, // https://www.twitch.tv/liveplayer_
        408596853, // https://www.twitch.tv/itsravora
    ];

    protected array $it = [
        24396904, // https://www.twitch.tv/speidy674
        44484822, // https://www.twitch.tv/pixeldesu
        51870314, // https://www.twitch.tv/JaxOffTV
        54011665, // https://www.twitch.tv/justplayerde
        139951429, // https://www.twitch.tv/sleepytawi
        76615709, // https://www.twitch.tv/shuffgy
    ];

    protected array $contributors = [
        756841354, // https://www.twitch.tv/dotgyy
        1353141462, // https://www.twitch.tv/nythnea
    ];

    public function run(TwitchService $twitchService): void
    {
        if (app()->environment('testing') || User::query()->whereNot('id', 0)->exists()) {
            return;
        }

        if (app()->isLocal()) {
            $this->command->warn('Granting SuperAdmin to entire IT Team because of local environment.');
            $this->superadmins = $this->it;
        }

        $roleMappings = [
            0 => $this->superadmins,
            1 => $this->admins,
            2 => $this->communityManagers,
            3 => $this->moderators,
            4 => $this->cutter,
            5 => $this->it,
            7 => $this->contributors,
        ];

        $allUserIds = collect($roleMappings)
            ->flatten()
            ->unique()
            ->values();

        $this->command->info("Fetching total {$allUserIds->count()} users...");

        $userDtos = $twitchService->asApp()->getUsers(['id' => $allUserIds->toArray()]);

        $usersData = collect($userDtos)->map(fn (UserDto $userDto): array => [
            'id' => $userDto->id,
            'name' => $userDto->displayName,
            'avatar_url' => TwitchAvatarCast::encode($userDto->profileImageUrl),
            'created_at' => $userDto->createdAt,
            'updated_at' => now(),
        ])->toArray();

        User::upsert($usersData, ['id'], ['name', 'avatar_url']);

        $users = User::whereIn('id', $allUserIds)->get();

        foreach ($users as $user) {
            $userRoles = collect($roleMappings)
                ->filter(fn ($userIds): bool => in_array($user->id, $userIds, true))
                ->keys()
                ->toArray();

            $user->syncRoles($userRoles);
        }
    }
}
