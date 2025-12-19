<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentAthlete;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TournamentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|home_yard']);
    }

    public function index()
    {
        $user = auth()->user();

        // Admins see all tournaments
        if ($user->hasRole('admin')) {
            $tournaments = Tournament::latest()->paginate(10);
        } else {
            // Home yard users only see their own tournaments
            $tournaments = Tournament::where('user_id', $user->id)->latest()->paginate(10);
        }

        return view('admin.tournaments.index', compact('tournaments'));
    }

    public function create()
    {
        return view('admin.tournaments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
           'name' => 'required|string|max:255',
           'description' => 'nullable|string',
           'start_date' => 'required|date',
           'end_date' => 'nullable|date|after_or_equal:start_date',
           'registration_deadline' => 'nullable|date_format:Y-m-d\TH:i',
           'location' => 'nullable|string|max:255',
           'max_participants' => 'required|integer|min:1',
           'price' => 'required|numeric|min:0',
           'rules' => 'nullable|string',
           'prizes' => 'nullable|string',
           'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
           'status' => 'required|in:0,1',
           'is_watch' => 'nullable|in:0,1',
           'is_ocr' => 'nullable|in:0,1',
           'tournament_rank' => 'nullable|string|in:beginner,intermediate,advanced,professional',
           'registration_benefits' => 'nullable|string',
           'competition_rules' => 'nullable|string',
           'event_timeline' => 'nullable|string',
           'social_information' => 'nullable|string',
           'organizer_email' => 'nullable|email',
           'organizer_hotline' => 'nullable|string|max:20',
           'competition_schedule' => 'nullable|string',
           'results' => 'nullable|string',
           'gallery_json' => 'nullable|string',
           'gallery.*' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
           'banner' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
           'category_types' => 'required|array|min:1',
           'category_types.*' => 'string|in:single_men,single_women,double_men,double_women,double_mixed',
        ]);

        $data = $request->only([
           'name',
           'description',
           'start_date',
           'end_date',
           'registration_deadline',
           'location',
           'max_participants',
           'price',
           'rules',
           'prizes',
           'status',
           'is_watch',
           'is_ocr',
           'tournament_rank',
           'registration_benefits',
           'competition_rules',
           'event_timeline',
           'social_information',
           'organizer_email',
           'organizer_hotline',
           'competition_schedule',
           'results',
        ]);

        // Handle checkbox - convert to integer
        $data['is_watch'] = $request->has('is_watch') ? 1 : 0;
        $data['is_ocr'] = $request->has('is_ocr') ? 1 : 0;

        if ($request->hasFile('image')) {
           $data['image'] = $request->file('image')->store('tournament_images', 'public');
        }

        // Handle gallery JSON (from admin form)
        if ($request->has('gallery_json') && !empty($request->gallery_json)) {
           try {
               $data['gallery'] = json_decode($request->gallery_json, true) ?? [];
           } catch (\Exception $e) {
               $data['gallery'] = [];
           }
        }
        // Handle gallery file uploads (legacy support)
        elseif ($request->hasFile('gallery')) {
           $gallery = [];
           foreach ($request->file('gallery') as $file) {
               $gallery[] = $file->store('tournament_gallery', 'public');
           }
           $data['gallery'] = $gallery;
        }

        // Handle banner file upload
        if ($request->hasFile('banner')) {
           $data['banner'] = $request->file('banner')->store('tournament_banner', 'public');
        }

        $data['user_id'] = auth()->id();
        
        // Generate slug from name
        $data['slug'] = Str::slug($request->name, '-');

        $tournament = Tournament::create($data);

        // Create categories from selected types
        if ($request->has('category_types') && is_array($request->category_types)) {
           foreach ($request->category_types as $categoryType) {
               $tournament->categories()->create([
                   'category_type' => $categoryType,
                   'category_name' => $this->getCategoryName($categoryType),
                   'status' => 1,
               ]);
           }
        }

        // Log activity
        ActivityLog::log("Giải đấu '{$tournament->name}' được tạo", 'Tournament', $tournament->id);

        // Redirect to homeyard tournaments if user is home_yard, else admin
        $redirectRoute = auth()->user()->hasRole('home_yard') ? 'homeyard.tournaments' : 'admin.tournaments.index';

        return redirect()->route($redirectRoute)->with('success', 'Tournament created successfully.');
    }

    public function show(Tournament $tournament)
    {
        $this->authorize('view', $tournament);
        $athletes = $tournament->athletes()->paginate(15);
        return view('admin.tournaments.show', compact('tournament', 'athletes'));
    }

    public function edit(Tournament $tournament)
    {
        $this->authorize('update', $tournament);
        return view('admin.tournaments.edit', compact('tournament'));
    }

    public function update(Request $request, Tournament $tournament)
    {
        $this->authorize('update', $tournament);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'registration_deadline' => 'nullable|date_format:Y-m-d\TH:i',
            'location' => 'nullable|string|max:255',
            'max_participants' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'rules' => 'nullable|string',
            'prizes' => 'nullable|numeric|min:0',
            'is_watch' => 'nullable|in:0,1',
            'is_ocr' => 'nullable|in:0,1',
            'tournament_rank' => 'nullable|string|in:beginner,intermediate,advanced,professional',
            'registration_benefits' => 'nullable|string',
            'competition_rules' => 'nullable|string',
            'event_timeline' => 'nullable|string',
            'social_information' => 'nullable|string',
            'organizer_email' => 'nullable|email',
            'organizer_hotline' => 'nullable|string|max:20',
            'category_types' => 'required|array|min:1',
            'category_types.*' => 'string|in:single_men,single_women,double_men,double_women,double_mixed',
        ]);

        $data = $request->only([
            'name',
            'description',
            'start_date',
            'end_date',
            'registration_deadline',
            'location',
            'max_participants',
            'price',
            'rules',
            'prizes',
            'is_watch',
            'is_ocr',
            'tournament_rank',
            'registration_benefits',
            'competition_rules',
            'event_timeline',
            'social_information',
            'organizer_email',
            'organizer_hotline',
        ]);

        // Handle checkbox - convert to integer
        $data['is_watch'] = $request->has('is_watch') ? 1 : 0;
        $data['is_ocr'] = $request->has('is_ocr') ? 1 : 0;

        // Generate slug from name if name changed
        if ($request->has('name')) {
            $data['slug'] = Str::slug($request->name, '-');
        }

        // Sync gallery images
        $tournament->syncMediaCollection('gallery', 'gallery', $request);

        // Sync banner image
        $tournament->syncMediaCollection('banner', 'banner', $request);

        $tournament->update($data);

        // Delete existing categories and create new ones
        if ($request->has('category_types') && is_array($request->category_types)) {
            $tournament->categories()->delete();
            
            foreach ($request->category_types as $categoryType) {
                $tournament->categories()->create([
                    'category_type' => $categoryType,
                    'category_name' => $this->getCategoryName($categoryType),
                    'status' => 1,
                ]);
            }
        }

        return redirect()->route('admin.tournaments.index')->with('success', 'Tournament updated successfully.');
    }

    public function destroy(Tournament $tournament)
    {
        $this->authorize('delete', $tournament);

        // Delete media files if they exist
        try {
            $bannerMedia = $tournament->getMedia('banner');
            foreach ($bannerMedia as $media) {
                $media->delete();
            }
        } catch (\Exception $e) {
            // Log error but continue with deletion
            \Log::warning('Failed to delete tournament media: ' . $e->getMessage());
        }

        $tournament->delete();

        return redirect()->route('admin.tournaments.index')->with('success', 'Tournament deleted successfully.');
    }

    /**
     * Get category name from category type
     */
    private function getCategoryName($categoryType)
    {
        $names = [
            'single_men' => 'Đơn Nam',
            'single_women' => 'Đơn Nữ',
            'double_men' => 'Đôi Nam',
            'double_women' => 'Đôi Nữ',
            'double_mixed' => 'Đôi Nam Nữ',
        ];
        
        return $names[$categoryType] ?? ucfirst(str_replace('_', ' ', $categoryType));
    }

    private function storeCategoryFormats(Tournament $tournament, array $formats): void
    {
        // Map format to enum values and category names
        $formatMap = [
            'single' => ['enum_value' => 'single_men', 'name' => 'Đơn'],
            'double' => ['enum_value' => 'double_men', 'name' => 'Đôi'],
            'mixed' => ['enum_value' => 'double_mixed', 'name' => 'Đôi nam nữ'],
        ];

        // Get or create TournamentCategory records for each format
        $categoryIds = [];
        
        foreach ($formats as $format) {
            if (!isset($formatMap[$format])) {
                continue;
            }

            $mapping = $formatMap[$format];
            
            // Check if category already exists for this tournament and format
            $category = \App\Models\TournamentCategory::firstOrCreate(
                [
                    'tournament_id' => $tournament->id,
                    'category_type' => $mapping['enum_value'],
                ],
                [
                    'tournament_id' => $tournament->id,
                    'category_name' => $mapping['name'],
                    'category_type' => $mapping['enum_value'],
                    'status' => 'open',
                ]
            );
            $categoryIds[] = $category->id;
        }
    }
}
