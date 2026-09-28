<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Reports\ReportCategoryDetailsType;
use App\Models\ReportCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportCategory>
 */
class ReportCategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_id' => null,
            'name' => [
                'en' => fake('en_US')->words(2, true),
                'de' => fake('de_DE')->words(2, true),
            ],
            'summary' => [
                'en' => fake('en_US')->sentence(),
                'de' => fake('de_DE')->sentence(),
            ],
            'description' => [
                'en' => fake('en_US')->paragraph(),
                'de' => fake('de_DE')->paragraph(),
            ],
            'reportable_types' => null,
            'is_note' => false,
            'details_type' => ReportCategoryDetailsType::Optional,
            'sort_order' => 0,
        ];
    }

    public function childOf(ReportCategory|int|null|self $parent = null): static
    {
        return $this->state(fn (): array => [
            'parent_id' => $parent ?? ReportCategory::factory(),
        ]);
    }

    /**
     * @param  array<int, string>  $types
     */
    public function forTypes(array $types): static
    {
        return $this->state(fn (): array => ['reportable_types' => $types]);
    }
}
