<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AtributVariable extends Model
{
    protected $table = 'atributvariable';
    protected $connection = 'perusahaan_db';
    public $timestamps = false;
}