<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\ImportClipAction;
use App\Enums\Clips\ClipStatus;
use App\Enums\Clips\CompilationClipStatus;
use App\Enums\Clips\CompilationStatus;
use App\Enums\Clips\CompilationType;
use App\Jobs\ImportCategoryJob;
use App\Models\Clip;
use App\Models\Clip\Compilation;
use App\Models\Scopes\ClipPermissionScope;
use App\Models\User;
use App\Services\Twitch\Data\ClipDto;
use App\Services\Twitch\TwitchEndpoints;
use App\Services\Twitch\TwitchService;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InitialEpisodeSeeder extends Seeder
{
    // Regex to replace full urls with Slugs: 'http.+/([A-Z][a-zA-Z0-9]*-[a-zA-Z0-9_-]+)',
    // Replace with: '$1',

    // Only raw clip ids pls
    protected const array Clips = [
        // Episode 1
        'BigImpartialWalletAMPEnergy-8RYRjexnyvbeNfrV',
        'EnticingLongNuggetsCurseLit-1LUi7d9JzNhMZ2Rx',
        'EndearingCrazyPepperoniEagleEye-DKddR1AmIcTylJAe',
        'AntediluvianUnsightlyKimchiKappaWealth-uRvVzUsNDG8MYR0K',
        'ElatedCuriousPepperoniFloof-emscHxhWG5Pav1gB',
        'OddBlindingPuffinAliens-oQ97t1DQIK7DtnfX',
        'ArbitraryMuddyTomatoVoteYea-URKp4wkFAxFXh6jq',
        'DeterminedWittyEggplantKappaClaus-i6lhKvoXjXD5wVMh',
        'JollyUnusualKuduBudBlast-rCtPoY1KJJsVd-2_',
        'BitterAlertGoatKappaClaus-Ijl-kmTL-oO1Gm4u',
        'LazyAliveCroissantAMPTropPunch-UosBLCxh8lpIyHwg',
        'BlatantCleverLemurKappaRoss-YjqBo02ib6rIUgFj',
        'DeterminedGleamingMooseHotPokket-jp6PZREvcUDYM4aK',
        'CooperativeBeautifulCourgetteNomNom-Xmm8Lg18BzVewWdR',
        'DependablePreciousDootKappaClaus-BSAcRgIl9k_HYzAI',
        'CuriousObliviousKathyWutFace-XYHby8Zp2Zvcrc-G',
        'AgileFurryPlumberBigBrother-NectoRiE70STavvR',
        'FaithfulSpoopyTortoiseTakeNRG-tvo9yVbXy9PoshUq',
        'ClumsyGloriousNightingaleTooSpicy-0U-EAoDIXzzKB9rS',
        'CleverFaithfulWatercressTF2John-cu8rRTahL8ATnnMA',
        'SpookyProductivePhoneSwiftRage-3J7WJVEn-vypPHdx',
        'CreativeTalentedAniseTBTacoRight--zmezpz1T3vNI1Z8',
        'AgitatedWrongMartenAMPEnergyCherry-QtACzJvoGLjV7baP',
        'HardDreamyMilkRuleFive-1HqkB9C9ccszgUMC',
        'SpotlessAcceptableLionDancingBanana-2IvjUPPoasjB3Psa',
        'HorribleAdventurousRavenPeteZaroll-sbI3t7oKgy_beI_h',
        'LachrymoseTardyPuddingDoritosChip-FrBrXz-wnWcIXgbD',
        'CourteousVastDragonfruitDatSheffy-aMnD1QIF9SoSiQwQ',
        'HeadstrongOnerousParrotDogFace-Pzk4pAjq0Ws-AuY5',
        'TangibleTalentedPonyRalpherZ-TENdUqv0CL1FhKPq',
        // Episode 2
        'ExuberantEnticingWitchHeyGuys-Wcv6qzZUhQ-mJX9n',
        'TallCrazyChickenTBTacoRight-4AY0kmUinFFw6Lsc',
        'KawaiiEnchantingClintDeIlluminati-CtVitmcX5PxkPojz',
        'TransparentBoredHamsterFunRun-oUCKYudFhRsGC6cB',
        'SparklyPoorMangetoutPeanutButterJellyTime-AP1RmGLR2h1yqnuA',
        'BoldRudeCheesecakeJonCarnage-eqQTB1K5_aHc2hHn',
        'SeductiveCleverPigAMPTropPunch-Z2V4PkrK_mGXmsN0',
        'ArborealPeacefulZebraSpicyBoy-E_mCesApy4766iRo',
        'PiliableBlazingTomatoAMPTropPunch-gizvB689hU74zqOh',
        'ManlyPrettyElephantDuDudu--Su7d54XIENigrcI',
        'SparklingCovertSwallowSeemsGood-WKQomIHiUqmS76xu',
        'GentleFineSandwichHassanChop-M1Wluwk5gKSbVf1G',
        'UnusualViscousJalapenoDxCat-Yb4Bs4UEKa_kYhWQ',
        // Episode 3
        'MuddyTenderChowderGrammarKing-DhpWCkO1YYbD2ktG',
        'BlindingSpineyArugulaNerfBlueBlaster-XhmWQJEyG_VnWXca',
        'CuriousSneakyPterodactylEagleEye-_tNUZyR1PGZWsyTF',
        'ModernCooperativeDumplingsRalpherZ-aRB5GaMZBA2fIoti',
        'GeniusFurtiveSparrowRedCoat-lWR5nEZEdwUlDiVx',
        'SuaveExuberantDonutItsBoshyTime-JztkICSXZb589SrN',
        'ImportantSpotlessStarlingFreakinStinkin-IbKyBsNxcjRI0DCC',
        'FrailInnocentEyeballHumbleLife-qAXpnQimMA7Fg6Mq',
        'RenownedWittyVanillaPJSalt-6WCURajzS_AuUjvw',
        'CrowdedObeseCurlewRitzMitz-refDpDPdnGSFlGLa',
        'LittleGeniusGarbagePMSTwin-08tpWz10gWvTis-y',
        'CredulousYummyHamPartyTime-iJzaT0DvAh3aUmPp',
        'AmusedAliveRamenTBCheesePull-YbpQkgs36gSvnIfQ',
        'EntertainingDeafCrabsYouDontSay-xtPD-UPufRlb0Wus',
        'AntsyThoughtfulDiscSSSsss-kXiWTMseykD53Gc_',
        'PrettyInterestingCoyoteKappaPride-neXiGp39m6znQvPq',
        'ShinyUglySpiderPartyTime-jtK5Rw_GOE87AqYD',
        'PhilanthropicAstuteRamenMau5-iHpoNwR2ay55Rt6f',
        'TenuousCrowdedCarrotEagleEye-t-gy-2ht4QyEN9S3',
        'LachrymoseLightDiamondPlanking-IOz7U2ySvMW4c3ue',
        'FastSwissSpindleSoonerLater-nz8QSt7IrTVjmR4j',
        'IntelligentColdTildeVoHiYo-4BSv_xg__-nwCQAU',
        'BlushingRudeLeopardDatSheffy-zVzLV96z4iJLcGbs',
        'SnappyShakingDeerHassaanChop-1CbBdTGMVID2kcK8',
        // Episode 4
        'ThirstyTalentedCiderHassanChop-O9ANf8uGedEfC4gn',
        'CrepuscularHotAlpacaTwitchRaid-lE9PVmmC1va0KQMB',
        'SlipperyAbstruseChamoisVoHiYo-pV9BNLSlWc15VLoK',
        'LazyEasyYamNinjaGrumpy-UD-vjxILeuSYEUnb',
        'TrustworthyAgreeableClipsdadDerp-SnIk_VNcajkw8qqD',
        'ExpensiveHeartlessEelPogChamp-hH_6bG2XFQaXpTr_',
        'GlamorousPlainGarlicSpicyBoy-dCCgf0KdQCCA5vfp',
        'SwissTangiblePanOhMyDog-of8PtewY5xs0k14k',
        'KawaiiOutstandingMinkRedCoat-sS42R8gh8JlfmTqt',
        'DifferentPeacefulGrasshopperGrammarKing-dFvpEBvzZRX22aYi',
        'KitschySolidHorseAMPTropPunch-bBp1xVsm1XgmXd7e',
        'EphemeralWonderfulPterodactylPipeHype-kQDnPcXnU9ZYXnbm',
        'LightObeseChimpanzeeJebaited-dVimA5iCjWPROneg',
        'OptimisticSaltyMageUnSane-hbz6i-p6SOLPUZm_',
        'DarlingTentativeButterflyChefFrank-e0pvDCb45SrQVkcr',
        'RamshackleVainSowWTRuck-P1KLDht79Zp2VzD4',
        'PoorSmallWombatBigBrother-PZv10lEgbzYP4Ivo',
        'PlausibleAgileMetalDerp-uwRJX5qZ3ov9qWrQ',
        'CovertSaltyTigerYee-UcOWZ1Oy7G7htwbz',
        'UgliestHonorableParrotSuperVinlin-jOGrifsdMbWzNn3A',
        'CuteSuccessfulCrabsEleGiggle-XunRXm9dgCXoJzO_',
        'MagnificentDelightfulCheesecakeHeyGirl-8dv3cmEizdwS8zS2',
        'FragileColdCrowGingerPower-xSZ12TTp30OswROp',
        'DirtyDifficultBearBCWarrior-6nhMG9EOeFqm9y75',
        'PluckyBoxyMelonVoHiYo-Jx-MqEelxXy6HXaO',
        'PeacefulTenuousPhonePJSugar-VVYwLi24I_zmHwOU',
        'LachrymoseFilthyPonySaltBae-A4WwhGJc89g3UAA-',
        'GoldenTangentialPassionfruitChefFrank-XNTCvR4idRnxVTMb',
        'EnchantingTallArtichokeKAPOW-5qaxCLnKEoDvGMye',
        'BumblingAffluentPelicanWholeWheat-I9ThAX3bJ98QpDob',
        'EnergeticAmericanBillSSSsss-bS6G979TVzzhVzyE',
        'CooperativeMoldyWrenchSuperVinlin-6sNe7pVEsMj28XRw',
        'AmericanFragileStarYee-rRhitio2lORaJRTx',
        'UglyAwkwardPheasantSmoocherZ-A0qQYHlawSjYqtba',
        'LaconicEndearingKimchiDansGame--ys2b-0FNysKu7Mn',
        // Episode 5
        'SingleColorfulSageWoofer-cfuIJ102ziUciuwZ',
        'MoldyCleverOctopusPanicVis-POKv0owfNwQk3r5R',
        'BlushingScrumptiousWormLitty-Fx3l2GrN9Ekd3jvJ',
        'BashfulEnticingDogHeyGirl-WEFlAqtJUIZfp_oq',
        'AnimatedGenerousCatLitty-Lp5bHStot4BmMgpx',
        'SmellyCleanReindeerPanicBasket-x2l3wyk91-BDh1dl',
        'BeautifulSpikyPotDerp-zFf0g2ST16KVvz2f',
        'HumbleHonestCaterpillarPanicBasket-7uSaGMZLuiC6oA6A',
        'ProductiveCrowdedLeopardM4xHeh-lddJY9UPqdEwuUYl',
        'BadThirstyPepperoniStinkyCheese-N0jJ3nHB9opNZpJI',
        'CoySuccessfulSpiderTooSpicy-UiVreGYnOfOsNnn9',
        // Episode 6
        'BlightedSavoryMeerkatBIRB-LSEP1jOaf7cWGgQT',
        'SpoopyBovineCrocodileVoteNay-0sG5ByX6omW8lrFQ',
        'CarelessKnottyTriangleFailFish-OvYXBGZw3T2ntD_1',
        'PuzzledDependablePlumageCharlieBitMe-HjUtTcu4L0mlmlob',
        'NurturingIronicDumplingsAllenHuhu-FH0RKCXKs6iVeDy4',
        'NiceHomelySmoothieHeyGuys-mgKOicnKq0cr4A_O',
        'ShortNeighborlyCobraSpicyBoy-GjJy22pqUox_op3C',
        'SmellyBloodyPeanutEagleEye-Nm1UjzNSKF0YQko0',
        'TawdryGlutenFreeSandwichAMPTropPunch-ajfXxeXgy_tn2sOD',
        'ArtsyApatheticGoldfishPunchTrees-lyfC66CC3qbfqprM',
        'FaithfulBravePhoneBIRB-iEkIYRAvYMnQnPfU',
        'CharmingFineZebraWutFace-owiIdQnnlUwtWJhI',
        'BombasticAbrasivePastaRaccAttack-o9ireLA9xruq8Cxb',
        'PeppyEasyVampirePeoplesChamp-KOT5IZvfnLfhPrP7',
        'EsteemedEnjoyableEmuOpieOP-bTukpp5c6YWu4grG',
        'JollyMistySnakeAliens-lUjpfwNh0wlbQ-2l',
        'GlamorousLittleMonitorRaccAttack-ax0rXaIRw-I5vE-d',
        'CrowdedTrappedJaguarThunBeast-BipF3ItjVq1-VkQ8',
        'TameNiceDumplingsCeilingCat-4MUy5kRPEGeOOnID',

        // folge 7
        'UnsightlyRamshackleMarjoramBuddhaBar-V3AcDpnigmjtLy6f',
        'OilySmoggyElkTwitchRaid-k12YxTEPL-jTDBgL',
        'SuperYawningKuduYouDontSay-uyj5Qgi4I2L9DUBw',
        'FunnyMoldyHerdHeyGuys-eynaD3XdSBrSG6DF',
        'SpineyBillowingSushiNotATK-YbddYZZ2mrv-vAgf',
        'RichSlipperyHamRedCoat-B7ADxf-nKaVIDW71',
        'CrowdedTrappedGooseBrainSlug-WFyYIHJseeZsZZ4q',
        'IncredulousAntsyGuanacoArsonNoSexy-EjqUNcmAqQwfpRhg',
        'RacyLivelyVelociraptorSoonerLater-2ZznhHLpwQcoqXU',
        'DependableEnergeticDootVoteNay-Np_cOJztqnJBU7pb',
        'RoughProductiveSkirretPRChase-kAzJ5fUYznnYQDTk',
        'ImpartialCrackyWebBloodTrail-LUS5LRt_S_4mepIr',
        'TenderAmazonianMallardSeemsGood-OoClJvGqSNt-Ne65',
        'LittleTolerantReindeerKAPOW-fCMI5jT0lFSzrsL1',
        'SavoryObliqueWerewolfNomNom-kl8yTEi0BgBmGLNZ',
        'ImpossibleClumsyLlamaGrammarKing-hK9aS1jyZ9HPZZoa',
        'KindWonderfulSushiWTRuck-244OMryliWqgIctn',
        'EnchantingIcyGrasshopperShazBotstix-3cQsfsWX3la04YVh',
        'ProductiveTenderBananaMrDestructoid-pOYga5pH_nrD95CR',
        'GloriousCharmingGarlicCeilingCat-HTWdjOFYAWBPnTEL',
        'AwkwardEasyTireDancingBaby-AZDSYSv6Mpk5URbz',
        'HardJoyousPheasantDendiFace-a5XF6hw10ZkR6NPa',
        'StrangeManlyVelociraptorCopyThis-5sOFivLpUCUp2OOi',
        'SullenEmpathicCrocodileSoBayed-FQOqvPZJAOBESwAs',
        'GorgeousProudButterflyArgieB8-FeM6rwkS7gSWyqox',
        'TubularProudNuggetsDogFace-mfoc-_yPC1CaBA-Z',
        'TransparentEndearingSwallowBabyRage-3WGnYHfvaLvU-kdL',
        'StrangeLuckyFriesPrimeMe-m2yqy-jnCsASvYOP',

        // openEnding
        'GentleStormyWolverinePJSalt-5brkW-dDeuNO9Pol',
        'TangibleAmazingPotSquadGoals-vlEZC98AjZK1VqYH',
        'SpicyTriumphantDragonflyKevinTurtle-oiDaLBP2TtaAZFh3',
        'EnthusiasticShinyLapwingTBCheesePull-Rirk7SP-uLRb8OpM',
        'MistyKitschyPartridgeYouWHY-anZflhPnVlaixb8N',
        'GentleCarefulBobaStoneLightning-aEDFutJLhiONMi2R',
        'ResourcefulBoldTriangleFrankerZ-SvcOh35BcQ7Czhcl',
        'BlatantProudSheepNomNom-GO7jn-s5hLOLsThK',
        'ThoughtfulTangentialAubergineKippa-NTGUGs_KkHgfbdFL',
        'RamshacklePiliableChimpanzeeVoteYea-aLNHniGMIY8OE8R9',
        'AgreeableHealthyKoupreyNomNom-N-X8_PNHbm6tpW3w',

    ];

    protected const array Episodes = [
        [
            'user_id' => 0,
            'title' => 'Episode 1',
            'slug' => 'episode-1',
            'youtube_url' => 'https://www.youtube.com/watch?v=D9PHIxhU_MM',
            'status' => CompilationStatus::Published,
            'created_at' => '2026-01-16 16:00:00',
            'clips' => [
                'StupidHumbleBeeResidentSleeper-nP9QXjKx26XY9k_D',
                'SoftTemperedDeerSmoocherZ-Pg5m0Sz5AdCAUJWO',
                'PlausibleSavorySwordCurseLit-y9LZMS1ERrSSq4N4',
                'PunchyWanderingDootImGlitch-pRyUHeGFUXT9vr3U',
                'CooperativeAmericanPheasantMingLee-5FXZZ91UFu9b-8T3',
                'AttractiveEvilWrenImGlitch-duvV8mJIFIq4C1QT',
                'EasyIronicSharkSaltBae-YoQgrQCxYcGoKwFd',
                'VastMushyElkKappaWealth-UvFk5M20T1-uNIee',
                'ShinyColdbloodedMageCeilingCat-M9KaMexmbCUdXvW4',
                'CreativeApatheticAlpacaM4xHeh-vtAnNPvrgXYZPJTS',
                'CharmingSquareLyrebirdCoolStoryBob-Di5d7-2Ju-O_0VWW',
                'FaintBoringButterSuperVinlin-PcB6n7v1YFpgvpSe',
                'SmellyGorgeousIguanaThunBeast-QCwJQNN9fAO9o9RM',
                'GlamorousImpartialClipzTTours-ZU8SbZn5ZEGHbRV4',
                'CleverKnottyUdonPRChase-N_zw9BP7soQFegFG',
                'BelovedPowerfulHumanVoteYea-JA2ieqE2dTp7Bfdw',
                'ClearDeadWatermelonHassaanChop-bWWVSUUvMXNAbTd3',
                'BoxyFineBorkMrDestructoid-Nz0rwTv3eXGtUM0n',
                'CalmKathishParrotTF2John-4ZpmI1AfPQoEsd_R',
                'IgnorantMuddyLocustAMPEnergy-VUBWoHLFCEteN3C_',
                'HelplessEagerTardigradeSaltBae-LKKuYwxmKhbOgD1G',
                'DeadKindDadUnSane-bRoTdW1iy5ZwuWsN',
                'GrotesqueSpicyFoxHassanChop-Np4vO_C33mjI7G5b',
                'InexpensiveResilientAppleOSkomodo-sbNBVHuO8fDaMmtn',
                'BovineWittyPepperoniSuperVinlin-S1ETPMeUJrPoRoYV',
                'CulturedAverageWerewolfCharlieBitMe-5SdXIxXzpiI9Qt2y',
                'ExpensiveCrunchyElephantKappaClaus-aN4xFHHxAPyUOnM9',
                'InspiringAnnoyingPigOMGScoots-MkuSyJ9cYFx1kCtA',
                'LuckyPrettyWrenchTBTacoLeft-GzKmEqva9LLHVHVs',
                'ScrumptiousDaintyCocoaBrokeBack-nZJf9zZ-OAIc6LS6',
                'WanderingImportantBasenjiBigBrother-ba-JFKyDNodYdOWH',
                'HealthyConcernedSwallowUnSane-EeJIsA8ePcjGi62v',
                'SavoryTangibleNigiriVoHiYo-3y-dv8tbBQgLWYvZ',
                'GentleNimblePanFreakinStinkin-1eErSWrf0oTe4DNb',
                'FamousAltruisticCucumberSaltBae-jMssVVE4G8F1DOWz',
                'TiredPreciousMartenKappaWealth-zFzxz3qlrQYGOWS3',
                'AmericanSecretiveChipmunkCoolCat-y5MA7WfJuezTJ6le',
                'PatientDifficultSpiderDerp-3XSJ5Sy9MSFPvcac',
                'FrozenSeductiveSoybeanTBTacoLeft-68OrgaC-HEgyXI2_',
                'CrepuscularHelpfulWaspVoHiYo-x1qSTRXwuhnCsoV4',
                'SpotlessFaintClipsmomPartyTime-iVeYirUOukt2MMqK',
            ],
        ],
        [
            'user_id' => 0,
            'title' => 'Episode 2',
            'slug' => 'episode-2',
            'youtube_url' => 'https://www.youtube.com/watch?v=2tQbOkXfdGc',
            'status' => CompilationStatus::Published,
            'created_at' => '2026-01-23 16:00:00',
            'clips' => [
                'SarcasticBlightedSangPRChase-iwD6ZpLo6KNnt279',
                'NeighborlySparklingRaisinPeoplesChamp-wTnyIx7hMs_ZI8YY',
                'SillyCreativePlumageStoneLightning-6HYMKhnGQx7Qp9ht',
                'MuddySlickChimpanzeeJebaited-CeEAhbA410rFqmDL',
                'TangibleBetterHorseradishThisIsSparta-Pwo4iY6ud5njguhv',
                'CulturedMiniatureBubbleteaVoteNay-x5JgfuLHQwtYx5Qh',
                'SaltyBovinePrariedogAliens-b10f7yJI_ijIJJh-',
                'SparklyDeterminedVanillaUWot-WS3svdoabNb12QGm',
                'VibrantEphemeralQuailMrDestructoid-o_1ixWnAdkB7jDWH',
                'OriginalSarcasticLaptopHeyGirl-SaGymc43jgUACVnV',
                'MotionlessCloudyLegTinyFace-B8sJIwc8G49VNnkR',
                'GrotesqueStupidSnoodWholeWheat-Qc4jPte4HUOp-2dX',
                'BenevolentImportantOysterVoteYea-bMXRDYEDki7USUIZ',
                'LazyIntelligentFennelNotATK-sBs1Tln-4tZKGK4J',
                'DepressedPlausibleTomatoKreygasm-1NEsfCaFaWk4HCuc',
                'SpineyCrunchyFriesOMGScoots-sS7QVVGAHK5Sxa4Q',
                'SweetAgileTarsierOSsloth-RM6QEHEJ1TGUuXHE',
                'AmusedCautiousAxeMikeHogu-X0Dk8hcCAMFKaiR_',
                'DeliciousAbrasiveOstrichOhMyDog-DoRvCFstZ3PY9Nl2',
                'TastyMoldyPheasantMingLee-oxjvdI7OpTMj_BxN',
                'KathishOilyBottleTooSpicy-1Thwa6Is8VH82eqh',
                'ConfidentTrustworthyToothBabyRage-amBLhsBrzjHpgL2-',
                'HelpfulFlaccidWolfLeeroyJenkins-F7Bp_fEc8Dvv0c8k',
                'TamePopularJalapenoPrimeMe-onoMzK-aqVqlGAdt',
                'MoldyTolerantPeppermintCoolStoryBro-u4ZMYFl6vahmQD1V',
                'ChillyRealFishDBstyle-PFpjB4JV3VH5-GSG',
                'PerfectCallousApeNotLikeThis-FmdLIDFMAJ4Rjze5',
                'HyperBusyPenguinUnSane-R6nZS_PRE57qp_py',
                'SplendidImpossibleKuduLitty-Oa0BwA37orawprrN',
                'ImportantIntelligentParrotHassanChop-xglTuLCPxtVjHBDM',
                'ShortWittyMangoWOOP-QZeZj9AVcvUdYOfW',
                'HilariousAntsyDonkeyNerfRedBlaster-FEINwuAhhUrBur3l',
                'FurryMoistSkirretTooSpicy-eTWeXugYLK385YVc',
                'FriendlyHeadstrongBillGOWSkull-hYvbWd2hdoVtoeMR',
                'DignifiedHappyFennelGingerPower-G1R4wu6DxjZ4sKBm',
                'BusyEnticingPonyTheTarFu-S1t8cOA_gE2Vtb9W',
                'FairEmpathicLaptopOhMyDog-LkjlqxgRzwflZ4wm',
                'DarkDeadLyrebirdYee-yTjGwJRT9qkc3YR5',
                'SwissCourteousBaconTBTacoRight-Aaz5mLHUOoPTQSPX',
                'ShakingStrangeAdminWutFace-6VNLFSas71V71QXF',
                'TastyAntsyCookieEagleEye-QoE9GHi-zgDTD8jh',
                'FrailImpossibleJellyfishDancingBaby-zO5_xSX-bOdq_Ftt',
                'TalentedShyGarageMVGame-keqU8I7XJ5FI1G34',
                'YawningEnticingDragonfruitItsBoshyTime-XRA3St7AI1k49hML',
                'SuccessfulResourcefulPlumPartyTime-iPGxDnmhkiYcc1p7',
                'AverageAgilePancakeKevinTurtle-MI4eLE8P2oPOsoKs',
                'GenerousBoxySwanFloof-HM_jprvcQDR7WnwR',
            ],
        ],
        [
            'user_id' => 0,
            'title' => 'Episode 3',
            'slug' => 'episode-3',
            'youtube_url' => 'https://www.youtube.com/watch?v=-BX7qzTCt3U',
            'status' => CompilationStatus::Published,
            'created_at' => '2026-01-30 16:00:00',
            'clips' => [
                'WittySassyCroissantPoooound-dnh0LrXD_Ub4Q9dQ',
                'OptimisticPolishedZebraDansGame-JbwNl0XK-70cnKs6',
                'CoyIncredulousGooseStrawBeary-G4b9M0wpGKE-ekfc',
                'EndearingNastyOxKippa-6ZQBCAFPhNzCFQl7',
                'BitterKitschyFiddleheadsPeoplesChamp-aiT5GANb5ebo3czb',
                'FunOptimisticTrollPRChase-36HigJphxyYhwww8',
                'WonderfulOddVultureYee-mUHZx-F8CJl7j32b',
                'EmpathicInterestingTermiteNotLikeThis-t14_6HDIkIfFRyrn',
                'JoyousKindPangolinAMPEnergy-SqtiEytiqGMHRArD',
                'OptimisticMiniatureChickpeaHassanChop-qS0zkmYczWSYAAwU',
                'GoodCallousMonkeyAMPEnergyCherry-wFg-XUTexcqt8SIQ',
                'KnottyArborealYakinikuFeelsBadMan-MaoUkRfna03-a_v1',
                'SquareCreativeClamNerfBlueBlaster-B5sf3Kvk63eWJwuv',
                'SwissKnottyLousePoooound-bAh3WUe1pXUWTync',
                'AnimatedGoldenLaptopRitzMitz-DKPrbIj7lJzFyyK7',
                'BreakableAnnoyingBobaTheTarFu-gm8il_EIarJvyUvc',
                'BlushingWimpyCasetteDatBoi-urxkRPureBgccey_',
                'ArtsyTenaciousMoonBrainSlug-XmjMhrMW_XzI0Kyb',
                'PiercingEsteemedLardMrDestructoid-O8NlVDOif2rUhCDo',
                'MushySpineyJackalOSfrog-f6ORyCUEJ0tclaYa',
                'BrightZealousCodAliens-0DU5tTVQKVjQB9xV',
                'FrailClumsyDadAMPEnergy-GxadwZ61U6xsxmN9',
                'OptimisticTangibleDogeUWot-TjkhPnsbdskziBI1',
                'CoweringPlausibleSalamanderAMPEnergyCherry-Noqh0qaHBANyHazb',
                'OutstandingCrispyChimpanzeeGivePLZ-a-XNZqlb-gQ_aXLG',
                'HappySuaveWatercressFUNgineer-aZqh0TxfbZjTrsDj',
                'ObliviousScaryKathyBibleThump-9P0tCp2svNC70VxG',
                'SullenBrightCobraGivePLZ-5saN78vW9W0MvqlS',
                'DrabInventiveBibimbapTakeNRG-HlGljvlNFuySqlIY',
                'SuccessfulArborealBaboonKAPOW-o7-YESrsTJvlBcU-',
                'SpikyTangibleDotterelAliens-Ywzv0-w6uCeg2UH5',
                'BraveBumblingMonkeyJKanStyle-ujWXzs-Jz1bc73kQ',
                'BoredFitEyeballPraiseIt-cDvHV09Ba5tSB6Hb',
                'StylishImpartialOrcaHeyGuys-lmObfR7X38JtQMW4',
                'ObedientGiftedPhoneHassaanChop-JojRk22BBuvSKC9T',
                'ThirstyCallousPieBlargNaut-c1Pa2-CKdlsGmMtT',
                'MuddyConcernedGerbilPJSugar-q4VK_xoSKRGaVRUs',
                'ClearDelightfulEggCclamChamp-9pe64XifCPUUwAOf',
                'TalentedSpicyAlbatrossNinjaGrumpy-DLzVtz7H4r9R1qMJ',
                'SplendidPiercingPuddingArsonNoSexy-OMj5X6TA1lxCedfq',
                'LovelyEndearingPenguinArgieB8-cLQ2v0eRxnsAWp9Y',
            ],
        ],
        [
            'user_id' => 0,
            'title' => 'Episode 4',
            'slug' => 'episode-4',
            'youtube_url' => 'https://www.youtube.com/watch?v=AC9bdrzWiOo',
            'status' => CompilationStatus::Published,
            'created_at' => '2026-02-06 16:00:00',
            'clips' => [
                'SweetHomelyChoughJKanStyle-27iiaAUv5PIYmNXe',
                'TawdryHardFriseeBudBlast-VlaBIgZThOHrcmSi',
                'EncouragingKindHornetMcaT-Xid-73s0LspQQA3L',
                'KathishHomelyCrowPlanking-SCM-bL7CgQkc24ID',
                'CrowdedSpunkyDotterelDerp-0hf71PHgrZlvg0w-',
                'AnimatedRoughTapirJonCarnage-3LsA-0wcV8jH42mi',
                'AdventurousLivelyBearCeilingCat-k7AcO3476q5gSlGn',
                'PoliteThankfulCrabAsianGlow-f8MWSZp93alqkU3i',
                'ClearSlickOxArgieB8-mRQVHijMFYaicXND',
                'ResilientHeadstrongGrouseJebaited-sX4VApYf7ozG-Hap',
                'CrowdedBlitheTrollPJSugar-XNe8cZe872riPg-p',
                'DeterminedRichChickpeaCoolCat-jttXk3pwqwTUDwV7',
                'SmellyFrailDelicataGivePLZ-QDmKaDwuKuJtZJf3',
                'EsteemedDreamyAlmondKeepo-LYNWtJx1AgphG-42',
                'LivelyDiligentGorillaJKanStyle-9sW9dY5oPvOYuMNO',
                'EncouragingHelpfulFishBibleThump-oqs0OvKSSrAGrbmB',
                'HumbleWonderfulFerretM4xHeh-gfSzPjyNlXRKM-0G',
                'CautiousMoldyDragonfruitPanicBasket-XcBV8v-OgnxxAdI4',
                'PunchyBitterChipmunkDoritosChip-O8oX32lAmvKfG3Xk',
                'BraveShakingAlpacaSSSsss-Jy1XiZAxrOGLd_l9',
                'ZealousSmallMomKeyboardCat-AYdvTTavJ8TEd3pr',
                'TameSmoothWasabiChefFrank-8Hf60aMnPxSU5QX1',
                'InquisitiveArbitraryChickpeaOSkomodo-F9g_YYkrPzcjBxQN',
                'BlushingFrailSheepPunchTrees-F1LdWe_JXV8Cj9PE',
                'FlirtyHedonisticLaptopLeeroyJenkins-XsWdnPktoBbvrLVW',
                'AgreeableLazyChickpeaPermaSmug-LRVNuz-DccaV54b3',
                'TawdryAnnoyingPrariedogJKanStyle-tOedvV4J16uvjmDM',
                'TallFantasticSnailLitFam-A-lhkcyXhTRpKv-M',
                'ShortCorrectCheeseKappaRoss-nj7SCUvFm4SqgPFr',
                'TrustworthyPlayfulMagpieKippa-zfvCFt-4rfwdZPdv',
                'SinglePlumpCatKAPOW-naHcPLEC2kxrnNxb',
                'BetterStupidYogurtPhilosoraptor-SoFVyYpR5EOEi466',
                'SincereThankfulKoupreyMoreCowbell-rqWUMhL9asACRPji',
                'SingleTriangularPidgeonBrainSlug-zA4Kp-BckWCNqv24',
                'AnimatedNiceLardPrimeMe-jLAxTci8xutSXnuv',
                'CooperativePricklyGooseSaltBae-v9qjwG25wYFn0n3q',
                'MoralIntelligentNostrilItsBoshyTime-8R-3k_p4qGJKH_Y1',
                'SnappyDeafOtterNerfBlueBlaster-sVQlgo7dBc-aDb3d',
                'SuaveWanderingSlothPipeHype-l980bWzBr58WAfyD',
            ],
        ],
        [
            'user_id' => 0,
            'title' => 'Episode 5',
            'slug' => 'episode-5',
            'youtube_url' => 'https://www.youtube.com/watch?v=jvuk243L8z4',
            'status' => CompilationStatus::Published,
            'created_at' => '2026-02-13 16:00:00',
            'clips' => [
                'BlushingProductiveNikudonDoubleRainbow-_HBL_ULg-G4xiKXx',
                'CarelessDullOrangeEleGiggle-ZEpTZ0eTJy3hO_Di',
                'HardZanyButterflyDerp-hNw2oGqAUecSsKmY',
                'HeartlessInquisitiveSandstormCclamChamp-oAqWhb6bG08H3QG2',
                'PoliteGrotesqueWasabiYouWHY-igQgMRUbKr6xwu6d',
                'ArtisticHandsomeNightingaleMrDestructoid-M0Ee2o2KEmXchsRO',
                'SpeedyCarefulFinchPJSugar-vu5thW7trthlGLmh',
                'TenaciousAstuteTeaTriHard-Earv5baovT2GEgU6',
                'SoftTolerantFlyLeeroyJenkins-Ky_gnEC0m-vqwgmF',
                'PlumpEasyBeanCoolStoryBob-ieWXP9YUqdMbKedt',
                'ImpartialCrispyYamPartyTime-HXXWk0ELpQH2-nHC',
                'IcyGleamingCucumberKeyboardCat-9m3T8q-AwMSWUBMh',
                'QuaintEnergeticSkirretBleedPurple-NIuoKf7Ogrx_oUwo',
                'DepressedLivelyRutabagaWTRuck-Az3AEfecJJge5UeY',
                'SuccessfulConsideratePorcupineWholeWheat-cjDlwC3cEH-xWLpx',
                'NaiveSuspiciousCucumberHassaanChop-bgvd2rVUvrcjn7P-',
                'GoodSinglePeachRickroll-y2G1CsUBLsV-62zC',
                'SteamyBillowingNarwhalThisIsSparta-Nt0_pyBIZ7UNe9fS',
                'ExquisitePeacefulElephantVoHiYo-MexS3jZ5GpfsM95_',
                'CourteousSourSalsifyHassanChop-pOClTvFPH1ybKAi0',
                'DifferentSpikyLlamaTheTarFu-UK8RRfYhb_A2XFpQ',
                'PerfectAntsyScorpionPartyTime-a5IoajGm7muZeQiM',
                'AcceptableBlightedOryxPicoMause-Hk7iYPlgshqQAyyf',
                'AliveIronicSheepDancingBanana-5tSYgNh_7S5x58mh',
                'DistinctCooperativeOkapiImGlitch-og2-CvYwYMYGDVWv',
                'GorgeousPiercingKiwiBIRB-Zw2r1WoSnfrqtWG3',
                'StrangePlacidGiraffeDxAbomb-u9nQKeTXrnnwbUU8',
                'PluckyPrettyLobsterHassanChop-kHM3uJJ7q79uqOOZ',
                'PrettyAmazingMochaRalpherZ-3jvM5cgcwxiec6hs',
                'ModernOutstandingMonkeyCurseLit-3h5mnLd7sZJJDMLN',
                'HealthyDistinctGalagoShadyLulu-IfdAv852nQmdVuKZ',
                'FaintFragileRaccoonStoneLightning-hvofOmPssdMod6aR',
                'DeliciousEnergeticZucchiniPJSalt-D_6dc9fkfgTi6tsS',
                'BeautifulPiliableTubersPupper-W-tYmUe0II7QmGU1',
                'TenuousSparklingWeaselBCWarrior-Pu9ggzbJZxDEd8wd',
                'SpunkyCulturedWasabiSwiftRage-EkCDH401E6AmugzV',
                'TallPreciousMuleDatSheffy-iCERvDNtgryrJv-r',
                'SmoothBlitheDogeGOWSkull-EQtQGXmjnIRV7uho',
                'ColdOptimisticGullRlyTho--5xmA5Bs_CGFYBSu',
                'TiredObeseTermiteMingLee-etWvZnM-e4jQFBvp',
                'IntelligentRefinedKeyboardCoolStoryBro-iNW1LXlr8a_fQbfp',
                'SpotlessVastMosquitoANELE-KjIE1q5nR2exBqRO',
                'SuaveSuccessfulHippoTooSpicy-EGC-fUu-NuzsOJyb',
                'AbnegateSlipperyBulgogiUnSane-uWS1v7mESK2e_6MQ',
                'GenerousTemperedRamenBudBlast-RTVHu1SXRYzMn87k',
            ],
        ],
        [
            'user_id' => 0,
            'title' => 'Episode 6',
            'slug' => 'episode-6',
            'youtube_url' => 'https://www.youtube.com/watch?v=7QZH4rYSKVI',
            'status' => CompilationStatus::Published,
            'created_at' => '2026-02-20 16:00:00',
            'clips' => [
                'MildPluckyFlyYouDontSay-q593sExs4GxEoRcz',
                'EmpathicCoweringCroissantEagleEye-wQ8LO6CYfaj4gExY',
                'SuspiciousIronicRhinocerosJonCarnage-xBwNgbHoISEW7wTS',
                'HealthyYawningClintmullinsHeyGirl-oZiNYq3z4zcURPeP',
                'CoySuccessfulSpiderTooSpicy-UiVreGYnOfOsNnn9',
                'StormyMistyPorcupineSMOrc-jX9S7pz1fezPaKy8',
                'RefinedJazzyStarlingJonCarnage-VJVqWbUGIAeu_heU',
                'BloodyMiniatureJuicePartyTime-oyalmZF13wWeK3Xq',
                'WittyDoubtfulOctopusSwiftRage-Ia7ZO4KAc2bgQU3_',
                'TemperedCleverPancakeMrDestructoid-0qZ-i_C71FNrylRU',
                'GloriousPoliteEchidnaPeteZaroll-WyvC8dNHB9g6Sumd',
                'ArborealTawdryApeFunRun-wH8ZeRe_z7EhFGIb',
                'LaconicEndearingKimchiDansGame--ys2b-0FNysKu7Mn',
                'RichDeadFinchSeemsGood-h7jgXiVb4h85-tcw',
                'RudeRepletePhonePJSalt-csVP-PFuuwSHyxxq',
                'InquisitiveLachrymoseAntEleGiggle-wr5kXCcIi_aJARUd',
                'AthleticToughNarwhalShazBotstix-lYkiURL_ATF6tG_d',
                'EasyBlatantMinkDatBoi-NIDmzDHM-dHDfR-A',
                'BumblingAbnegateGarbageDansGame-Bqt2dy08WYKLFQla',
                'AffluentLovelyTroutKlappa-5wlw4mwgJ3JYYp-d',
                'CleanFancyFishCoolStoryBro-nsU0XdAdEySbhCP_',
                'ProductiveLovelyDelicataOhMyDog-AzjvQ-6UczOO_-h0',
                'SpookyArtsyMarjoramThisIsSparta-EFUe_l-sq1ZkQgeA',
                'SplendidConsiderateIguanaCopyThis-9YufKX7t8iiqTyh-',
                'SourAssiduousBulgogiPlanking-VQNdQXNQqd5w9cJp',
                'PoisedSnappyBarracudaItsBoshyTime-HEuWSRrYhqnOdwZ7',
                'ResilientObliquePigeonItsBoshyTime-eJsZwQCwAMoBfpyx',
                'HumbleBrightSalsifyBlargNaut-l432OuHFIKJFWR68',
                'NaiveSpicyShieldFunRun-ZHh2vU_ozLcvu3FW',
                'RealGloriousCheetahBigBrother-M0k4McoSUC3ZAw4J',
                'TenaciousFancyCheeseRickroll-G8SMqDK7AYTL_Wuk',
                'HungryBlightedDootPMSTwin-ZgVfoVCOJhC11UHt',
                'FrigidDifficultShallotNerfBlueBlaster-qMPGa_Uxdi1gq2JW',
                'RichCallousJackalSSSsss-BB8A1RhOaknUDRqH',
            ],
        ],
        [
            'user_id' => 0,
            'title' => 'Episode 7',
            'slug' => 'episode-7',
            'youtube_url' => 'https://www.youtube.com/watch?v=UG98Ygvlzhg',
            'status' => CompilationStatus::Published,
            'created_at' => '2026-02-27 16:00:00',
            'clips' => [
                'RenownedAggressivePoxOSsloth-5naxxLsMcY_gQrrW',
                'InventiveSpoopyGoldfishCoolStoryBro-IHwtAlunymgLsl2H',
                'UninterestedTransparentCasettePeteZaroll-4B0RW1XlRpO8ZkKJ',
                'FlaccidSullenLaptopArgieB8-R-YBkX6Z79TbvhWm',
                'HumbleBeautifulDonkeyWTRuck-erkw0B7uCo96mR0z',
                'DeafIcyAmazonJonCarnage-HNKpsG0CfnVHbo83',
                'ResoluteSuccessfulTildeCorgiDerp-oRD6i-fpyqSpW6rp',
                'TsundereNaiveEchidnaWTRuck-tMijRiUlY2gXIxRk',
                'CarefulPluckyLampDoritosChip-duMMsnj6QgM452Cj',
                'BoringPuzzledGiraffeOpieOP-3ORdDhR5F2vU4Nra',
                'DeadCleanCurlewTwitchRaid-yCGqhZAetV7b4n9a',
                'BillowingTacitBubbleteaRalpherZ-Gf3CFxFS6cNYSDvG',
                'ZanySquareEndiveEagleEye-QprkHuuVCLqFywm6',
                'SillyNastyMinkBrokeBack-TY3MShmhHpiwa2kv',
                'EndearingUnsightlyButterTwitchRPG--8EfhtYhrnMfr_Hw',
                'RelentlessInexpensiveWoodcockYouDontSay-watqyru0c_SUFu4y',
                'SilkyAstuteDoveThisIsSparta-gjKBVp2-Qn2KMU5y',
                'SecretiveSmellyElephantOhMyDog-FONl8DzU43cg0vGn',
                'DifficultFurtiveClipsmomCharlieBitMe-F8x-V9uTo9XpP2A5',
                'FurtiveProductiveNikudonLitty-E6RIGBPY6MajVdvs',
                'AmericanFunnyDugongTakeNRG-BrP4oLNbpvBXeEmS',
                'BlithePricklyPonyDoritosChip-C_mjC42LKOJVHFkx',
                'SlipperyCallousAsteriskHoneyBadger-Ih993Pbrme0Yxp1d',
                'BenevolentAbstemiousOrcaBrokeBack-_efVdJHmK0KCHYHB',
                'PiliableRoundPidgeonPeteZaroll-nV1qBW6IFhhjF473',
                'TubularSpeedyCheetahYouDontSay-47lMDONU419Ic8y4',
                'ShyEndearingWafflePupper-mM5X2QVQWSmeYNkd',
                'BlueSilkyPoxSpicyBoy-ESRLfBwBGvlZB-X4',
                'ScaryAbrasiveDadArsonNoSexy-v5iiWODKOgiv2pQv',
                'HomelyGlutenFreeNewtTTours-9fwx3EVfy97HWWV8',
                'PrettiestInspiringGarlicPeanutButterJellyTime-i7BgX4Pk6Bctd_qK',
                'HeadstrongAdorableKeyboardChefFrank-HF1dEi8Vl48ku9G4',
                'AmericanRudeTeaTinyFace-hTVT-gthooViMrYF',
                'DiligentScaryCarrotStrawBeary-e9Fg7YscjnUXe5kF',
                'FuriousPlacidCourgetteYouDontSay-rtWNoO3-7BRVH854',
                'FantasticDarkCroquetteCeilingCat-1ZzT-PqE8Mg-J_nC',
                'CloudySavoryPotShazBotstix-PlCdkshU0zMQpPqp',
                'ToughEnergeticClipsmomDoggo-WlZdlLFkPHHepnbR',
            ],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(ImportClipAction $importClipAction, TwitchService $twitchService): void
    {
        if (Compilation::count() > 0 || app()->environment('testing')) {
            return;
        }

        $systemUser = User::find(0);
        $allClips = collect(self::Clips);

        foreach (self::Episodes as $episode) {
            $allClips = $allClips->merge($episode['clips']);
        }

        $allClips = $allClips->values()->unique();
        $twitchClips = collect();
        $missingClips = collect();

        Log::notice("Importing {$allClips->count()} Clips...");

        $allClips->chunk(100)->each(function ($chunk, $index) use ($twitchService, &$twitchClips, &$missingClips): void {
            $requestedIds = $chunk->values()->toArray();
            $params = ['id' => $requestedIds];

            try {
                /** @var ClipDto[] $clips */
                $clips = $twitchService->get(TwitchEndpoints::GetClips, $params);

                $fetchedClips = collect($clips);

                $missingCount = count($requestedIds) - $fetchedClips->count();
                if ($missingCount > 0) {
                    $foundIds = $fetchedClips->pluck('id')->toArray();
                    $missingIds = array_diff($requestedIds, $foundIds);
                    $missingClips = $missingClips->merge($missingIds)->values()->unique();

                    Log::warning("Chunk {$index}: Requested ".count($requestedIds).' clips, but Twitch only returned '.$fetchedClips->count().'. Missing: '.implode(', ', $missingIds));
                } else {
                    Log::info("Chunk {$index}: Successfully fetched all ".count($requestedIds).' clips.');
                }

                $twitchClips = $twitchClips->merge($fetchedClips)->values();
            } catch (Exception $e) {
                Log::error('Twitch API Error: '.$e->getMessage());

                return;
            }

            sleep(1);
        });

        $twitchClips->each(function (ClipDto $clip) use ($systemUser, $importClipAction): void {
            $importClipAction->execute($clip, $systemUser);
        });

        Log::notice("{$twitchClips->count()} Clips have been imported.");

        foreach (self::Episodes as $episodeData) {
            /** @var Compilation $compilation */
            $compilation = Compilation::create([
                'user_id' => 0,
                'title' => $episodeData['title'],
                'slug' => $episodeData['slug'],
                'status' => $episodeData['status'],
                'type' => CompilationType::LongVideo,
                'youtube_url' => $episodeData['youtube_url'],
                'created_at' => $episodeData['created_at'] ?? now(),
                'updated_at' => now(),
            ]);

            /**
             * @var Collection $clips
             */
            $clips = Clip::query()
                ->withoutGlobalScope(ClipPermissionScope::class)
                ->whereIn('twitch_id', $episodeData['clips'])
                ->pluck('id')
                ->map(fn (int $id): array => [
                    'clip_id' => $id,
                    'claimed_by' => 0,
                    'claimed_at' => now(),
                    'status' => CompilationClipStatus::Completed,
                ]);

            $compilation->clips()->sync($clips);

            $clips->each(function (array $clip): void {
                DB::table('clips')->where('id', $clip['clip_id'])->update(['status' => ClipStatus::Approved]);
            });

            $compilation->comments()->create([
                'body' => "Compilation with {$clips->count()} clips has been imported.",
                'author_id' => $systemUser->getKey(),
                'author_type' => $systemUser->getMorphClass(),
            ]);
        }

        // Force-run the import Job because we are lazy
        ImportCategoryJob::dispatchSync([]);
    }
}
