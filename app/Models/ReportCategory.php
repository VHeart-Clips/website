<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Reports\ReportCategoryDetailsType;
use App\Enums\Reports\ReportCategoryReportableType;
use App\Models\Traits\Auditable;
use Database\Factories\ReportCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Spatie\Translatable\HasTranslations;

class ReportCategory extends Model
{
    use Auditable;

    /** @use HasFactory<ReportCategoryFactory> */
    use HasFactory;

    use HasTranslations;
    use SoftDeletes;

    public array $translatable = [
        'name',
        'summary',
        'description',
    ];

    /**
     * @return BelongsTo<ReportCategory, $this>
     */
    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<ReportCategory, $this>
     */
    public function subCategories(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * @return HasMany<Report, $this>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'category_id');
    }

    /**
     * @return HasManyThrough<Report, ReportCategory, $this>
     */
    public function subCategoryReports(): HasManyThrough
    {
        return $this->hasManyThrough(Report::class, self::class, 'parent_id', 'category_id');
    }

    protected static function booted(): void
    {
        static::creating(static function (self $category): void {
            $category->sort_order ??= (static::withTrashed()->max('sort_order') ?? -1) + 1;
        });
    }

    /**
     * @param  Builder<self>  $query
     * @param  ReportCategoryReportableType|string|array<int, ReportCategoryReportableType|string>  $types
     */
    #[Scope]
    protected function whereAppliesToReportableType(Builder $query, ReportCategoryReportableType|array|string $types): void
    {
        $types = Arr::wrap($types);

        $query->where(function (Builder $query) use ($types) {
            $query->whereNull('reportable_types')
                ->orWhereJsonLength('reportable_types', 0);

            foreach ($types as $type) {
                $query->orWhereJsonContains('reportable_types', $type);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'name' => 'json:unicode',
            'summary' => 'json:unicode',
            'description' => 'json:unicode',
            'details_type' => ReportCategoryDetailsType::class,
            'reportable_types' => AsEnumCollection::of(ReportCategoryReportableType::class),
            'is_note' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
