<?php

namespace App\Http\Controllers;

use App\Models\Social;
use App\Models\Stadium;
use Illuminate\Http\Request;

class SocialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $socials = Social::where('user_id', auth()->id())
            ->with('stadium')
            ->latest()
            ->paginate();
        $stadiums = Stadium::where('user_id', auth()->id())->get();
        return view('home-yard.socials.index', compact('socials', 'stadiums'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'object' => 'nullable|string|max:255',
            'stadium_id' => 'required|exists:stadiums,id',
            'start_time' => 'required',
            'end_time' => 'required',
            'fee' => 'nullable|numeric|min:0',
            'max_participants' => 'nullable|integer|min:1',
            'days_of_week' => 'nullable|array'
        ]);

        $validated['days_of_week'] = $validated['days_of_week'] ?? [];
        $validated['user_id'] = auth()->id();

        Social::create($validated);

        return redirect()->route('homeyard.socials.index')->with('success', 'Sự kiện xã hội đã được tạo thành công');
    }

    /**
     * Display the specified resource.
     */
    public function show(Social $social)
    {

        // Return JSON for AJAX requests
        if (request()->ajax()) {
            return response()->json([
                'html' => view('home-yard.socials.__detailSocial', compact('social'))->render()
            ]);
        }

        // Return view for regular requests
        $athletes = $social->athletes()->paginate(15);
        return view('home-yard.socials.show', compact('social', 'athletes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Social $social)
    {
        $stadiums = Stadium::where('user_id', auth()->id())->get();
        return response()->json([
            'html' => view('home-yard.socials.__editSocial', compact('social', 'stadiums'))->render()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Social $social)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'object' => 'nullable|string|max:255',
            'stadium_id' => 'required|exists:stadiums,id',
            'start_time' => 'required',
            'end_time' => 'required',
            'fee' => 'nullable|numeric|min:0',
            'max_participants' => 'nullable|integer|min:1',
            'days_of_week' => 'nullable|array'
        ]);

        $validated['days_of_week'] = $validated['days_of_week'] ?? [];
        $social->update($validated);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sự kiện xã hội đã được cập nhật thành công'
            ]);
        }

        return redirect()->route('homeyard.socials.index')->with('success', 'Sự kiện xã hội đã được cập nhật thành công');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Social $social)
    {
        $social->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sự kiện xã hội đã được xóa thành công'
            ]);
        }

        return redirect()->route('homeyard.socials.index')
            ->with('success', 'Sự kiện xã hội đã được xóa thành công');
    }
}
