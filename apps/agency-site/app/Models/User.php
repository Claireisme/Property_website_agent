<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'email_verified_at', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdministrator() || $this->activeTeamMember() !== null;
    }

    public function teamMember(): HasOne
    {
        return $this->hasOne(TeamMember::class);
    }

    public function buyerAccessRequests(): HasMany
    {
        return $this->hasMany(BuyerAccessRequest::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function isAdministrator(): bool
    {
        return $this->role === 'admin';
    }

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    public function isBuyer(): bool
    {
        return $this->role === 'buyer';
    }

    public function activeTeamMember(): ?TeamMember
    {
        if (! $this->isAgent()) {
            return null;
        }

        $teamMember = $this->relationLoaded('teamMember')
            ? $this->teamMember
            : $this->teamMember()->first();

        return $teamMember?->is_active ? $teamMember : null;
    }

    public function canManageListingsAndEnquiries(): bool
    {
        return $this->isAdministrator() || $this->activeTeamMember() !== null;
    }
}
