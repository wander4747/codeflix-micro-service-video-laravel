<?php

namespace Tests\Feature\Models;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CastMemberTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        CastMember::factory(1)->create();
        $castMember = CastMember::all();
        $this->assertCount(1, $castMember);

        $castMemberKey = array_keys($castMember->first()->getAttributes());
        $this->assertEqualsCanonicalizing([
            'id', 'name', 'type', 'created_at', 'updated_at', 'deleted_at'
        ], $castMemberKey);
    }

    public function testCreate()
    {
        $castMember = CastMember::create([
            'name' => 'test1',
            'type' => CastMember::TYPE_DIRECTOR
        ]);
        $castMember->refresh();

        $this->assertEquals(36, strlen($castMember->id));
        $this->assertEquals('test1', $castMember->name);

        $castMember = CastMember::create([
            'name' => 'test1',
            'type' => CastMember::TYPE_DIRECTOR
        ]);
        $this->assertMatchesRegularExpression('/[a-f0-9]{8}\-[a-f0-9]{4}\-[a-f0-9]{4}\-[a-f0-9]{4}-[a-f0-9]{12}/', $castMember->id);
    }

    public function testUpdate()
    {
        $castMember = CastMember::factory([
            'name' => 'test_name',
            'type' => CastMember::TYPE_DIRECTOR
        ])->create();

        $data = [
            'name' => 'test_name_updated',
            'type' => CastMember::TYPE_ACTOR
        ];

        $castMember->update($data);

        foreach($data as $key => $value){
            $this->assertEquals($value, $castMember->{$key});
        }
    }

    public function testDelete(){
        $castMember =  CastMember::factory(1)->create()->first();
        $castMember->delete();
        $this->assertNull(CastMember::find($castMember->id));

        $castMember->restore();
        $this->assertNotNull(CastMember::find($castMember->id));
    }
}
