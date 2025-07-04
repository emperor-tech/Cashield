<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;
    protected $fillable = [
        'report_id', 'user_id', 'message',
    ];
    public function user() { return $this->belongsTo(User::class); }
    public function report() { return $this->belongsTo(Report::class); }
} 