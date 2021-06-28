<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AtributNilai extends Model
{
    protected $table = 'atributnilai';
    protected $connection = 'perusahaan_db';
    
    public $timestamps = false;
}