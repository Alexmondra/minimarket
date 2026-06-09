<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Filament\Models\Contracts\HasAvatar;
use Spatie\Permission\Traits\HasRoles;


#[Fillable(['name', 'email', 'password', 'empresa_id', 'telefono', 'activo', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements \Filament\Models\Contracts\FilamentUser, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursales()
    {
        return $this->belongsToMany(Sucursal::class, 'sucursal_user')
            ->withTimestamps()
            ->withPivot('deleted_at')
            ->wherePivotNull('deleted_at');
    }
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        // Esto le da permiso a cualquier usuario que crees en tu base de datos
        return true; 
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if (blank($this->avatar)) {
            return null;
        }

        if (Str::startsWith($this->avatar, ['http://', 'https://', 'data:image/'])) {
            return $this->avatar;
        }

        return asset('storage/' . ltrim($this->avatar, '/'));
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->getFilamentAvatarUrl();
    }

}
