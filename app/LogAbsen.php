<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogAbsen extends Model
{
    protected $table = 'logabsen';
    protected $connection = 'perusahaan_db';
    public $timestamps = false;
}