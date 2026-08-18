<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Form extends Model
{
    use HasFactory, HasSlug, BelongsToTenant;

    protected $fillable = [
        'name',
        'slug',
        'fields',
        'notification_email',
        'success_message',
        'is_active',
    ];

    protected $casts = [
        'fields' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Field schema example:
     * [
     *   {
     *     "name": "full_name",
     *     "label": "Full Name",
     *     "type": "text",
     *     "required": true,
     *     "placeholder": "Enter your name",
     *     "validation": "string|max:255"
     *   },
     *   {
     *     "name": "email",
     *     "label": "Email",
     *     "type": "email",
     *     "required": true,
     *     "validation": "email|max:255"
     *   },
     *   {
     *     "name": "message",
     *     "label": "Message",
     *     "type": "textarea",
     *     "required": true,
     *     "validation": "string|max:5000"
     *   }
     * ]
     */

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function submissions()
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Build dynamic validation rules from the form's field config.
     */
    public function getValidationRules(): array
    {
        $rules = [];

        foreach ($this->fields ?? [] as $field) {
            $fieldRules = [];

            if (!empty($field['required'])) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            if (!empty($field['validation'])) {
                $fieldRules[] = $field['validation'];
            }

            $rules[$field['name']] = implode('|', $fieldRules);
        }

        return $rules;
    }
}
