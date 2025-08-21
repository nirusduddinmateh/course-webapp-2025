<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrow extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','equipment_id','borrowed_at','due_at','returned_at','status','note',
    ];

    protected $casts = [
        'borrowed_at' => 'datetime',
        'due_at'      => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    // ช่วย query เร็วๆ: รายการที่ยังไม่คืน
    public function scopeActive($q)
    {
        return $q->whereNull('returned_at');
    }
}
