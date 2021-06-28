<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MesinAtribut extends Model
{
    protected $table = 'mesinatribut';
    protected $connection = 'perusahaan_db';
    public $timestamps = false;
}