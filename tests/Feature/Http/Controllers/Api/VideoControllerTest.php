<?php


namespace Feature\Http\Controllers\Api;


use App\Http\Resources\VideoResource;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Tests\Traits\{TestRelations, TestResources, TestSaves, TestValidations, TestUploads};

class VideoControllerTest extends TestCase
{

    use DatabaseMigrations, TestValidations, TestSaves, TestRelations, TestUploads, TestResources;

    private $video;
    private $category;
    private $genre;
    private $sendData;
    private $serializedFields = [
        'id',
        'title',
        'description',
        'year_launched',
        'rating',
        'duration',
        'rating',
        'opened',
        'thumb_file_url',
        'banner_file_url',
        'video_file_url',
        'trailer_file_url',
        'created_at',
        'updated_at',
        'deleted_at',
        'categories' => [
            '*' => [
                'id',
                'name',
                'description',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at'
            ]
        ],
        'genres' => [
            '*' => [
                'id',
                'name',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at',
            ]
        ]
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->video = Video::factory()->create();
        $this->category = Category::factory()->create();
        $this->genre = Genre::factory()->create();
        $this->genre->categories()->sync($this->category);
        $this->sendData = [
            'title' => 'title',
            'description' => 'description',
            'year_launched' => 2021,
            'rating' => 'L',
            'duration' => 10,
        ];
    }

    public function testIndex()
    {
        $response = $this->get(route('videos.index'));

        $response->assertStatus(200)
            ->assertJson([
                'meta' => ['per_page' => 15]
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => $this->serializedFields
                ],
                'links' => [],
                'meta' => [],
            ]);

        $resource = VideoResource::collection(collect([$this->video]));
        $this->assertResource($response, $resource);
    }

    public function testShow()
    {
        $response = $this->get(route('videos.show', ['video' => $this->video->id]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->serializedFields
            ]);

        $id = $response->json('data.id');
        $resource = new VideoResource(Video::find($id));
        $this->assertResource($response, $resource);
    }

    public function testInvalidationRequired()
    {
        $data = [
            'title' => '',
            'description' => '',
            'year_launched' => '',
            'rating' => '',
            'duration' => '',
            'categories_id' => '',
            'genres_id' => '',
        ];
        $this->assertInvalidationStoreAction($data, 'required');
        $this->assertInvalidationUpdateAction($data, 'required');
    }

    public function testInvalidationMax()
    {
        $data = [
            'title' => str_repeat('a', 256)
        ];
        $this->assertInvalidationStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationUpdateAction($data, 'max.string', ['max' => 255]);
    }

    public function testInvalidationInteger()
    {
        $data = [
            'duration' => 's'
        ];
        $this->assertInvalidationStoreAction($data, 'integer');
        $this->assertInvalidationUpdateAction($data, 'integer');
    }

    public function testInvalidationYearLaunchedField()
    {
        $data = [
            'year_launched' => 'a'
        ];
        $this->assertInvalidationStoreAction($data, 'date_format', ['format' => 'Y']);
        $this->assertInvalidationUpdateAction($data, 'date_format', ['format' => 'Y']);
    }

    public function testInvalidationOpenedField()
    {
        $data = [
            'opened' => 's'
        ];
        $this->assertInvalidationStoreAction($data, 'boolean');
        $this->assertInvalidationUpdateAction($data, 'boolean');
    }

    public function testInvalidationRatingField()
    {
        $data = [
            'rating' => 0
        ];
        $this->assertInvalidationStoreAction($data, 'in');
        $this->assertInvalidationUpdateAction($data, 'in');
    }

    public function testInvalidationCategoriesIdField()
    {
        $data = [
            'categories_id' => 'a'
        ];
        $this->assertInvalidationStoreAction($data, 'array');
        $this->assertInvalidationUpdateAction($data, 'array');

        $data = [
            'categories_id' => [100]
        ];
        $this->assertInvalidationStoreAction($data, 'exists');
        $this->assertInvalidationUpdateAction($data, 'exists');
    }

    public function testInvalidationGenresIdField()
    {
        $data = [
            'genres_id' => 'a'
        ];
        $this->assertInvalidationStoreAction($data, 'array');
        $this->assertInvalidationUpdateAction($data, 'array');

        $data = [
            'genres_id' => [100]
        ];
        $this->assertInvalidationStoreAction($data, 'exists');
        $this->assertInvalidationUpdateAction($data, 'exists');
    }

    public function testStore()
    {
        $response = $this->assertStore($this->sendData + [
            'categories_id' => [$this->category->id], 'genres_id' => [$this->genre->id]
            ], $this->sendData + ['deleted_at' => null]);
        $response->assertJsonStructure(['data' => $this->serializedFields]);

        $this->assertDatabaseHasRelation('category_video',
            ['video_id' => $response->json('data.id'), 'category_id' => $this->category->id]);
    }

    public function testUpdate()
    {
        $response = $this->assertUpdate($this->sendData +
            ['opened' => true, 'categories_id' => [$this->category->id], 'genres_id' => [$this->genre->id]],
            $this->sendData + ['opened' => true]);
        $response->assertJsonStructure(['data' => $this->serializedFields]);
        $this->assertDatabaseHasRelation('genre_video',
            ['video_id' => $response->json('data.id'), 'genre_id' => $this->genre->id]);
    }

    public function testDestroy()
    {
        $response = $this->json(
            'DELETE', route('videos.destroy', ['video' => $this->video->id])
        );
        $response->assertStatus(204);
        $this->assertNull(Video::find($this->video->id));
    }

    public function testInvalidationVideoField()
    {
        $this->assertInvalidationFile(
            'video_file',
            'mp4',
            Video::MAX_VIDEO_SIZE,
            'mimetypes', ['values' => 'video/mp4']
        );
    }

    public function testInvalidationTrailerField()
    {
        $this->assertInvalidationFile(
            'trailer_file',
            'mp4',
            Video::MAX_TRAILER_SIZE,
            'mimetypes', ['values' => 'video/mp4']
        );
    }

    public function testInvalidationThumbField()
    {
        $this->assertInvalidationFile(
            'thumb_file',
            'jpg',
            Video::MAX_THUMB_SIZE,
            'image');
    }

    public function testInvalidationBannerField()
    {
        $this->assertInvalidationFile(
            'banner_file',
            'jpg',
            Video::MAX_BANNER_SIZE,
            'image'
        );
    }

    public function testCreateWithFiles()
    {
        \Storage::fake();
        $video = UploadedFile::fake()->create("video_file.mp4");
        $trailer = UploadedFile::fake()->create("trailer_file.mp4");
        $banner = UploadedFile::fake()->create("banner_file.jpg");
        $thumb = UploadedFile::fake()->create("thumb_file.jpg");

        $fields = [
            'video_file' => $video,
            'thumb_file' => $thumb,
            'banner_file' => $banner,
            'trailer_file' => $trailer,
            'categories_id' => [$this->category->id],
            'genres_id' => [$this->genre->id]
        ];
        $response = $this->json(
            'POST', $this->routeStore(), $this->sendData + $fields
        );
        $response->assertStatus(201);

        $id = $response->json('data.id');
        \Storage::assertExists("{$id}/{$video->hashName()}");
        \Storage::assertExists("{$id}/{$thumb->hashName()}");
        \Storage::assertExists("{$id}/{$banner->hashName()}");
        \Storage::assertExists("{$id}/{$trailer->hashName()}");
    }

    public function testUpdateWithFiles()
    {
        \Storage::fake();
        $video = UploadedFile::fake()->create("video_file.mp4");
        $trailer = UploadedFile::fake()->create("trailer_file.mp4");
        $banner = UploadedFile::fake()->create("banner_file.jpg");
        $thumb = UploadedFile::fake()->create("thumb_file.jpg");

        $fields = [
            'video_file' => $video,
            'thumb_file' => $thumb,
            'banner_file' => $banner,
            'trailer_file' => $trailer,
            'categories_id' => [$this->category->id],
            'genres_id' => [$this->genre->id]
        ];

        $response = $this->json(
            'PUT', $this->routeUpdate(), $this->sendData + $fields
        );
        $response->assertStatus(200);



        $response = $this->json(
            'PUT', $this->routeUpdate(), $this->sendData + $fields
        );

        $response->assertStatus(200);
        $id = $response->json('data.id');

        \Storage::assertExists("{$id}/{$video->hashName()}");
        \Storage::assertExists("{$id}/{$thumb->hashName()}");
        \Storage::assertExists("{$id}/{$banner->hashName()}");
        \Storage::assertExists("{$id}/{$trailer->hashName()}");
    }


    protected function routeStore()
    {
        return route('videos.store');
    }

    protected function routeUpdate()
    {
        return route('videos.update', ['video' => $this->video->id]);
    }

    protected function model()
    {
        return Video::class;
    }
}
