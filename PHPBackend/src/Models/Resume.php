<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Resume extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'resumes';

    protected $fillable = [
        'firstName',
        'lastName',
        'email',
        'title',
        'summary',
        'jobTitle',
        'phone',
        'address',
        'user_id',
        'experience',
        'education',
        'skills',
        'projects',
        'themeColor',
    ];

    protected $casts = [
        'experience' => 'array',
        'education' => 'array',
        'skills' => 'array',
        'projects' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
