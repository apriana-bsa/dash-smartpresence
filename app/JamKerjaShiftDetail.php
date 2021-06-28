<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JamKerjaShiftDetail extends Model
{
    protected $table = 'jamkerjashiftdetail';
    protected $connection = 'perusahaan_db';
    public $timestamps = false;
}