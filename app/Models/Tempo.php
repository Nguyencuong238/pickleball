<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Tempo extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'session_id',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('default');
    }
}
