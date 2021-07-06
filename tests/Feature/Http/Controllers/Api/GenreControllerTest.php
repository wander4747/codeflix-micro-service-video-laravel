<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestRelations;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves, TestRelations;

    private $genre;
    private $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->genre = Genre::factory()->create();
        $this->category = Category::factory()->create();
    }

    public function testIndex()
    {
        $response = $this->get(route('genres.index'));

        $response->assertStatus(200)
            ->assertJson([$this->genre->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('genres.show', ['genre' => $this->genre->id]));

        $response->assertStatus(200)
            ->assertJson($this->genre->toArray());
    }

    public function testInvalidationData()
    {
        $data = [
            'name' => '',
            'categories_id' => ''
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

        $data = [
            'categories_id' => [rand(0,100), chr(rand(97,122))]
        ];
        $this->assertInvalidationStoreAction($data, 'exists');
        $this->assertInvalidationUpdateAction($data, 'exists');

        $data = [
            'categories_id' => 'a'
        ];
        $this->assertInvalidationStoreAction($data, 'array');
        $this->assertInvalidationUpdateAction($data, 'array');
    }

    public function testStore()
    {
        $data = ['name' => 'teste'];
        $response = $this->assertStore($data + ['categories_id' => [$this->category->id]],
            $data + ['is_active' => true, 'deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);

        $this->assertDatabaseHasRelation('category_genre',
            ['genre_id' => $response->json('id'), 'category_id' => $this->category->id]);
        $data = [
            'name' => "test",
            'is_active' => false,
        ];
        $this->assertStore($data + ['categories_id' => [$this->category->id]],
                $data + ['is_active' => false, 'deleted_at' => null]);
    }

    public function testUpdate()
    {
        $data = [
            'name' => 'test update',
            'is_active' => true,
        ];
        $response = $this->assertUpdate(
            $data + ['categories_id' => [$this->category->id]],
            $data + ['deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);
        $this->assertDatabaseHasRelation('category_genre',
            ['genre_id' => $response->json('id'), 'category_id' => $this->category->id]);
    }

    public function testDelete()
    {
        $response = $this->json('DELETE', route('genres.destroy', ['genre' => $this->genre->id]), []);
        $response->assertStatus(204);
    }

    protected function model()
    {
        return Genre::class;
    }

    protected function routeStore()
    {
        return route('genres.store');
    }

    protected function routeUpdate()
    {
        return route('genres.update', ['genre' => $this->genre->id]);
    }
}
