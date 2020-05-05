<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    public $timestamps = true;
    protected $keyType = 'string';
    protected $fillable = ['*'];
}
