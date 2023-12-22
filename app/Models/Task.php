<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'status', 'deadline'];

    protected $casts = [
        'deadline' => 'datetime',
    ];

    public function isOverdue(): bool
    {
        return $this->deadline <= Carbon::now();
    }

    public function setUser($userId)
    {
        $this->attributes['user_id'] = $userId;
    }

    public function setProject($projectId)
    {
        $this->attributes['project_id'] = $projectId;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
