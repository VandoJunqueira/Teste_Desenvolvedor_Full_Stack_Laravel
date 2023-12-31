<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Metric extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip',
        'user_agent',
        'browsers',
        'system'
    ];
}
