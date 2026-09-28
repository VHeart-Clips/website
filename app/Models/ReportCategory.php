<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Reports\ReportCategoryDetailsType;
use App\Models\Traits\Auditable;
use Database\Factories\ReportCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
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

    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function subCategories(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'category_id');
    }

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

    protected function casts(): array
    {
        return [
            'name' => 'json:unicode',
            'summary' => 'json:unicode',
            'description' => 'json:unicode',
            'details_type' => ReportCategoryDetailsType::class,
            'reportable_types' => 'json',
            'is_note' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
