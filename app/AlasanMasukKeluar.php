<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AlasanMasukKeluar extends Model
{
    protected $table = 'alasanmasukkeluar';
    protected $connection = 'perusahaan_db';
    public $timestamps = false;
}