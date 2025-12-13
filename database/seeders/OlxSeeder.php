<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Category;
use App\Models\CategoryField;
use App\Models\CategoryFieldOption;

class OlxSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Starting OLX Seeder...');

        $categories = $this->fetchCategories();

        if (empty($categories)) {
            $this->command->warn("No categories fetched. Seeder finished.");
            return;
        }

        foreach ($categories as $rawCategory) {
            $category = $this->processCategory($rawCategory);

            if (!$category) {
                continue;
            }

            $fields = $this->fetchCategoryFields($category->external_id);

            if (empty($fields)) {
                $this->command->warn(
                    "No fields found for category {$category->external_id}"
                );
                continue;
            }

            $this->processFields($category, $fields);
        }

        $this->command->info("OLX seeder finished successfully.");
    }

    /**
     * 🔹 Fetch categories from OLX (cached daily)
     */
    protected function fetchCategories(): array
    {
        return Cache::remember(
            'olx.categories',
            now()->addDay(),
            function () {
                $url = "https://www.olx.com.lb/api/categories";

                try {
                    $response = Http::retry(3, 200)->get($url);
                } catch (\Throwable $e) {
                    $this->command->warn(
                        "Could not fetch categories: " . $e->getMessage()
                    );
                    return [];
                }

                if ($response->failed()) {
                    $this->command->warn(
                        "OLX returned status {$response->status()}"
                    );
                    return [];
                }

                $categories = $response->json();

                if (!is_array($categories)) {
                    $this->command->warn("Invalid categories format.");
                    return [];
                }

                return $categories;
            }
        );
    }

    /**
     * 🔹 Save / update category
     */
    protected function processCategory(array $rawCategory): ?Category
    {
        $externalId = data_get($rawCategory, 'id');
        $name       = data_get($rawCategory, 'name');

        if (!$externalId) {
            return null;
        }

        $category = Category::updateOrCreate(
            ['external_id' => $externalId],
            [
                'name' => $name,
                'raw'  => $rawCategory,
            ]
        );

        $this->command->info(
            "Imported category: {$name} (external_id: {$externalId})"
        );

        return $category;
    }

    /**
     * 🔹 Fetch category fields (cached per category daily)
     */
    protected function fetchCategoryFields(int $externalId): array
    {
        return Cache::remember(
            "olx.category_fields.{$externalId}",
            now()->addDay(),
            function () use ($externalId) {
                $url = "https://www.olx.com.lb/api/categoryFields"
                    . "?categoryExternalIDs={$externalId}"
                    . "&includeWithoutCategory=true"
                    . "&splitByCategoryIDs=true"
                    . "&flatChoices=true"
                    . "&groupChoicesBySection=true"
                    . "&flat=true";

                try {
                    $response = Http::retry(3, 200)->get($url);
                } catch (\Throwable $e) {
                    $this->command->warn(
                        "Skipping fields for category {$externalId}. Exception: " . $e->getMessage()
                    );
                    return [];
                }

                if ($response->failed()) {
                    $this->command->warn(
                        "Fields API returned {$response->status()} for category {$externalId}"
                    );
                    return [];
                }

                $payload = $response->json();
                $fields  = [];

                // Common fields
                if (
                    isset($payload['common_category_fields']['flatFields']) &&
                    is_array($payload['common_category_fields']['flatFields'])
                ) {
                    $fields = array_merge(
                        $fields,
                        $payload['common_category_fields']['flatFields']
                    );
                }

                // Category-specific fields
                if (
                    isset($payload[$externalId]['flatFields']) &&
                    is_array($payload[$externalId]['flatFields'])
                ) {
                    $fields = array_merge(
                        $fields,
                        $payload[$externalId]['flatFields']
                    );
                }

                return $fields;
            }
        );
    }

    /**
     * 🔹 Save / update category fields and options
     */
    protected function processFields(Category $category, array $fields): void
    {
        foreach ($fields as $fieldRaw) {
            $handle = data_get($fieldRaw, 'name');

            if (!$handle) {
                $this->command->warn(
                    "Skipping a field with missing name in category {$category->external_id}"
                );
                continue;
            }

            $field = CategoryField::updateOrCreate(
                [
                    'category_id' => $category->id,
                    'handle'      => $handle,
                ],
                [
                    'name'        => data_get($fieldRaw, 'label', $handle),
                    'type'        => data_get($fieldRaw, 'type', 'text'),
                    'required'    => data_get($fieldRaw, 'required', false),
                    'meta'        => $fieldRaw,
                    'external_id' => data_get($fieldRaw, 'id'),
                ]
            );

            $this->command->info(
                "Saved field: {$field->name} (handle: {$field->handle})"
            );

            $choices = data_get($fieldRaw, 'choices', []);
            if (!is_array($choices) || empty($choices)) {
                continue;
            }

            foreach ($choices as $choice) {
                $value = data_get($choice, 'value');
                if (!$value) {
                    continue;
                }

                CategoryFieldOption::updateOrCreate(
                    [
                        'category_field_id' => $field->id,
                        'value'             => (string) $value,
                    ],
                    [
                        'label' => data_get($choice, 'label', $value),
                        'meta'  => $choice,
                    ]
                );
            }
        }
    }
}
