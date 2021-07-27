<?php


namespace Unit\Model;


use App\Models\Video;
use App\Traits\UploadFiles;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use PHPUnit\Framework\TestCase;

class VideoTest  extends TestCase
{

    private $video;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->video = new Video();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    public function testFillable()
    {
        $fillable = [
            'title',
            'description',
            'year_launched',
            'opened',
            'rating',
            'duration',
            'video_file',
            'thumb_file',
            'banner_file',
            'trailer_file'
        ];
        $this->assertEquals($fillable, $this->video->getFillable());
    }

    public function testIfUseTraits()
    {
        $traits = [
            HasFactory::class,
            SoftDeletes::class,
            Uuid::class,
            UploadFiles::class
        ];
        $videoTraits = array_keys(class_uses(Video::class));
        $this->assertEquals($traits, $videoTraits);
    }

    public function testCats()
    {
        $casts = [
            'id' => 'string',
            'opened' => 'boolean',
            'year_launched' => 'integer',
            'duration' => 'integer',
            'deleted_at' => 'datetime'
        ];
        $this->assertEquals($casts, $this->video->getCasts());
    }

    public function testIncrementing()
    {
        $this->assertFalse($this->video->incrementing);
    }

    public function testDatesAttributes()
    {
        $dates = ['deleted_at', 'created_at', 'updated_at'];
        foreach ($dates as $date) {
            $this->assertContains($date, $this->video->getDates());
        }
        $this->assertCount(count($dates), $this->video->getDates());
    }
}
