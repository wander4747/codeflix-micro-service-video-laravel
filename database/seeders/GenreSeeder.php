<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = Category::all();
        $genre = \App\Models\Genre::factory(100)->create();
        $genre->each(function(Genre $genre) use($categories){
            $categoriesId = $categories->random(5)->pluck('id')->toArray();
            $genre->categories()->attach($categoriesId);
        });
    }
}
