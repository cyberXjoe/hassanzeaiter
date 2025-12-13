<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'price'       => $this->price,
            'category_id' => $this->category_id,
            'category'    => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'dynamic_fields' => $this->fieldValues->mapWithKeys(function ($fieldValue) {
                return [$fieldValue->categoryField->handle => $fieldValue->value];
            }),
            'created_at'  => $this->created_at->toDateTimeString(),
            'updated_at'  => $this->updated_at->toDateTimeString(),
        ];
    }
}
