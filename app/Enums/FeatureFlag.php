<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Traits\FeatureFlagMagic;
use App\Enums\Traits\HasHeadlineLabel;
use App\Support\FeatureFlag\Attributes\DefaultFeatureFlagState;
use App\Support\FeatureFlag\Attributes\Description;
use App\Support\FeatureFlag\Attributes\Environment;
use Filament\Support\Contracts\HasLabel;

enum FeatureFlag: string implements HasLabel
{
    use FeatureFlagMagic;
    use HasHeadlineLabel;

    #[Description('Controls the Clip Submission feature')]
    #[DefaultFeatureFlagState(true)]
    case ClipSubmission = 'clip_submission';

    #[Description('Controls the Clip Voting feature')]
    #[DefaultFeatureFlagState(true)]
    case ClipVoting = 'clip_voting';

    #[Description('Controls the Reporting feature')]
    #[DefaultFeatureFlagState(true)]
    case Reports = 'reporting';

    #[Description('Bypasses the Broadcaster Consent globally, useful for debugging locally')]
    // #[Environment(['local', 'staging'])] disabled for the softrelease
    case IgnoreBroadcasterConsent = 'bypass_broadcaster_consent';

    #[Description('Shows the about-us page on index (also hides the footer item and route for about us)')]
    case AboutUsAsIndex = 'about_us_as_index';

    #[DefaultFeatureFlagState(true)]
    case UserDashboard = 'user_dashboard';

    #[DefaultFeatureFlagState(true)]
    case UserSettings = 'user_settings';

    #[Description('Toggles the user navigation/dropdown in the top navigation, this is only a visual change and does NOT disable the features shown in that dropdown.')]
    #[DefaultFeatureFlagState(true)]
    case UserNavigation = 'user_navigation';

    #[DefaultFeatureFlagState(true)]
    case BroadcasterOnboarding = 'broadcaster_onboarding';

    #[Description('Toggles the Tenant Feature in the Broadcaster Dashboard')]
    #[DefaultFeatureFlagState(false)]
    case BroadcasterTenant = 'broadcaster_tenant';

    #[Description('Enables the automatic clip archive schedule')]
    #[DefaultFeatureFlagState(true)]
    case ArchiveClipsSchedule = 'archive_clips_schedule';

    #[Description('Toggles the Broadcaster User Submission Filter Manager in the Broadcaster Dashboard Settings')]
    #[DefaultFeatureFlagState(false)]
    case BroadcasterUserSubmissionFilterManager = 'broadcaster_user_submission_filter_manager';

    #[Description('Toggles some hidden debug tools')]
    #[DefaultFeatureFlagState(false)]
    #[Environment(['local', 'staging'])]
    case Debug = 'debug';

    #[Description('Allows the broadcasters to submit removal requests from their dashboard')]
    #[DefaultFeatureFlagState(true)]
    case BroadcasterRemovalRequestsDashboard = 'broadcaster_removal_requests_dashboard';
}
