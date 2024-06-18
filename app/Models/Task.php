<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'status',
  ];

  const STATUS_PENDING = 'pending';
  const STATUS_DONE = 'done';

  protected static function booted(): void
  {
    static::creating(fn (Task $task) => $task->status = self::STATUS_PENDING);
  }

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
