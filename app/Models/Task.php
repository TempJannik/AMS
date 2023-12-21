<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\Rule;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'status', 'deadline', 'user_id', 'project_id'];

    public $editable = ['title', 'description', 'status', 'deadline'];

    public static function createValidationRules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => ['required', Rule::in(['todo', 'in_progress', 'done'])],
            'user_id' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id',
            'deadline' => 'nullable|date|after:today',
        ];
    }

    public static function updateValidationRules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => ['required', Rule::in(['todo', 'in_progress', 'done'])],
            'deadline' => 'nullable|date|after:today',
        ];
    }

    public function editable(): array
    {
        return $this->editable;
    }

    public function isOverdue(): bool
    {
        return $this->deadline <= Carbon::now();
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
