<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    // SQL Server tablo adı
    protected $table = 'customers';

    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'phone',
        'email',
        'note',
    ];

    // İsteğe bağlı ilişkiler (örn: appointments)
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'customer_id', 'id');
    }
    public function payments()
{
    return $this->hasMany(Payment::class, 'customer_id');
}

}
