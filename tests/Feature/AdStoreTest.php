<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\CategoryField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class AdStoreTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_create_ad_successfully()
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create();

        CategoryField::create([
            'category_id' => $category->id,
            'name'        => 'Price',
            'handle'      => 'Price',
            'type'        => 'number',
            'required'    => true,
        ]);

        CategoryField::create([
            'category_id' => $category->id,
            'name'        => 'Agent Code',
            'handle'      => 'Agent Code',
            'type'        => 'text',
            'required'    => false,
        ]);

        $payload = [
            'title'       => 'Toyota Corolla 2018',
            'description' => 'Very clean car',
            'price'       => 1200,
            'category_id' => $category->id,
            'dynamic_fields' => [
                'Price'      => 8500,
                'Agent Code' => 'AG-7788',
            ],
        ];

        // Act
        $response = $this->postJson('/api/v1/ads', $payload);

        // Assert
        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => 1,
                'error'  => false,
                'message'=> 'Ad created successfully',
            ]);

        $this->assertDatabaseHas('ads', [
            'title' => 'Toyota Corolla 2018',
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function it_fails_when_required_dynamic_field_is_missing()
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create();

        CategoryField::create([
            'category_id' => $category->id,
            'name'        => 'Price',
            'handle'      => 'Price',
            'type'        => 'number',
            'required'    => true,
        ]);

        $payload = [
            'title'       => 'Toyota Corolla',
            'description' => 'Nice car',
            'price'       => 1000,
            'category_id' => $category->id,
            'dynamic_fields' => [
                // ❌ Missing required "Price"
            ],
        ];

        // Act
        $response = $this->postJson('/api/v1/ads', $payload);

        // Assert
        $response
            ->assertStatus(422)
            ->assertJson([
                'status' => 0,
                'error'  => true,
                'message'=> 'Validation failed',
            ])
            ->assertJsonStructure([
                'errors' => [
                    'dynamic_fields.Price',
                ],
            ]);
    }
}
