<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $videos = Video::with('category')->latest()->paginate(10);
        return view('admin.videos.index', compact('videos'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.videos.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'description' => 'nullable|string',
            'video_link' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $data = $request->only('name', 'description', 'video_link', 'category_id');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('videos', 'public');
        }

        Video::create($data);

        return redirect()->route('admin.videos.index')->with('success', 'Tạo video thành công.');
    }

    public function edit(Video $video)
    {
        $categories = Category::all();
        return view('admin.videos.edit', compact('video', 'categories'));
    }

    public function update(Request $request, Video $video)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'description' => 'nullable|string',
            'video_link' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $data = $request->only('name', 'description', 'video_link', 'category_id');

        if ($request->hasFile('image')) {
            if ($video->image) {
                Storage::disk('public')->delete($video->image);
            }
            $data['image'] = $request->file('image')->store('videos', 'public');
        }

        $video->update($data);

        return redirect()->route('admin.videos.index')->with('success', 'Cập nhật video thành công.');
    }

    public function destroy(Video $video)
    {
        if ($video->image) {
            Storage::disk('public')->delete($video->image);
        }

        $video->delete();

        return redirect()->route('admin.videos.index')->with('success', 'Xóa video thành công.');
    }
}
