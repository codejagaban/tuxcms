<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $fillable = [
        'job_post_id', 'name', 'email', 'phone', 'cover_letter',
        'cv_path', 'cv_name', 'delivery_status', 'emailed_at',
    ];

    protected $casts = ['emailed_at' => 'datetime'];

    public function jobPost()
    {
        return $this->belongsTo(JobPost::class);
    }
}
