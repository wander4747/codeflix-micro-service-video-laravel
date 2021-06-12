<?php


namespace App\Traits;
use \Ramsey\Uuid\Uuid as RamseyUuid;

trait Uuid
{
    public static function bootUuid() {
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = RamseyUuid::uuid4()->toString();
        });
    }
}
