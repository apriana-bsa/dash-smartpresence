<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JadwalShift extends Model
{
    protected $table = 'jadwalshift';
    protected $connection = 'perusahaan_db';
    public $timestamps = false;
}