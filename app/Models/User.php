<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use NotificationChannels\WebPush\PushSubscription;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'role',
        'phone',
        'department',
        'student_id',
        'notification_prefs',
        'last_location_lat',
        'last_location_lng',
        'safety_points'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'notification_prefs' => 'array',
        'last_location_lat' => 'float',
        'last_location_lng' => 'float',
        'safety_points' => 'integer'
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class)->withTimestamps();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function emergencyResponses(): HasMany
    {
        return $this->hasMany(EmergencyResponse::class, 'responder_id');
    }

    public function notificationPreferences(): HasOne
    {
        return $this->hasOne(NotificationPreference::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ReportComment::class);
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar 
            ? asset('storage/' . $this->avatar) 
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=0D8ABC&color=fff';
    }

    public function hasRole(string|array $role): bool
    {
        if (is_array($role)) {
            return in_array($this->role, $role);
        }
        return $this->role === $role;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function getNotificationPrefsAttribute($value): array
    {
        return $value ? json_decode($value, true) : [
            'email' => true,
            'push' => true,
            'area' => true
        ];
    }

    public function setNotificationPrefsAttribute($value): void
    {
        $this->attributes['notification_prefs'] = json_encode($value);
    }

    public function scopeResponders($query)
    {
        return $query->whereIn('role', ['security', 'admin']);
    }

    public function areaSubscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class)->where('type', 'area');
    }

    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    public function routeNotificationForWebPush()
    {
        return $this->pushSubscriptions();
    }

    public function earnPoints(int $points): self
    {
        $this->increment('safety_points', $points);
        return $this->fresh();
    }

    public function getPointsHistoryAttribute(): array
    {
        return [
            'reports' => $this->reports()->count() * 10,
            'responses' => $this->emergencyResponses()->count() * 15,
            'verified_reports' => $this->reports()->where('verified', true)->count() * 20,
            'badges' => $this->badges()->count() * 50
        ];
    }
}
