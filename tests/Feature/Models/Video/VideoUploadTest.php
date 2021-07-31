<?php


namespace Tests\Feature\Models\Video;


use App\Models\Video;
use Illuminate\Http\UploadedFile;

class VideoUploadTest extends BaseVideoTest
{
    public function testCreateWithFiles()
    {
        \Storage::fake();
        $video = Video::create(
            $this->videoData + [
                'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
                'video_file' => UploadedFile::fake()->image('video.mp4'),
            ]
        );
        \Storage::assertExists("{$video->id}/{$video->thumb_file}");
        \Storage::assertExists("{$video->id}/{$video->video_file}");
    }
}
