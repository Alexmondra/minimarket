<?php

namespace App\Support;

use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SucursalContext
{
    public const SESSION_KEY = 'active_sucursal_id';

    public function isAdmin(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $user?->hasRole('Administrador') ?? false;
    }

    public function assignedSucursales(?User $user = null): Collection
    {
        $user ??= auth()->user();

        if (! $user?->empresa_id) {
            return collect();
        }

        return $user->sucursales()
            ->where('sucursales.empresa_id', $user->empresa_id)
            ->where('sucursales.activo', true)
            ->orderBy('sucursales.nombre_sucursal')
            ->get();
    }

    public function allowedSucursales(?User $user = null): Collection
    {
        $user ??= auth()->user();

        if (! $user?->empresa_id) {
            return collect();
        }

        $assigned = $this->assignedSucursales($user);

        if ($this->isAdmin($user) && $assigned->isEmpty()) {
            return Sucursal::query()
                ->where('empresa_id', $user->empresa_id)
                ->where('activo', true)
                ->orderBy('nombre_sucursal')
                ->get();
        }

        return $assigned;
    }

    public function allowedSucursalIds(?User $user = null): Collection
    {
        return $this->allowedSucursales($user)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();
    }

    public function activeSucursalId(): ?int
    {
        $value = session(self::SESSION_KEY);

        return is_numeric($value) ? (int) $value : null;
    }

    public function activeSucursal(?User $user = null): ?Sucursal
    {
        $activeId = $this->activeSucursalId();

        if (! $activeId) {
            return null;
        }

        return $this->allowedSucursales($user)->firstWhere('id', $activeId);
    }

    public function canUseAllMode(?User $user = null): bool
    {
        $user ??= auth()->user();

        if (! $this->isAdmin($user)) {
            return false;
        }

        $assignedCount = $this->assignedSucursales($user)->count();

        return $assignedCount === 0 || $assignedCount > 1;
    }

    public function shouldShowTopbarSelector(?User $user = null): bool
    {
        return $this->canUseAllMode($user) && $this->allowedSucursales($user)->isNotEmpty();
    }

    public function canAccessSucursal(int $sucursalId, ?User $user = null): bool
    {
        return $this->allowedSucursalIds($user)->contains($sucursalId);
    }

    public function selectSucursal(?int $sucursalId, ?User $user = null): void
    {
        $user ??= auth()->user();

        if ($sucursalId === null) {
            abort_unless($this->canUseAllMode($user), 403, 'No puedes trabajar sin una sucursal seleccionada.');

            session([self::SESSION_KEY => null]);

            return;
        }

        abort_unless($this->canAccessSucursal($sucursalId, $user), 403, 'No tienes acceso a esa sucursal.');

        session([self::SESSION_KEY => $sucursalId]);
    }

    public function normalizeSession(?User $user = null): void
    {
        $user ??= auth()->user();

        if (! $user) {
            return;
        }

        $allowed = $this->allowedSucursales($user);
        $activeId = $this->activeSucursalId();

        if ($activeId && ! $allowed->contains('id', $activeId)) {
            session()->forget(self::SESSION_KEY);
            $activeId = null;
        }

        if (! $this->isAdmin($user)) {
            if ($allowed->count() === 1) {
                session([self::SESSION_KEY => $allowed->first()->id]);
            }

            return;
        }

        $assignedCount = $this->assignedSucursales($user)->count();

        if ($assignedCount === 1 && $allowed->isNotEmpty()) {
            session([self::SESSION_KEY => $allowed->first()->id]);

            return;
        }

        if (! session()->exists(self::SESSION_KEY)) {
            session([self::SESSION_KEY => null]);
        }
    }

    public function requiresSelectionPage(?User $user = null): bool
    {
        $user ??= auth()->user();

        if (! $user || $this->isAdmin($user)) {
            return false;
        }

        $allowed = $this->allowedSucursales($user);

        if ($allowed->count() <= 1) {
            return false;
        }

        $activeId = $this->activeSucursalId();

        return ! $activeId || ! $allowed->contains('id', $activeId);
    }

    public function hasNoSucursalAccess(?User $user = null): bool
    {
        $user ??= auth()->user();

        return ! $this->isAdmin($user) && $this->allowedSucursales($user)->isEmpty();
    }

    public function resolveSucursalForWrite(?int $requestedSucursalId = null, ?User $user = null): ?int
    {
        $user ??= auth()->user();

        $activeId = $this->activeSucursalId();

        if ($activeId && $this->canAccessSucursal($activeId, $user)) {
            return $activeId;
        }

        if ($requestedSucursalId && $this->canAccessSucursal($requestedSucursalId, $user)) {
            return $requestedSucursalId;
        }

        $allowed = $this->allowedSucursales($user);

        if ($allowed->count() === 1) {
            return (int) $allowed->first()->id;
        }

        return null;
    }

    public function sucursalesForWrite(?User $user = null): Collection
    {
        $activeId = $this->activeSucursalId();
        $allowed = $this->allowedSucursales($user);

        if ($activeId) {
            return $allowed->where('id', $activeId)->values();
        }

        return $allowed;
    }

    public function applyToQuery(Builder $query, string $column = 'sucursal_id', ?User $user = null): Builder
    {
        $user ??= auth()->user();
        $activeId = $this->activeSucursalId();

        if ($activeId && $this->canAccessSucursal($activeId, $user)) {
            return $query->where($column, $activeId);
        }

        $allowedIds = $this->allowedSucursalIds($user);

        if ($allowedIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($column, $allowedIds->all());
    }

    public function applyNullableToQuery(Builder $query, string $column = 'sucursal_id', ?User $user = null): Builder
    {
        $user ??= auth()->user();
        $activeId = $this->activeSucursalId();

        return $query->where(function (Builder $query) use ($activeId, $column, $user) {
            $query->whereNull($column);

            if ($activeId && $this->canAccessSucursal($activeId, $user)) {
                $query->orWhere($column, $activeId);

                return;
            }

            $allowedIds = $this->allowedSucursalIds($user);

            if ($allowedIds->isNotEmpty()) {
                $query->orWhereIn($column, $allowedIds->all());
            }
        });
    }
}
