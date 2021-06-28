<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IjinTidakMasuk extends Model
{
    protected $table = 'ijintidakmasuk';
    protected $connection = 'perusahaan_db';
    public $timestamps = false;
}