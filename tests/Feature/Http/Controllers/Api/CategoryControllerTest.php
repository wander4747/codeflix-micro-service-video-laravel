<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = Category::factory()->create();
    }

    public function testIndex()
    {
        $response = $this->get(route('categories.index'));

        $response->assertStatus(200)
            ->assertJson([$this->category->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('categories.show', ['category' => $this->category->id]));

        $response->assertStatus(200)
            ->assertJson($this->category->toArray());
    }

    public function testInvalidationData()
    {
        $data = [
            'name' => ''
        ];
        $this->assertInvalidationStoreAction($data, 'required');
        $this->assertInvalidationUpdateAction($data, 'required');

        $data = [
            'name' => str_repeat('a', 256),
        ];
        $this->assertInvalidationStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationupdateAction($data, 'max.string', ['max' => 255]);

        $data = [
            'is_active' => 'a'
        ];
        $this->assertInvalidationStoreAction($data, 'boolean');
        $this->assertInvalidationUpdateAction($data, 'boolean');
    }

    public function testStore()
    {
        $data = ['name' => 'teste'];
        $response = $this->assertStore($data, $data + ['description' => null, 'is_active' => true, 'deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);

        $data = [
            'name' => "test",
            'is_active' => false,
            'description' => 'description'
        ];
        $this->assertStore($data, $data + ['description' => 'description', 'is_active' => false, 'deleted_at' => null]);
    }

    public function testUpdate()
    {
        $this->category = Category::create([
            'name' => 'test1',
            'is_active' => false,
            'description' => 'description'
        ]);

        $data = [
            'name' => 'test update',
            'is_active' => true,
            'description' => 'description 2'
        ];
        $response = $this->assertUpdate(
            $data,
            $data + ['deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);

        $data = [
            'name' => 'test update',
            'is_active' => true,
            'description' => ''
        ];
        $this->assertUpdate(
            $data,
            array_merge($data, ['description' => null]));

        $data = [
            'name' => 'test update',
            'is_active' => true,
            'description' => 'test'
        ];
        $this->assertUpdate(
            $data,
            array_merge($data, ['description' => 'test']));
    }

    public function testDelete()
    {
        $response = $this->json('DELETE', route('categories.destroy', ['category' => $this->category->id]), []);
        $response->assertStatus(204);
    }

    protected function routeStore()
    {
        return route('categories.store');
    }

    protected function routeUpdate()
    {
        return route('categories.update', ['category' => $this->category->id]);
    }

    protected function model()
    {
        return Category::class;
    }
}
