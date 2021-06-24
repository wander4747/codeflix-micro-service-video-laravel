<?php

namespace Tests\Unit\Model;

use App\Models\CastMember;
use PHPUnit\Framework\TestCase;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class CastMemberTest extends TestCase
{
    private $castMember;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->castMember = new CastMember();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    public function testFillable()
    {
        $fillable = ['name', 'type'];
        $this->assertEquals($fillable, $this->castMember->getFillable());
    }

    public function testIfUseTraits()
    {
        $traits = [
            HasFactory::class,
            SoftDeletes::class,
            Uuid::class,
        ];
        $genreTraits = array_keys(class_uses(CastMember::class));
        $this->assertEquals($traits, $genreTraits);
    }

    public function testCats()
    {
        $casts = ['id' => 'string', 'type' => 'integer','deleted_at' => 'datetime'];
        $this->assertEquals($casts, $this->castMember->getCasts());
    }

    public function testIncrementing()
    {
        $this->assertFalse($this->castMember->incrementing);
    }

    public function testDatesAttributes()
    {
        $dates = ['deleted_at', 'created_at', 'updated_at'];
        foreach ($dates as $date) {
            $this->assertContains($date, $this->castMember->getDates());
        }
        $this->assertCount(count($dates), $this->castMember->getDates());
    }
}
