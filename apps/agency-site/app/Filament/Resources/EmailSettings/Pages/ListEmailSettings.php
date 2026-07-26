<?php

namespace App\Filament\Resources\EmailSettings\Pages;

use App\Filament\Resources\Concerns\HasEmailSettingsTabs;
use App\Filament\Resources\EmailSettings\EmailSettingResource;
use App\Models\EmailSetting;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmailSettings extends ListRecords
{
    use HasEmailSettingsTabs;

    protected static string $resource = EmailSettingResource::class;

    public function mount(): void
    {
        parent::mount();

        $this->redirect(static::getResource()::getUrl('edit', [
            'record' => EmailSetting::current(),
        ], false), navigate: true);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn (): bool => EmailSetting::query()->doesntExist()),
        ];
    }
}
