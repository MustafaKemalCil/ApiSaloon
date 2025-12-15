<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    use HasFactory;

    // Toplu atanabilir alanlar
    protected $fillable = [
        'platform',   // android / ios
        'version',    // sürüm numarası
        'file_path',  // APK/IPA yolu
    ];
}
