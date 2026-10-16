<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->orderBy('id')
            ->get(['id', 'name', 'surname', 'email', 'created_at']);

        return response()->json(['data' => $users]);
    }

    public function show(User $user)
    {
        return response()->json([
            'data' => $user->only(['id', 'name', 'surname', 'email', 'created_at']),
        ]);
    }
}
