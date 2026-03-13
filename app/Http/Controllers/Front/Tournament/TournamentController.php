<?php

namespace App\Http\Controllers\Front\Tournament;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Services\Tournament\TournamentCrudService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TournamentController extends Controller
{
    public function __construct(private TournamentCrudService $service)
    {
        $this->middleware(['auth']);
    }

    public function index(Request $request)
    {
        $query = Tournament::where('user_id', auth()->id())
            ->withCount('athletes')
            ->latest();

        if ($request->filled('search')) {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $request->search);
            $query->where('name', 'like', '%' . $escaped . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tournaments = $query->paginate(12)->withQueryString();

        return view('home-yard.tournaments.index', compact('tournaments'));
    }

    public function create()
    {
        return view('home-yard.tournaments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->service->validationRules());

        $tournament = DB::transaction(function () use ($validated, $request) {
            $t = Tournament::create(array_merge(
                $this->service->fillable($validated),
                [
                    'user_id' => auth()->id(),
                    'slug'    => $this->service->generateSlug($validated['name']),
                ]
            ));

            $this->service->syncCategories($t, $validated['categories'] ?? []);

            if ($request->hasFile('banner')) {
                $t->addMediaFromRequest('banner')->toMediaCollection('banner');
            }

            return $t;
        });

        return redirect()
            ->route('tournament-manage.tournaments.show', $tournament)
            ->with('success', 'Giải đấu đã được tạo thành công.');
    }

    public function show(Tournament $tournament)
    {
        $this->authorizeOwner($tournament);

        $tournament->load(['categories', 'athletes', 'matches']);

        return view('home-yard.tournaments.show', compact('tournament'));
    }

    public function edit(Tournament $tournament)
    {
        $this->authorizeOwner($tournament);

        $tournament->load('categories');

        return view('home-yard.tournaments.edit', compact('tournament'));
    }

    public function update(Request $request, Tournament $tournament)
    {
        $this->authorizeOwner($tournament);

        $validated = $request->validate($this->service->validationRules(true));

        DB::transaction(function () use ($validated, $request, $tournament) {
            $tournament->update($this->service->fillable($validated));

            $this->service->syncCategories($tournament, $validated['categories'] ?? []);

            if ($request->hasFile('banner')) {
                $tournament->clearMediaCollection('banner');
                $tournament->addMediaFromRequest('banner')->toMediaCollection('banner');
            }
        });

        return redirect()
            ->route('tournament-manage.tournaments.show', $tournament)
            ->with('success', 'Giải đấu đã được cập nhật.');
    }

    public function destroy(Tournament $tournament)
    {
        $this->authorizeOwner($tournament);

        $tournament->clearMediaCollection('banner');
        $tournament->delete();

        return redirect()
            ->route('tournament-manage.tournaments.index')
            ->with('success', 'Giải đấu đã được xóa.');
    }

    private function authorizeOwner(Tournament $tournament): void
    {
        abort_if(
            $tournament->user_id !== auth()->id(),
            403,
            'Bạn không có quyền thực hiện thao tác này.'
        );
    }
}
