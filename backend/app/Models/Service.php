<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    // SQL Server tablo adı
    protected $table = 'service';

    protected $fillable = [
        'name',
        'description',
        'cost',

    ];


}
