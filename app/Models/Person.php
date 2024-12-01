<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;

    protected $primaryKey = 'emso';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'emso',
        'name',
        'country_id',
        'description',
        'age',
    ];
}
