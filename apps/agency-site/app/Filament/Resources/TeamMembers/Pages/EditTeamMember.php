<?php

namespace App\Filament\Resources\TeamMembers\Pages;

use App\Filament\Resources\TeamMembers\Pages\Concerns\SyncsTeamMemberAccount;
use App\Filament\Resources\TeamMembers\TeamMemberResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTeamMember extends EditRecord
{
    use SyncsTeamMemberAccount;

    protected static string $resource = TeamMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->extractAccountPassword($data);
    }

    protected function afterSave(): void
    {
        $this->syncTeamMemberAccount($this->record);
    }
}
