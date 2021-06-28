<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lokasi extends Model
{
    protected $table = 'lokasi';
    protected $connection = 'perusahaan_db';
    public $timestamps = false;
}
