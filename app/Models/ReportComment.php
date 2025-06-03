<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportComment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'report_id',
        'user_id',
        'content',
        'attachments',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'attachments' => 'array',
    ];

    /**
     * Get the report that owns the comment.
     */
    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * Get the user who made the comment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the media files attached to the comment.
     */
    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }
} 