<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends Model
{
    use HasFactory;

    // Status sabitleri
    const STATUS_PENDING   = 'pending';   // henüz ödenmemiş, randevu beklemede
    const STATUS_COMPLETED = 'completed'; // randevu tamamlandı
    const STATUS_CANCELLED = 'cancelled'; // iptal edilmiş
    const STATUS_PAID      = 'paid';      // ödeme yapılmış

    protected $fillable = [
        'customer_id',
        'user_id',
        'start_datetime',
        'end_datetime',
        'service',
        'cost',
        'note',
        'status',
    ];

    // Çalışan ilişkisi
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Müşteri ilişkisi
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function payments()
    {
        return $this->hasMany(Payment::class, 'appointment_id');
    }

}
