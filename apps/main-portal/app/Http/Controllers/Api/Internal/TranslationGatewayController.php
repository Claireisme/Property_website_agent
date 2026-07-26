<?php

namespace App\Http\Controllers\Api\Internal;

use App\Http\Controllers\Controller;
use App\Services\DeepSeekTranslationService;
use App\Support\Locales;
use Illuminate\Http\Request;

class TranslationGatewayController extends Controller
{
    public function __invoke(Request $request, DeepSeekTranslationService $translator)
    {
        $data = $request->validate([
            'locale' => ['required', 'string', 'max:8'],
            'source.title' => ['required', 'string', 'max:255'],
            'source.description' => ['nullable', 'string'],
            'source.features' => ['nullable', 'array'],
            'source.features.*' => ['string', 'max:1000'],
            'source.viewing_notes' => ['nullable', 'string'],
        ]);

        abort_unless(Locales::isSupported($data['locale']) && $data['locale'] !== Locales::default(), 422);

        return response()->json([
            'provider' => 'deepseek',
            'locale' => $data['locale'],
            'translation' => $translator->translatePropertyPayload($data['source'], $data['locale']),
        ]);
    }
}
