<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportCategory extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'severity_level',
        'response_time',
        'requires_approval',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'requires_approval' => 'boolean',
        'response_time' => 'integer',
    ];

    /**
     * Get the reports for this category.
     */
    public function reports()
    {
        return $this->hasMany(Report::class);
    }
} 