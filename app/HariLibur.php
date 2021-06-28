<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HariLibur extends Model
{
    protected $table = 'harilibur';
    protected $connection = 'perusahaan_db';
    public $timestamps = false;
}