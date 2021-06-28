<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PegawaiAtribut extends Model
{
    protected $table = 'pegawaiatribut';
    protected $connection = 'perusahaan_db';
    public $timestamps = false;
}