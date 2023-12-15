<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'status', 'deadline', 'user_id', 'project_id'];
    public $editable = ['title', 'description', 'status', 'deadline'];

    public function editable(): array
    {
        return $this->editable;
    }

    public function isOverdue(): bool
    {
        return $this->deadline > Carbon::now();
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
