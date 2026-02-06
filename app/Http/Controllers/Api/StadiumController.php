<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StadiumResource;
use App\Models\Stadium;
use App\Models\BankInfo;
use App\Models\PosStadiumSetting;
use Illuminate\Http\Request;

class StadiumController extends Controller
{
    /**
     * Get all stadiums
     */
    public function index(Request $request)
    {
        $query = Stadium::with('province', 'courts', 'reviews');

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by province
        if ($request->filled('province_id')) {
            $query->where('province_id', $request->province_id);
        }

        // Sort
        if($request->sort && in_array($request->sort, ['name', 'created_at'])) {
            $query->orderBy($request->sort, $request->input('direction', 'asc'));
        }
        

        // Pagination
        $stadiums = $query->paginate( $request->input('per_page', 15));

        return StadiumResource::collection($stadiums)
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Get stadium details
     */
    public function show($id)
    {
        $stadium = Stadium::with('province', 'courts', 'reviews')->find($id);

        if (!$stadium) {
            return response()->json([
                'success' => false,
                'message' => 'Stadium not found',
            ], 404);
        }

        return new StadiumResource($stadium);
    }

    /**
     * Get bank info for stadium (for payment QR generation)
     */
    public function getBankInfo($stadiumId)
    {
        try {
            $stadium = Stadium::find($stadiumId);

            if (!$stadium) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stadium not found',
                ], 404);
            }

            // Get bank info from pos_stadium_setting table in pickleball_pos database
            $bankInfo = PosStadiumSetting::getBankInfo($stadiumId);

            if (!$bankInfo || !$bankInfo['bank_code'] || !$bankInfo['account_number']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bank information not configured for this stadium',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'bank_code' => $bankInfo['bank_code'],
                    'account_number' => $bankInfo['account_number'],
                    'account_name' => $bankInfo['account_name'],
                    'bank_name' => $bankInfo['bank_name']
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('getBankInfo error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching bank information',
            ], 500);
        }
    }
}
