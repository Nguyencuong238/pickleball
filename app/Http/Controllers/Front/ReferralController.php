<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show referral page
     */
    public function index()
    {
        $user = auth()->user();

        // Get referral stats
        $referralStats = $user->getReferralStats();
        
        // Get referral details
        $referralDetails = $user->referrals()->with('referredUser')->latest()->get();

        return view('user.referral.index', compact('user', 'referralStats', 'referralDetails'));
    }
}
