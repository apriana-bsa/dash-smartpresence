<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AlasanTidakMasuk extends Model
{
    protected $table = 'alasantidakmasuk';
    protected $connection = 'perusahaan_db';
    public $timestamps = false;
}