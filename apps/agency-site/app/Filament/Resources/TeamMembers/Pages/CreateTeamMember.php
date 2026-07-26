<?php

namespace App\Filament\Resources\TeamMembers\Pages;

use App\Filament\Resources\TeamMembers\Pages\Concerns\SyncsTeamMemberAccount;
use App\Filament\Resources\TeamMembers\TeamMemberResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTeamMember extends CreateRecord
{
    use SyncsTeamMemberAccount;

    protected static string $resource = TeamMemberResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->extractAccountPassword($data);
    }

    protected function afterCreate(): void
    {
        $this->syncTeamMemberAccount($this->record);
    }
}
