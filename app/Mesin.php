<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mesin extends Model
{
    protected $table = 'mesin';
    protected $connection = 'perusahaan_db';
    public $timestamps = false;
}