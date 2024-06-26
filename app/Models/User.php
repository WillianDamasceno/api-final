<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
  use HasFactory, Notifiable;

  protected $fillable = [
    'name',
    'email',
    'pass',
    'partner_id',
  ];

  protected $hidden = [
    'password',
    'remember_token',
  ];

  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }

  protected static function booted(): void
  {
    static::creating(function (User $user) {
      $user->code = Str::random(6);
    });
  }

  public function partner(): BelongsTo
  {
    return $this->belongsTo(User::class, 'partner_id');
  }

  public function tasks(): HasMany
  {
    return $this->hasMany(Task::class)->where(function ($query) {
      $query->where('user_id', $this->id)
        ->orWhere('user_id', $this->partner_id);
    });
  }

  public function setPartner(User $partner): void
  {
    $this->partner_id = $partner->id;
    $partner->partner_id = $this->id;

    $this->save();
    $partner->save();
  }

  public function getTasks()
  {
    $userTasks = $this->tasks()->orderByDesc('created_at')->get();
    $partnerTasks = $this->partner->tasks()->orderByDesc('created_at')->get();

    return array_values($userTasks->merge($partnerTasks)->sortByDesc('created_at')->toArray());
  }

  public function createTask(string $name): Task
  {
    $task = Task::create([
      'name' => $name,
      'user_id' => $this->id,
      'partner_id' => $this->partner_id,
    ]);

    return $task;
  }
}
