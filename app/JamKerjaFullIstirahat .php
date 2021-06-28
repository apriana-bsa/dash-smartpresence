<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JamKerjaFullIstirahat extends Model
{
    protected $table = 'jamkerjafullistirahat';
    protected $connection = 'perusahaan_db';
    public $timestamps = false;
}