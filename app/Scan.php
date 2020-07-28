<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Scan extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'last_scan_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'last_scan_at' => 'datetime',
     ];
}
