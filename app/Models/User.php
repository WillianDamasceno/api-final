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
    return $this->hasMany(Task::class);
  }

  public function setPartner(User $partner): void
  {
    $this->partner_id = $partner->id;
    $partner->partner_id = $this->id;

    $this->save();
    $partner->save();
  }
}
