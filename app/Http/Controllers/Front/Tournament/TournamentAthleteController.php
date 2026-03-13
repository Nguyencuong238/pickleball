<?php

namespace App\Http\Controllers\Front\Tournament;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentAthlete;
use App\Models\TournamentCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TournamentAthleteController extends Controller
{
    use TournamentAthleteStatusTrait;

    public function __construct()
    {
        $this->middleware(['auth']);
    }

    private function authorizeOwner(Tournament $tournament): void
    {
        abort_if($tournament->user_id !== auth()->id(), 403, 'Bạn không có quyền truy cập giải đấu này.');
    }

    private function formatAthlete(TournamentAthlete $athlete): array
    {
        return [
            'id'             => $athlete->id,
            'athlete_name'   => $athlete->athlete_name,
            'email'          => $athlete->email,
            'phone'          => $athlete->phone,
            'category_id'    => $athlete->category_id,
            'category_name'  => $athlete->category->category_name ?? '',
            'category_type'  => $athlete->category->category_type ?? '',
            'is_doubles'     => $athlete->category ? $athlete->category->isDoubles() : false,
            'status'         => $athlete->status,
            'payment_status' => $athlete->payment_status,
            'partner_id'     => $athlete->partner_id,
            'partner_name'   => $athlete->partner->athlete_name ?? null,
        ];
    }

    /**
     * Danh sách vận động viên của giải đấu
     */
    public function index(Tournament $tournament)
    {
        $this->authorizeOwner($tournament);

        if (request()->wantsJson()) {
            $athletes = TournamentAthlete::with(['category', 'partner'])
                ->where('tournament_id', $tournament->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($a) => $this->formatAthlete($a));

            $categories = TournamentCategory::where('tournament_id', $tournament->id)
                ->get(['id', 'category_name', 'category_type']);

            return response()->json([
                'athletes'   => $athletes,
                'categories' => $categories,
            ]);
        }

        $athletes = TournamentAthlete::with(['category', 'partner'])
            ->where('tournament_id', $tournament->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $categories = TournamentCategory::where('tournament_id', $tournament->id)->get();

        return view('home-yard.tournaments.athletes', compact('tournament', 'athletes', 'categories'));
    }

    /**
     * Thêm vận động viên vào giải đấu
     */
    public function store(Request $request, Tournament $tournament): JsonResponse
    {
        $this->authorizeOwner($tournament);

        $validated = $request->validate([
            'athlete_name' => 'required|string|max:100',
            'email'        => 'required|email|max:100',
            'phone'        => 'nullable|string|max:20',
            'category_id'  => 'required|integer|exists:tournament_categories,id',
            'partner_id'   => 'nullable|integer|exists:tournament_athletes,id',
        ]);

        $category = TournamentCategory::where('id', $validated['category_id'])
            ->where('tournament_id', $tournament->id)
            ->firstOrFail();

        $athlete = DB::transaction(function () use ($validated, $tournament, $category) {
            $athlete = TournamentAthlete::create([
                'tournament_id'  => $tournament->id,
                'category_id'    => $category->id,
                'athlete_name'   => $validated['athlete_name'],
                'email'          => $validated['email'],
                'phone'          => $validated['phone'] ?? null,
                'status'         => 'pending',
                'payment_status' => 'unpaid',
            ]);

            if ($category->isDoubles() && !empty($validated['partner_id'])) {
                $partner = TournamentAthlete::where('id', $validated['partner_id'])
                    ->where('tournament_id', $tournament->id)
                    ->where('category_id', $category->id)
                    ->first();

                if ($partner) {
                    $athlete->partner_id = $partner->id;
                    $athlete->save();
                    $partner->partner_id = $athlete->id;
                    $partner->save();
                }
            }

            return $athlete;
        });

        $athlete->load(['category', 'partner']);

        return response()->json([
            'message' => 'Thêm vận động viên thành công.',
            'athlete' => $this->formatAthlete($athlete),
        ], 201);
    }

    /**
     * Cập nhật thông tin vận động viên
     */
    public function update(Request $request, Tournament $tournament, TournamentAthlete $athlete): JsonResponse
    {
        $this->authorizeOwner($tournament);
        abort_if($athlete->tournament_id !== $tournament->id, 404);

        $validated = $request->validate([
            'athlete_name' => 'required|string|max:100',
            'email'        => 'required|email|max:100',
            'phone'        => 'nullable|string|max:20',
        ]);

        $athlete->update($validated);
        $athlete->load(['category', 'partner']);

        return response()->json([
            'message' => 'Cập nhật thông tin thành công.',
            'athlete' => $this->formatAthlete($athlete),
        ]);
    }

    /**
     * Xóa vận động viên khỏi giải đấu
     */
    public function destroy(Tournament $tournament, TournamentAthlete $athlete): JsonResponse
    {
        $this->authorizeOwner($tournament);
        abort_if($athlete->tournament_id !== $tournament->id, 404);

        DB::transaction(function () use ($athlete) {
            if ($athlete->partner_id) {
                TournamentAthlete::where('id', $athlete->partner_id)
                    ->update(['partner_id' => null]);
            }
            TournamentAthlete::where('partner_id', $athlete->id)
                ->update(['partner_id' => null]);

            $athlete->delete();
        });

        return response()->json(['message' => 'Xóa vận động viên thành công.']);
    }

    /**
     * Danh sách vận động viên theo trạng thái
     */
    public function listByStatus(Request $request, Tournament $tournament): JsonResponse
    {
        $this->authorizeOwner($tournament);

        $query = TournamentAthlete::with(['category', 'partner'])
            ->where('tournament_id', $tournament->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $athletes = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($a) => $this->formatAthlete($a));

        return response()->json(['athletes' => $athletes]);
    }
}
