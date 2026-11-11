<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    public const TYPES = ['staff_request', 'registration', 'contact'];

    protected $fillable = [
        'type', 'name', 'organisation', 'email', 'phone', 'message',
        'details', 'delivery_status', 'emailed_at',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'emailed_at' => 'datetime',
        ];
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'staff_request' => 'Staff request',
            'registration' => 'Candidate registration',
            default => 'Enquiry',
        };
    }
}
