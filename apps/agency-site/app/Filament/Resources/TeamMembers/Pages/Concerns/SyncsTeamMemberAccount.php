<?php

namespace App\Filament\Resources\TeamMembers\Pages\Concerns;

use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Support\Str;

trait SyncsTeamMemberAccount
{
    protected ?string $accountPassword = null;

    protected function extractAccountPassword(array $data): array
    {
        $this->accountPassword = filled($data['account_password'] ?? null)
            ? (string) $data['account_password']
            : null;

        unset($data['account_password']);

        return $data;
    }

    protected function syncTeamMemberAccount(TeamMember $teamMember): void
    {
        if (blank($teamMember->email)) {
            return;
        }

        $user = $teamMember->user
            ?: User::query()->where('email', $teamMember->email)->first();

        $attributes = [
            'name' => $teamMember->name,
            'email' => $teamMember->email,
            'role' => 'agent',
        ];

        if ($this->accountPassword) {
            $attributes['password'] = $this->accountPassword;
        }

        if (! $user) {
            $attributes['password'] ??= Str::password(16);
            $user = User::query()->create($attributes);
        } else {
            $user->fill($attributes)->save();
        }

        if ($teamMember->user_id !== $user->id) {
            $teamMember->forceFill(['user_id' => $user->id])->saveQuietly();
        }
    }
}
