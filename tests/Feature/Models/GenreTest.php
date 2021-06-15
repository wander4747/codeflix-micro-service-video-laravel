<?php

namespace Tests\Feature\Models;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        Genre::factory(1)->create();
        $categories = Genre::all();
        $this->assertCount(1, $categories);

        $categoryKey = array_keys($categories->first()->getAttributes());
        $this->assertEqualsCanonicalizing([
            'id', 'name', 'is_active', 'created_at', 'updated_at', 'deleted_at'
        ], $categoryKey);
    }

    public function testCreate()
    {
        $genre = Genre::create([
            'name' => 'test1'
        ]);
        $genre->refresh();

        $this->assertEquals(36, strlen($genre->id));
        $this->assertEquals('test1', $genre->name);
        $this->assertTrue((bool) $genre->is_active);

        $genre = Genre::create([
            'name' => 'test1',
            'is_active' => false
        ]);
        $this->assertFalse($genre->is_active);

        $genre = Genre::create([
            'name' => 'test1',
            'is_active' => true
        ]);
        $this->assertTrue($genre->is_active);
        $this->assertMatchesRegularExpression('/[a-f0-9]{8}\-[a-f0-9]{4}\-[a-f0-9]{4}\-[a-f0-9]{4}-[a-f0-9]{12}/', $genre->id);
    }

    public function testUpdate()
    {
        $genre = Genre::factory([
            'name' => 'test_name',
            'is_active' => false
        ])->create();

        $data = [
            'name' => 'test_name_updated',
            'is_active' => true
        ];

        $genre->update($data);

        foreach($data as $key => $value){
            $this->assertEquals($value, $genre->{$key});
        }
    }

    public function testDelete(){
        $genre =  Genre::factory(1)->create()->first();
        $genre->delete();
        $this->assertNull(Genre::find($genre->id));

        $genre->restore();
        $this->assertNotNull(Genre::find($genre->id));
    }
}
