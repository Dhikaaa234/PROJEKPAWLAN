<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'status_id',
        'judul',
        'deskripsi',
        'lokasi',
        'gambar',
    ];

    // =========================
    // RELATIONS
    // =========================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }
}