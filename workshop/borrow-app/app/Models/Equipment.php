<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipment';

    protected $fillable = [
        'equipment_category_id','code','name','stock','photo_path',
    ];

    public function category()
    {
        return $this->belongsTo(EquipmentCategory::class, 'equipment_category_id');
    }

    public function borrows()
    {
        return $this->hasMany(Borrow::class);
    }
}
