<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SlideShow extends Model
{
    protected $table = 'slideshow';
    protected $connection = 'perusahaan_db';
    public $timestamps = false;
}