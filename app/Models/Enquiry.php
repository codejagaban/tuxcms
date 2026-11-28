<?php

namespace App\Models;

use App\Support\EnquiryKinds;
use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    protected $fillable = [
        'type', 'name', 'organisation', 'email', 'phone', 'message',
        'details', 'delivery_status', 'emailed_at', 'spam_reason',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'emailed_at' => 'datetime',
        ];
    }

    /** What this kind of message is called; the site's theme decides. */
    public function typeLabel(): string
    {
        return EnquiryKinds::label((string) $this->type);
    }
}
