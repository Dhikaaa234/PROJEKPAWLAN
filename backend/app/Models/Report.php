<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_code',
        'user_id',
        'category_id',
        'status_id',
        'title',
        'description',
        'location',
        'image_path',
        'admin_response',
        'cancelled_at',
        'processed_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'cancelled_at' => 'datetime',
            'processed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

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

    public function logs()
    {
        return $this->hasMany(ReportLog::class);
    }

    public function reportLogs()
    {
        return $this->hasMany(ReportLog::class);
    }
}