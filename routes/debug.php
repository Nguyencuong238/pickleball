<?php

Route::get('/debug/referral', function () {
    $user = auth()->user();
    if (!$user) {
        return response()->json(['error' => 'Not authenticated'], 401);
    }
    
    return response()->json([
        'user_id' => $user->id,
        'user_name' => $user->name,
        'referral_code' => $user->referral_code,
        'referral_link' => url('/register?ref=' . $user->referral_code),
        'referral_count' => $user->referrals()->count(),
    ]);
});

Route::get('/debug/users', function () {
    $users = \App\Models\User::limit(5)->get(['id', 'name', 'email', 'referral_code']);
    return response()->json($users);
});
