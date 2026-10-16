<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MediaItem;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function index(MediaItem $item)
    {
        $reviews = $item->reviews()
            ->with('user:id,name,surname')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $reviews]);
    }

    public function byUser(User $user)
    {
        $reviews = $user->reviews()
            ->with('mediaItem:id,type,title,creator,published_year,cover_url')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'user' => $user->only(['id', 'name', 'surname']),
            'data' => $reviews,
        ]);
    }

    public function store(Request $request, MediaItem $item)
    {
        if ($item->reviews()->where('user_id', $request->user()->id)->exists()) {
            throw ValidationException::withMessages([
                'review' => ['คุณรีวิวรายการนี้ไปแล้ว'],
            ]);
        }

        $attributes = $request->validate([
            'rating' => ['required', 'numeric', 'between:1,5'],
            'body' => ['nullable', 'string', 'max:10000'],
        ]);

        $review = $item->reviews()->create([
            ...$attributes,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'เพิ่มรีวิวสำเร็จ',
            'data' => $review->load('user:id,name,surname'),
        ], 201);
    }

    public function update(Request $request, Review $review)
    {
        abort_unless($review->user_id === $request->user()->id, 403, 'แก้ไขได้เฉพาะรีวิวของตนเอง');

        $attributes = $request->validate([
            'rating' => ['sometimes', 'required', 'numeric', 'between:1,5'],
            'body' => ['sometimes', 'nullable', 'string', 'max:10000'],
        ]);

        $review->update($attributes);

        return response()->json([
            'message' => 'แก้ไขรีวิวสำเร็จ',
            'data' => $review->fresh()->load('user:id,name,surname'),
        ]);
    }

    public function destroy(Request $request, Review $review)
    {
        abort_unless($review->user_id === $request->user()->id, 403, 'ลบได้เฉพาะรีวิวของตนเอง');

        $review->delete();

        return response()->json(['message' => 'ลบรีวิวสำเร็จ']);
    }
}
