<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PegawaiLokasi extends Model
{
    protected $table = 'pegawailokasi';
    protected $connection = 'perusahaan_db';
    public $timestamps = false;
}