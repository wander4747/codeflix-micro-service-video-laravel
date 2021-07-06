<?php


namespace Tests\Traits;


trait TestRelations
{
    protected function assertDatabaseHasRelation($table, array $data)
    {
        $this->assertDatabaseHas($table, $data);
    }
}
