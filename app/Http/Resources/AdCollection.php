<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AdCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->transform(function ($ad) {
                return [
                    'id'          => $ad->id,
                    'title'       => $ad->title,
                    'price'       => $ad->price,
                    'category'    => $ad->category ? $ad->category->name : null,
                    'dynamic_fields' => $ad->fieldValues->mapWithKeys(function ($fieldValue) {
                        return [$fieldValue->categoryField->handle => $fieldValue->value];
                    }),
                    'created_at'  => $ad->created_at->toDateTimeString(),
                ];
            }),
        ];
    }
}
