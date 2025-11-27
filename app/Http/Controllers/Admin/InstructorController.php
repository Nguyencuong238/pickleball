<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Instructor;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InstructorController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $instructors = Instructor::with('province')->latest()->paginate(5);
        return view('admin.instructors.index', compact('instructors'));
    }

    public function create()
    {
        $provinces = Province::all();
        return view('admin.instructors.create', compact('provinces'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'experience' => 'nullable|string',
            'ward' => 'nullable|string|max:255',
            'province_id' => 'nullable|exists:provinces,id',
        ]);

        $data = $request->only('name', 'experience', 'ward', 'province_id');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('instructors', 'public');
        }

        Instructor::create($data);

        return redirect()->route('admin.instructors.index')->with('success', 'Tạo giảng viên thành công.');
    }

    public function edit(Instructor $instructor)
    {
        $provinces = Province::all();
        return view('admin.instructors.edit', compact('instructor', 'provinces'));
    }

    public function update(Request $request, Instructor $instructor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'experience' => 'nullable|string',
            'ward' => 'nullable|string|max:255',
            'province_id' => 'nullable|exists:provinces,id',
        ]);

        $data = $request->only('name', 'experience', 'ward', 'province_id');

        if ($request->hasFile('image')) {
            if ($instructor->image) {
                Storage::disk('public')->delete($instructor->image);
            }
            $data['image'] = $request->file('image')->store('instructors', 'public');
        }

        $instructor->update($data);

        return redirect()->route('admin.instructors.index')->with('success', 'Cập nhật giảng viên thành công.');
    }

    public function destroy(Instructor $instructor)
    {
        if ($instructor->image) {
            Storage::disk('public')->delete($instructor->image);
        }

        $instructor->delete();

        return redirect()->route('admin.instructors.index')->with('success', 'Xóa giảng viên thành công.');
    }
}
