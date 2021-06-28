<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JamKerja extends Model
{
    protected $table = 'jamkerja';
    protected $connection = 'perusahaan_db';
    public $timestamps = false;
}