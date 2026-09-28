<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\ReportCategory;
use App\Models\User;

class ReportCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::ViewAnyReportCategory);
    }

    public function view(User $user, ReportCategory $reportCategory): bool
    {
        return $user->can(Permission::ViewAnyReportCategory);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::CreateReportCategory);
    }

    public function update(User $user, ReportCategory $reportCategory): bool
    {
        return $user->can(Permission::UpdateAnyReportCategory);
    }

    public function delete(User $user, ReportCategory $reportCategory): bool
    {
        return $user->can(Permission::DeleteAnyReportCategory);
    }

    public function restore(User $user, ReportCategory $reportCategory): bool
    {
        return $user->can(Permission::RestoreAnyReportCategory);
    }

    public function forceDelete(User $user, ReportCategory $reportCategory): bool
    {
        if ($reportCategory->subCategories()->count() > 0 || $reportCategory->reports()->count() > 0) {
            return false;
        }

        return $user->can(Permission::ForceDeleteAnyReportCategory);
    }
}
