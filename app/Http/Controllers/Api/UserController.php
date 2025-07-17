<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $users = User::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->select('id', 'name', 'email', 'position')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $users
        ]);
    }
}
