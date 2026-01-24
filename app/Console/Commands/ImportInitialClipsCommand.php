<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\ImportClipAction;
use App\Models\User;
use App\Services\Twitch\TwitchEndpoints;
use App\Services\Twitch\TwitchService;
use Illuminate\Console\Command;

class ImportInitialClipsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-initial-clips';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Initinal Clips';

    private $clips = [
        'https://www.twitch.tv/yhubei/clip/BigImpartialWalletAMPEnergy-8RYRjexnyvbeNfrV',
        'https://www.twitch.tv/ixigami/clip/EnticingLongNuggetsCurseLit-1LUi7d9JzNhMZ2Rx',
        'https://www.twitch.tv/icyhime/clip/GenerousBoxySwanFloof-HM_jprvcQDR7WnwR',
        'https://www.twitch.tv/mayura_ch/clip/PlausibleSavorySwordCurseLit-y9LZMS1ERrSSq4N4',
        'https://www.twitch.tv/chiino/clip/HelplessEagerTardigradeSaltBae-LKKuYwxmKhbOgD1G',
        'https://www.twitch.tv/dschiina/clip/InexpensiveResilientAppleOSkomodo-sbNBVHuO8fDaMmtn',
        'https://www.twitch.tv/magvariety/clip/LachrymoseTardyPuddingDoritosChip-FrBrXz-wnWcIXgbD',
        'https://www.twitch.tv/poxari/clip/FrozenSeductiveSoybeanTBTacoLeft-68OrgaC-HEgyXI2_',
        'https://www.twitch.tv/pixpia/clip/EndearingCrazyPepperoniEagleEye-DKddR1AmIcTylJAe',
        'https://clips.twitch.tv/SoftTemperedDeerSmoocherZ-Pg5m0Sz5AdCAUJWO',
        'https://www.twitch.tv/ikiochi/clip/CourteousVastDragonfruitDatSheffy-aMnD1QIF9SoSiQwQ',
        'https://www.twitch.tv/diekorifee/clip/BlushingFrailSheepPunchTrees-F1LdWe_JXV8Cj9PE',
        'https://www.twitch.tv/sushiima/clip/BovineWittyPepperoniSuperVinlin-S1ETPMeUJrPoRoYV',
        'https://www.twitch.tv/nanaaachan/clip/CalmKathishParrotTF2John-4ZpmI1AfPQoEsd_R',
        'https://www.twitch.tv/ryuko_vt/clip/AntediluvianUnsightlyKimchiKappaWealth-uRvVzUsNDG8MYR0K',
        'https://www.twitch.tv/ikira_vt/clip/CrepuscularHelpfulWaspVoHiYo-x1qSTRXwuhnCsoV4',
        'https://www.twitch.tv/justplayerde/clip/GentleNimblePanFreakinStinkin-1eErSWrf0oTe4DNb',
        'https://www.twitch.tv/nanakiiq/clip/CooperativeAmericanPheasantMingLee-5FXZZ91UFu9b-8T3',
        'https://www.twitch.tv/draconiustiamat/clip/HeadstrongOnerousParrotDogFace-Pzk4pAjq0Ws-AuY5',
        'https://www.twitch.tv/rompedereisbaer/clip/IgnorantMuddyLocustAMPEnergy-VUBWoHLFCEteN3C_',
        'https://www.twitch.tv/drakolyr/clip/DeafIcyAmazonJonCarnage-HNKpsG0CfnVHbo83',
        'https://www.twitch.tv/tatsukine/clip/AbnegateSlipperyBulgogiUnSane-uWS1v7mESK2e_6MQ',
        'https://www.twitch.tv/xcharmingmonsterx/clip/SuccessfulResourcefulPlumPartyTime-iPGxDnmhkiYcc1p7',
        'https://www.twitch.tv/shiinozu/clip/CharmingSquareLyrebirdCoolStoryBob-Di5d7-2Ju-O_0VWW',
        'https://www.twitch.tv/nebulosvt/clip/ElatedCuriousPepperoniFloof-emscHxhWG5Pav1gB',
        'https://www.twitch.tv/sejumarnie/clip/AverageAgilePancakeKevinTurtle-MI4eLE8P2oPOsoKs',
        'https://www.twitch.tv/maaychen/clip/CleverKnottyUdonPRChase-N_zw9BP7soQFegFG?filter=clips&range=all&sort=time',
        'https://www.twitch.tv/xblackievt/clip/ShinyColdbloodedMageCeilingCat-M9KaMexmbCUdXvW4',
        'https://www.twitch.tv/ignitris/clip/FamousAltruisticCucumberSaltBae-jMssVVE4G8F1DOWz',
        'https://www.twitch.tv/aregion/clip/EasyIronicSharkSaltBae-YoQgrQCxYcGoKwFd',
        'https://www.twitch.tv/hebi_zockt/clip/TiredPreciousMartenKappaWealth-zFzxz3qlrQYGOWS3',
        'https://www.twitch.tv/spiritofthewolfs/clip/OddBlindingPuffinAliens-oQ97t1DQIK7DtnfX',
        'https://www.twitch.tv/sozalainsock/clip/ExcitedAwkwardMarjoramCmonBruh-e6fNA_ZXMkyW12kH',
        'https://www.twitch.tv/xcharmingmonsterx/clip/GlamorousImpartialClipzTTours-ZU8SbZn5ZEGHbRV4',
        'https://www.twitch.tv/mimimaid/clip/SpotlessFaintClipsmomPartyTime-iVeYirUOukt2MMqK',
        'https://www.twitch.tv/sakuyue/clip/InspiringAnnoyingPigOMGScoots-MkuSyJ9cYFx1kCtA',
        'https://www.twitch.tv/heijmdall/clip/ExpensiveCrunchyElephantKappaClaus-aN4xFHHxAPyUOnM9',
        'https://www.twitch.tv/zhenganu/clip/ArbitraryMuddyTomatoVoteYea-URKp4wkFAxFXh6jq',
        'https://www.twitch.tv/krystix_vt/clip/CreativeApatheticAlpacaM4xHeh-vtAnNPvrgXYZPJTS',
        'https://www.twitch.tv/miinyu/clip/DeadKindDadUnSane-bRoTdW1iy5ZwuWsN',
        'https://www.twitch.tv/toruvt/clip/TangibleTalentedPonyRalpherZ-TENdUqv0CL1FhKPq',
        'https://www.twitch.tv/roxysparrow/clip/PunchyWanderingDootImGlitch-pRyUHeGFUXT9vr3U?filter=clips&range=all&sort=time',
        'https://www.twitch.tv/spiritssoul08/clip/DeterminedWittyEggplantKappaClaus-i6lhKvoXjXD5wVMh',
        'https://www.twitch.tv/guidingkeen/clip/LivelyDiligentGorillaJKanStyle-9sW9dY5oPvOYuMNO?filter=clips&range=all&sort=time',
        'https://www.twitch.tv/shaunisan/clip/HumbleWonderfulFerretM4xHeh-gfSzPjyNlXRKM-0G',
        'https://www.twitch.tv/nyanartx3/clip/RefinedJazzyStarlingJonCarnage-VJVqWbUGIAeu_heU',
        'https://www.twitch.tv/yelenta/clip/SavoryTangibleNigiriVoHiYo-3y-dv8tbBQgLWYvZ',
        'https://www.twitch.tv/merox_7291/clip/JollyUnusualKuduBudBlast-rCtPoY1KJJsVd-2_',
        'https://www.twitch.tv/randomtime_ch/clip/HealthyConcernedSwallowUnSane-EeJIsA8ePcjGi62v?filter=clips&range=all&sort=time',
        'https://www.twitch.tv/akiva_kun/clip/TalentedShyGarageMVGame-keqU8I7XJ5FI1G34',
        'https://www.twitch.tv/ferunaaa/clip/AmericanSecretiveChipmunkCoolCat-y5MA7WfJuezTJ6le ',
        'https://www.twitch.tv/liveonezerolp/clip/SlipperyCallousAsteriskHoneyBadger-Ih993Pbrme0Yxp1d',
        'https://www.twitch.tv/kaetzchenayumy/clip/BitterAlertGoatKappaClaus-Ijl-kmTL-oO1Gm4u',
        'https://www.twitch.tv/linichanviit/clip/LazyAliveCroissantAMPTropPunch-UosBLCxh8lpIyHwg',
        'https://www.twitch.tv/le0lion/clip/BlatantCleverLemurKappaRoss-YjqBo02ib6rIUgFj',
        'https://www.twitch.tv/yumixvt/clip/ClearDeadWatermelonHassaanChop-bWWVSUUvMXNAbTd3',
        'https://www.twitch.tv/leanorar/clip/LuckyPrettyWrenchTBTacoLeft-GzKmEqva9LLHVHVs',
        'https://www.twitch.tv/jasiyue/clip/AttractiveEvilWrenImGlitch-duvV8mJIFIq4C1QT',
        'https://www.twitch.tv/sanjaak/clip/GrotesqueSpicyFoxHassanChop-Np4vO_C33mjI7G5b',
        'https://www.twitch.tv/elliawald/clip/CulturedAverageWerewolfCharlieBitMe-5SdXIxXzpiI9Qt2y',
        'https://www.twitch.tv/dovakvt/clip/StupidHumbleBeeResidentSleeper-nP9QXjKx26XY9k_D',
        'https://www.twitch.tv/yume_shima/clip/DeterminedGleamingMooseHotPokket-jp6PZREvcUDYM4aK',
        'https://www.twitch.tv/syraphya/clip/CooperativeBeautifulCourgetteNomNom-Xmm8Lg18BzVewWdR',
        'https://www.twitch.tv/chirokoon/clip/DependablePreciousDootKappaClaus-BSAcRgIl9k_HYzAI',
        'https://www.twitch.tv/tshii/clip/CuriousObliviousKathyWutFace-XYHby8Zp2Zvcrc-G',
        'https://www.twitch.tv/feuerneko/clip/BelovedPowerfulHumanVoteYea-JA2ieqE2dTp7Bfdw',
        'https://www.twitch.tv/gatrexreal/clip/DifferentSpikyLlamaTheTarFu-UK8RRfYhb_A2XFpQ',
        'https://www.twitch.tv/wolfun_wolfskin/clip/FaintBoringButterSuperVinlin-PcB6n7v1YFpgvpSe',
        'https://www.twitch.tv/fenrir_tss/clip/AgileFurryPlumberBigBrother-NectoRiE70STavvR?filter=clips&range=all&sort=time',
        'https://www.twitch.tv/maephira/clip/FaithfulSpoopyTortoiseTakeNRG-tvo9yVbXy9PoshUq',
        'https://www.twitch.tv/arukori/clip/ScrumptiousDaintyCocoaBrokeBack-nZJf9zZ-OAIc6LS6',
        'https://www.twitch.tv/charonya_/clip/PrettyAmazingMochaRalpherZ-3jvM5cgcwxiec6hs',
        'https://clips.twitch.tv/ClumsyGloriousNightingaleTooSpicy-0U-EAoDIXzzKB9rS?tt_content=url&tt_medium=clips_api',
        'https://www.twitch.tv/amynook/clip/CleverFaithfulWatercressTF2John-cu8rRTahL8ATnnMA',
        'https://www.twitch.tv/diekorifee/clip/TenuousSparklingWeaselBCWarrior-Pu9ggzbJZxDEd8wd',
        'https://www.twitch.tv/sillyemy/clip/SpookyProductivePhoneSwiftRage-3J7WJVEn-vypPHdx',
        'https://www.twitch.tv/maaychen/clip/CleverKnottyUdonPRChase-N_zw9BP7soQFegFG?filter=clips&range=all&sort=time',
        'https://www.twitch.tv/space_furry/clip/CreativeTalentedAniseTBTacoRight--zmezpz1T3vNI1Z8?filter=clips&range=all&sort=time',
        'https://www.twitch.tv/confusedket/clip/AgitatedWrongMartenAMPEnergyCherry-QtACzJvoGLjV7baP',
        'https://www.twitch.tv/demonlordtion/clip/AmericanRudeTeaTinyFace-hTVT-gthooViMrYF?filter=clips&range=all&sort=time',
        'https://www.twitch.tv/shirakiin3/clip/HardDreamyMilkRuleFive-1HqkB9C9ccszgUMC?filter=clips&range=all&sort=time',
        'https://www.twitch.tv/coldbunnyvt/clip/TalentedSpicyAlbatrossNinjaGrumpy-DLzVtz7H4r9R1qMJ',
        'https://www.twitch.tv/vtakeru/clip/BoxyFineBorkMrDestructoid-Nz0rwTv3eXGtUM0n',
        'https://www.twitch.tv/ashariia/clip/VastMushyElkKappaWealth-UvFk5M20T1-uNIee?filter=clips&range=all&sort=time',
        'https://www.twitch.tv/hanae/clip/WanderingImportantBasenjiBigBrother-ba-JFKyDNodYdOWH',
        'https://www.twitch.tv/minaly/clip/HorribleAdventurousRavenPeteZaroll-sbI3t7oKgy_beI_h',
        'https://www.twitch.tv/corvisatreis/clip/ImportantSpotlessStarlingFreakinStinkin-IbKyBsNxcjRI0DCC',
        'https://www.twitch.tv/ollertroll/clip/SpotlessAcceptableLionDancingBanana-2IvjUPPoasjB3Psa?filter=clips&range=all&sort=time',
        'https://www.twitch.tv/checkcards/clip/PatientDifficultSpiderDerp-3XSJ5Sy9MSFPvcac',
        'https://www.twitch.tv/zarisia/clip/SmellyGorgeousIguanaThunBeast-QCwJQNN9fAO9o9RM',
    ];

    /**
     * Execute the console command.
     */
    public function handle(TwitchService $twitchService, ImportClipAction $importClipAction)
    {
        $params = ['id' => []];

        foreach ($this->clips as $clip) {
            $params['id'][] = $twitchService->parseClipId($clip);
        }

        $clips = $twitchService->get(TwitchEndpoints::GetClips, $params);

        foreach ($clips as $clip) {
            $importClipAction->execute($clip, User::first() ?? null);
        }
    }
}
