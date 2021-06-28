<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HariLiburAtribut extends Model
{
    protected $table = 'hariliburatribut';
    protected $connection = 'perusahaan_db';
    public $timestamps = false;
}