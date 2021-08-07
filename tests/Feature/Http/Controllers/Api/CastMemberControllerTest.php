<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Resources\CastMemberResource;
use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves, TestResources;

    private $castMember;

    private $serializedFields = [
        'id',
        'name',
        'type',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->castMember = CastMember::factory()->create();
    }

    public function testIndex()
    {
        $response = $this->get(route('cast_members.index'));

        $response
            ->assertStatus(200)
            ->assertJsonStructure(
                [
                    'data' => [
                        '*' => $this->serializedFields
                    ],
                    'meta' => [],
                    'links' => []
                ]
            )
            ->assertJsonFragment($this->castMember->toArray());
    }

    public function testShow()
    {
        $response = $this->get(route('cast_members.show', ['cast_member' => $this->castMember->id]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->serializedFields
            ]);

        $this->assertResource($response, new CastMemberResource($this->castMember));
    }

    public function testInvalidationData()
    {
        $data = [
            'name' => '',
            'type' => ''
        ];
        $this->assertInvalidationStoreAction($data, 'required');
        $this->assertInvalidationUpdateAction($data, 'required');

        $data = [
            'type' => '123',
        ];
        $this->assertInvalidationStoreAction($data, 'in');
        $this->assertInvalidationupdateAction($data, 'in');
    }

    public function testStore()
    {
        $data = ['name' => 'teste', 'type' => CastMember::TYPE_ACTOR];
        $response = $this->assertStore($data, $data + ['type' => CastMember::TYPE_ACTOR, 'deleted_at' => null]);
        $response->assertJsonStructure(['data' => $this->serializedFields]);

        $data = ['name' => 'teste', 'type' => CastMember::TYPE_DIRECTOR];
        $this->assertStore($data, $data + ['type' => CastMember::TYPE_DIRECTOR, 'deleted_at' => null]);

        $id = $response->json('data.id');
        $resource = new CastMemberResource(CastMember::find($id));
        $this->assertResource($response, $resource);
    }

    public function testUpdate()
    {
        $this->castMember = CastMember::create([
            'name' => 'test1',
            'type' => CastMember::TYPE_ACTOR
        ]);

        $data = [
            'name' => 'test update',
            'type' => CastMember::TYPE_DIRECTOR
        ];
        $response = $this->assertUpdate(
            $data,
            $data + ['deleted_at' => null]);
        $response->assertJsonStructure(['data' => $this->serializedFields]);

        $id = $response->json('data.id');
        $resource = new CastMemberResource(CastMember::find($id));
        $this->assertResource($response, $resource);
    }

    public function testDelete()
    {
        $response = $this->json('DELETE', route('cast_members.destroy', ['cast_member' => $this->castMember->id]), []);
        $response->assertStatus(204);
    }

    protected function model()
    {
        return CastMember::class;
    }

    protected function routeStore()
    {
        return route('cast_members.store');
    }

    protected function routeUpdate()
    {
        return route('cast_members.update', ['cast_member' => $this->castMember->id]);
    }
}
