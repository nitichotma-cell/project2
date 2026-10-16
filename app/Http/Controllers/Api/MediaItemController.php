<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MediaItem;
use Illuminate\Http\Request;

class MediaItemController extends Controller
{
    public function index()
    {
        $items = MediaItem::query()
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $items]);
    }

    public function show(MediaItem $item)
    {
        $item->loadAvg('reviews', 'rating')
            ->loadCount('reviews')
            ->load(['reviews' => fn ($query) => $query
                ->with('user:id,name,surname')
                ->orderByDesc('created_at')
                ->orderByDesc('id')]);

        return response()->json(['data' => $item]);
    }

    public function store(Request $request)
    {
        $item = MediaItem::create($this->validatedAttributes($request));

        return response()->json([
            'message' => 'เพิ่มรายการสำเร็จ',
            'data' => $item->loadCount('reviews')->loadAvg('reviews', 'rating'),
        ], 201);
    }

    public function update(Request $request, MediaItem $item)
    {
        $item->update($this->validatedAttributes($request, true));

        return response()->json([
            'message' => 'แก้ไขรายการสำเร็จ',
            'data' => $item->fresh()->loadCount('reviews')->loadAvg('reviews', 'rating'),
        ]);
    }

    public function destroy(MediaItem $item)
    {
        $item->delete();

        return response()->json(['message' => 'ลบรายการสำเร็จ']);
    }

    private function validatedAttributes(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'type' => [$required, 'in:book,movie'],
            'title' => [$required, 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'creator' => ['sometimes', 'nullable', 'string', 'max:255'],
            'published_year' => ['sometimes', 'nullable', 'integer', 'between:1000,9999'],
            'cover_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
        ]);
    }
}
