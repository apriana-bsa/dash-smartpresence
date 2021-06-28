<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JamKerjaKhusus extends Model
{
    protected $table = 'jamkerjakhusus';
    protected $connection = 'perusahaan_db';
    public $timestamps = false;
}