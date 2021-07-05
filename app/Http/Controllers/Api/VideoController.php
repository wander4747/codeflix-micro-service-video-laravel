<?php

namespace App\Http\Controllers\Api;

use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends BasicCrudController
{

    protected function model()
    {
        return Video::class;
    }

    protected function rulesStore()
    {
        // TODO: Implement rulesStore() method.
    }

    protected function rulesUpdate()
    {
        // TODO: Implement rulesUpdate() method.
    }
}
