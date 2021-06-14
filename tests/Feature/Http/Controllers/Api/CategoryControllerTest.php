<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndex()
    {
        $category = Category::factory(1)->create();
        $response = $this->get(route('categories.index'));

        $response->assertStatus(200)
            ->assertJson($category->toArray());
    }

    public function testShow()
    {
        $category = Category::create([
            'name' => 'test1'
        ]);

        $response = $this->get(route('categories.show', ['category' => $category->id]));

        $response->assertStatus(200)
            ->assertJson($category->toArray());
    }

    public function testInvalidationData()
    {
        $response = $this->json('POST', route('categories.store'), []);

        $this->assertInvalidationRequired($response);

        $response = $this->json('POST', route('categories.store'), [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]);

        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);

        $category = Category::create([
            'name' => 'test1'
        ]);

        $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), []);

        $this->assertInvalidationRequired($response);

        $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]);

        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);
    }

    protected function assertInvalidationRequired(TestResponse $response)
    {
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                \Lang::get('validation.required', ['attribute' => 'name'])
            ]);
    }

    protected function assertInvalidationMax(TestResponse $response)
    {
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                \Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255])
            ]);
    }

    protected function assertInvalidationBoolean(TestResponse $response)
    {
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['is_active'])
            ->assertJsonFragment([
                \Lang::get('validation.boolean', ['attribute' => 'is active'])
            ]);
    }

    public function testStore()
    {
        $response = $this->json('POST', route('categories.store'), [
            'name' => "test"
        ]);

        $id = $response->json('id');
        $category = Category::find($id);

        $response->assertStatus(201)
            ->assertJson($category->toArray());

        $this->assertTrue($response->json('is_active'));
        $this->assertNull($response->json('description'));

        $response = $this->json('POST', route('categories.store'), [
            'name' => "test",
            'is_active' => false,
            'description' => 'description'
        ]);

        $response->assertJsonFragment([
            'is_active' => false,
            'description' => 'description'
        ]);
    }

    public function testUpdate()
    {
        $category = Category::create([
            'name' => 'test1',
            'is_active' => false,
            'description' => 'description'
        ]);

        $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), [
            'name' => 'test update',
            'is_active' => true,
            'description' => 'description 2'
        ]);

        $id = $response->json('id');
        $category = Category::find($id);

        $response->assertStatus(200)
            ->assertJson($category->toArray())
            ->assertJsonFragment([
                'description' => 'description 2',
                'is_active' => true,
            ]);

        $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), [
            'name' => 'test update',
            'is_active' => true,
            'description' => ''
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'description' => null
            ]);
    }
}
