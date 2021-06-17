<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndex()
    {
        $genre = Genre::factory(1)->create();
        $response = $this->get(route('genres.index'));

        $response->assertStatus(200)
            ->assertJson($genre->toArray());
    }

    public function testShow()
    {
        $genre = Genre::create([
            'name' => 'test1'
        ]);

        $response = $this->get(route('genres.show', ['genre' => $genre->id]));

        $response->assertStatus(200)
            ->assertJson($genre->toArray());
    }

    public function testInvalidationData()
    {
        $response = $this->json('POST', route('genres.store'), []);

        $this->assertInvalidationRequired($response);

        $response = $this->json('POST', route('genres.store'), [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]);

        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);

        $genre = Genre::create([
            'name' => 'test1'
        ]);

        $response = $this->json('PUT', route('genres.update', ['genre' => $genre->id]), []);

        $this->assertInvalidationRequired($response);

        $response = $this->json('PUT', route('genres.update', ['genre' => $genre->id]), [
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
        $response = $this->json('POST', route('genres.store'), [
            'name' => "test"
        ]);

        $id = $response->json('id');
        $genre = Genre::find($id);

        $response->assertStatus(201)
            ->assertJson($genre->toArray());

        $this->assertTrue($response->json('is_active'));

        $response = $this->json('POST', route('genres.store'), [
            'name' => "test",
            'is_active' => false,
        ]);

        $response->assertJsonFragment([
            'is_active' => false
        ]);
    }

    public function testUpdate()
    {
        $genre = Genre::create([
            'name' => 'test1',
            'is_active' => false,
        ]);

        $response = $this->json('PUT', route('genres.update', ['genre' => $genre->id]), [
            'name' => 'test update',
            'is_active' => true,
        ]);

        $id = $response->json('id');
        $genre = Genre::find($id);

        $response->assertStatus(200)
            ->assertJson($genre->toArray())
            ->assertJsonFragment([
                'is_active' => true,
            ]);

        $response = $this->json('PUT', route('genres.update', ['genre' => $genre->id]), [
            'name' => 'test update',
            'is_active' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'test update'
            ]);
    }

    public function testDelete()
    {
        $genre = Genre::create([
            'name' => 'test delete',
            'is_active' => false,
        ]);

        $response = $this->json('DELETE', route('genres.destroy', ['genre' => $genre->id]), []);

        $response->assertStatus(204);
    }
}
