<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PegawaiAtributVariable extends Model
{
    protected $table = 'pegawaiatributvariable';
    protected $connection = 'perusahaan_db';
    public $timestamps = false;
}