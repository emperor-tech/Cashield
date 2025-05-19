<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model {
    use HasFactory;

    protected $fillable = [
        'user_id', 'campus', 'description', 'location', 'severity', 'anonymous', 'status'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function comments() {
        return $this->hasMany(ReportComment::class);
    }
}
