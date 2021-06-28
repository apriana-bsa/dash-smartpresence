<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Atribut extends Model
{
    protected $table = 'atribut';
    protected $connection = 'perusahaan_db';
    public $timestamps = false;
}