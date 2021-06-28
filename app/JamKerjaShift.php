<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JamKerjaShift extends Model
{
    protected $table = 'jamkerjashift';
    protected $connection = 'perusahaan_db';
    public $timestamps = false;
}