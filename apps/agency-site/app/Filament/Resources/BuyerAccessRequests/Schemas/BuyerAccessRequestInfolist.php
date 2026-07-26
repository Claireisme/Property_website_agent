<?php

namespace App\Filament\Resources\BuyerAccessRequests\Schemas;

use App\Models\BuyerAccessRequest;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class BuyerAccessRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('property.title')
                    ->label('Property'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('buyer_name'),
                TextEntry::make('buyer_email'),
                TextEntry::make('buyer_phone')
                    ->placeholder('-'),
                TextEntry::make('initial_offer_amount')
                    ->label('Current offer')
                    ->money('EUR')
                    ->placeholder('-'),
                ViewEntry::make('review_decision')
                    ->label('Review decision')
                    ->state(fn (BuyerAccessRequest $record): array => self::reviewState($record))
                    ->view('filament.buyer-access-requests.review-actions')
                    ->columnSpanFull(),
                TextEntry::make('buyer_position')
                    ->placeholder('-'),
                TextEntry::make('financing_type')
                    ->placeholder('-'),
                TextEntry::make('mortgage_approval_status')
                    ->placeholder('-'),
                TextEntry::make('current_property_status')
                    ->placeholder('-'),
                ViewEntry::make('uploaded_documents')
                    ->label('Uploaded documents')
                    ->state(fn (BuyerAccessRequest $record): array => self::documentsState($record))
                    ->view('filament.buyer-access-requests.documents')
                    ->columnSpanFull(),
                TextEntry::make('message')
                    ->placeholder('-')
                    ->columnSpanFull(),
                IconEntry::make('consent_to_terms')
                    ->boolean(),
                TextEntry::make('requested_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('documents_uploaded_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('reviewed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('approved_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('rejected_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function documentsState(BuyerAccessRequest $record): array
    {
        return [
            'documents' => [
                self::documentState($record, 'proof', 'Proof of funds or mortgage approval', $record->proof_of_funds_path),
                self::documentState($record, 'identity', 'Photo ID', $record->identity_document_path),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function documentState(BuyerAccessRequest $record, string $key, string $label, ?string $path): array
    {
        $exists = filled($path) && Storage::disk('local')->exists($path);
        $extension = strtolower(pathinfo((string) $path, PATHINFO_EXTENSION));

        return [
            'key' => $key,
            'label' => $label,
            'path' => $path,
            'filename' => $path ? basename($path) : null,
            'exists' => $exists,
            'extension' => $extension,
            'is_image' => in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true),
            'is_pdf' => $extension === 'pdf',
            'preview_url' => $exists ? route('admin.buyer-access-requests.documents.show', [$record, $key]) : null,
            'download_url' => $exists ? route('admin.buyer-access-requests.documents.download', [$record, $key]) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function reviewState(BuyerAccessRequest $record): array
    {
        return [
            'status' => $record->status,
            'approve_url' => route('admin.buyer-access-requests.approve', $record),
            'reject_url' => route('admin.buyer-access-requests.reject', $record),
        ];
    }
}
