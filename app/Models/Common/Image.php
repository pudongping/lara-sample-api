<?php

namespace App\Models\Common;

use App\Models\Model;

class Image extends Model
{

    protected $fillable = ['user_id', 'type', 'path'];

}
