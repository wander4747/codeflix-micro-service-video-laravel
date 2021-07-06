<?php


namespace Feature\Models;


use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class VideoTest  extends TestCase
{
    use DatabaseMigrations;

    private $category;
    private $genre;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = Category::factory()->create();
        $this->genre = Genre::factory()->create();
    }
    public function testList()
    {
        Video::factory(1)->create();
        $videos = Video::all();
        $this->assertCount(1, $videos);
    }

    public function testCreate()
    {
        $video = Video::create([
            'title' => 'title',
            'description' => 'description',
            'year_launched' => 2021,
            'rating' => 'L',
            'duration' => 90,
            'categories_id' => [$this->category->id],
            'genres_id' => [$this->genre->id],
        ]);

        $video->refresh();

        $this->assertEquals(36, strlen($video->id));
        $this->assertEquals('title', $video->title);
    }

    public function testUpdate()
    {
        $video = Video::create([
            'title' => 'title',
            'description' => 'description',
            'year_launched' => 2021,
            'rating' => 'L',
            'duration' => 90,
            'categories_id' => [$this->category->id],
            'genres_id' => [$this->genre->id],
        ]);

        $data = [
            'title' => 'title_updated',
            'description' => 'test_description_updated',
        ];

        $video->update($data);

        foreach($data as $key => $value){
            $this->assertEquals($value, $video->{$key});
        }
    }

    public function testDelete(){
        $video = Video::create([
            'title' => 'title',
            'description' => 'description',
            'year_launched' => 2021,
            'rating' => 'L',
            'duration' => 90,
            'categories_id' => [$this->category->id],
            'genres_id' => [$this->genre->id],
        ]);
        $video->delete();
        $this->assertNull(Video::find($video->id));

        $video->restore();
        $this->assertNotNull(Video::find($video->id));
    }
}
