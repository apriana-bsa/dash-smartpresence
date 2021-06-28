<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JamKerjaFull extends Model
{
    protected $table = 'jamkerjafull';
    protected $connection = 'perusahaan_db';
    public $timestamps = false;
}