<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model {
    use HasFactory;

    protected $fillable = [
        'user_id', 'campus', 'description', 'location', 'severity', 'anonymous', 'status', 'media_path'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($report) {
            if (!$report->user_id) {
                // If no user_id is set, use the anonymous user
                $report->user_id = 1;
            }
        });
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function comments() {
        return $this->hasMany(ReportComment::class);
    }

    public function chatMessages() {
        return $this->hasMany(\App\Models\ChatMessage::class);
    }

    public function timelineEvents() {
        $events = collect();
        foreach ($this->comments as $comment) {
            $events->push([
                'type' => 'comment',
                'user' => $comment->user->name ?? 'Unknown',
                'message' => $comment->body,
                'created_at' => $comment->created_at,
            ]);
        }
        foreach ($this->chatMessages as $msg) {
            $events->push([
                'type' => 'chat',
                'user' => $msg->user->name ?? 'Unknown',
                'message' => $msg->message,
                'created_at' => $msg->created_at,
            ]);
        }
        if ($this->wasChanged('status')) {
            $events->push([
                'type' => 'status',
                'user' => $this->user->name ?? 'System',
                'message' => 'Status changed to ' . $this->status,
                'created_at' => $this->updated_at,
            ]);
        }
        return $events->sortBy('created_at')->values();
    }
}
