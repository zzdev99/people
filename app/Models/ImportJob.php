<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'status',
        'total_rows',
        'processed_rows',
        'failed_rows',
        'error_logs',
    ];
}
