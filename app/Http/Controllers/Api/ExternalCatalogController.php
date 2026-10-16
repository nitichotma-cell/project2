<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ExternalCatalogException;
use App\Http\Controllers\Controller;
use App\Models\MediaItem;
use App\Services\ExternalCatalogService;
use Illuminate\Http\Request;

class ExternalCatalogController extends Controller
{
    public function search(Request $request, ExternalCatalogService $catalog)
    {
        $attributes = $request->validate([
            'type' => ['required', 'in:book,movie'],
            'q' => ['required', 'string', 'min:2', 'max:200'],
        ]);

        try {
            return response()->json([
                'data' => $catalog->search($attributes['type'], $attributes['q']),
            ]);
        } catch (ExternalCatalogException $exception) {
            return response()->json(['message' => $exception->getMessage()], 502);
        }
    }

    public function import(Request $request, ExternalCatalogService $catalog)
    {
        $attributes = $request->validate([
            'type' => ['required', 'in:book,movie'],
            'external_id' => ['required', 'string', 'max:255'],
        ]);

        try {
            $details = $catalog->find($attributes['type'], $attributes['external_id']);
        } catch (ExternalCatalogException $exception) {
            return response()->json(['message' => $exception->getMessage()], 502);
        }

        $item = MediaItem::updateOrCreate(
            [
                'external_source' => $details['external_source'],
                'external_id' => $details['external_id'],
            ],
            $details
        );

        return response()->json([
            'message' => 'นำเข้ารายการจากบริการภายนอกสำเร็จ',
            'data' => $item->loadCount('reviews')->loadAvg('reviews', 'rating'),
        ], $item->wasRecentlyCreated ? 201 : 200);
    }
}
