<?php


namespace Tests\Feature\Models\Video;


use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

abstract class BaseVideoTest extends TestCase
{

    use DatabaseMigrations;

    protected $category;
    protected $genre;
    protected $videoData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = Category::factory()->create();
        $this->genre = Genre::factory()->create();
        $this->videoData = [
            'title' => 'title',
            'description' => 'description',
            'year_launched' => 2021,
            'rating' => Video::ratingList()[0],
            'duration' => 90,
        ];
    }
}
