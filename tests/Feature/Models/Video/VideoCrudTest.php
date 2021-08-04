<?php


namespace Tests\Feature\Models\Video;


use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;

class VideoCrudTest extends BaseVideoTest
{

    private $fileFieldsData = [];

    protected function setUp(): void
    {
        parent::setUp();
        foreach (Video::$fileFields as $field) {
            $this->fileFieldsData[$field] = "$field.test";
        }
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
            'rating' => Video::ratingList()[0],
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
            'rating' => Video::ratingList()[0],
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
            'rating' => Video::ratingList()[0],
            'duration' => 90,
            'categories_id' => [$this->category->id],
            'genres_id' => [$this->genre->id],
        ]);
        $video->delete();
        $this->assertNull(Video::find($video->id));

        $video->restore();
        $this->assertNotNull(Video::find($video->id));
    }

    public function testCreateWithBasicFields()
    {
        $video = Video::factory()->create( ['opened' => false]);
        $video->update($this->videoData);
        $this->assertFalse($video->opened);
        $this->assertDatabaseHas('videos',
            $this->videoData + ['opened' => false]
        );
    }

    public function testUpdateWithBasicFields()
    {
        $video = Video::factory()->create( ['opened' => false]);
        $video->update($this->videoData + $this->fileFieldsData);
        $this->assertFalse($video->opened);
        $this->assertDatabaseHas('videos',
            $this->videoData + $this->fileFieldsData + ['opened' => false]
        );

        $video = Video::factory()->create( ['opened' => false]);
        $video->update($this->videoData + $this->fileFieldsData + ['opened' => true]);
        $this->assertTrue($video->opened);
        $this->assertDatabaseHas('videos', $this->videoData + ['opened' => true]);
    }

    public function testCreateWithRelations()
    {
        $video = Video::create($this->videoData + [
                'categories_id' => [$this->category->id],
                'genres_id' => [$this->genre->id],
            ]
        );

        $this->assertHasCategory($video->id, $this->category->id);
        $this->assertHasGenre($video->id, $this->genre->id);
    }

    protected function assertHasCategory($videoId, $categoryId)
    {
        $this->assertDatabaseHas('category_video', [
            'video_id' => $videoId,
            'category_id' => $categoryId
        ]);
    }

    protected function assertHasGenre($videoId, $genreId)
    {
        $this->assertDatabaseHas('genre_video', [
            'video_id' => $videoId,
            'genre_id' => $genreId
        ]);
    }

    public function testSyncCategories()
    {
        $categoriesId = Category::factory(3)->create()->pluck('id')->toArray();
        $video = Video::factory()->create();

        Video::handleRelations($video, [
            'categories_id' => [$categoriesId[0]]
        ]);

        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[0],
            'video_id' => $video->id
        ]);

        Video::handleRelations($video, [
            'categories_id' => [$categoriesId[1], $categoriesId[2]]
        ]);
        $this->assertDatabaseMissing('category_video', [
            'category_id' => $categoriesId[0],
            'video_id' => $video->id
        ]);
        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[1],
            'video_id' => $video->id
        ]);
        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[2],
            'video_id' => $video->id
        ]);
    }

    public function testSyncGenres()
    {
        $genresId = Genre::factory(3)->create()->pluck('id')->toArray();
        $video = Video::factory()->create();

        Video::handleRelations($video, [
            'genres_id' => [$genresId[0]]
        ]);
        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresId[0],
            'video_id' => $video->id
        ]);

        Video::handleRelations($video, [
            'genres_id' => [$genresId[1], $genresId[2]]
        ]);
        $this->assertDatabaseMissing('genre_video', [
            'genre_id' => $genresId[0],
            'video_id' => $video->id
        ]);
        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresId[1],
            'video_id' => $video->id
        ]);
        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresId[2],
            'video_id' => $video->id
        ]);
    }
}
