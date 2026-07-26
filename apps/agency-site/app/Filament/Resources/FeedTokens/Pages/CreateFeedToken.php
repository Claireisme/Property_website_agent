<?php

namespace App\Filament\Resources\FeedTokens\Pages;

use App\Filament\Resources\FeedTokens\FeedTokenResource;
use App\Models\FeedToken;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateFeedToken extends CreateRecord
{
    protected static string $resource = FeedTokenResource::class;

    private ?string $plainTextToken = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->plainTextToken = FeedToken::generatePlainTextToken();

        return $data + [
            'token_hash' => FeedToken::hashToken($this->plainTextToken),
        ];
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Feed token created')
            ->body('Copy this token now: '.$this->plainTextToken);
    }
}
