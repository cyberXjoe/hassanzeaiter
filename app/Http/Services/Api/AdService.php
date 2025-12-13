<?php

namespace App\Http\Services\Api;

use App\Models\Ad;
use App\Models\AdFieldValue;
use App\Models\CategoryField;

class AdService
{
    /**
     * Create an Ad with dynamic fields.
     */
    public function createAd($user, $data)
    {
        $ad = Ad::create([
            'user_id' => $user->id,
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'price' => $data['price'],
        ]);

        $dynamicFields = $data['dynamic_fields'] ?? [];
        foreach ($dynamicFields as $fieldHandle => $value) {
            $categoryField = CategoryField::where('category_id', $ad->category_id)
                                ->where('handle', $fieldHandle)
                                ->first();
            if (!$categoryField) continue;

            AdFieldValue::create([
                'ad_id' => $ad->id,
                'category_field_id' => $categoryField->id,
                'value' => $value,
            ]);
        }

        return $ad->load('fieldValues.categoryField', 'category');
    }

    /**
     * Get ads for a specific user (paginated)
     */
    public function getUserAds($user, $perPage = 10)
    {
        return Ad::with('fieldValues.categoryField', 'category')
                ->where('user_id', $user->id)
                ->latest()
                ->paginate($perPage);
    }

    /**
     * Get a single Ad by ID
     */
    public function getAdById($id)
    {
        return Ad::with('fieldValues.categoryField', 'category')->find($id);
    }
}
