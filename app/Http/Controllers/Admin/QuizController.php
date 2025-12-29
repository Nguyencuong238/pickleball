<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index(Request $request)
    {
        $query = Quiz::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('question', 'like', "%{$search}%");
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $quizzes = $query->latest()->paginate(10)->appends($request->query());
        $categories = Quiz::select('category')->distinct()->whereNotNull('category')->pluck('category');

        return view('admin.quizzes.index', compact('quizzes', 'categories'));
    }

    public function create()
    {
        $categories = Quiz::select('category')->distinct()->whereNotNull('category')->pluck('category');
        return view('admin.quizzes.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'question' => 'required|string',
            'option_a' => 'required|string',
            'option_b' => 'required|string',
            'option_c' => 'required|string',
            'option_d' => 'required|string',
            'correct_answer' => 'required|in:a,b,c,d',
            'explanation' => 'nullable|string',
            'category' => 'nullable|string',
            'difficulty' => 'required|integer|between:1,3',
            'description' => 'nullable|string',
        ]);

        $options = [
            'a' => $request->input('option_a'),
            'b' => $request->input('option_b'),
            'c' => $request->input('option_c'),
            'd' => $request->input('option_d'),
        ];

        Quiz::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'question' => $request->input('question'),
            'options' => $options,
            'correct_answer' => $request->input('correct_answer'),
            'explanation' => $request->input('explanation'),
            'category' => $request->input('category'),
            'difficulty' => $request->input('difficulty'),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.quizzes.index')->with('success', 'Tạo câu hỏi quiz thành công.');
    }

    public function edit(Quiz $quiz)
    {
        $categories = Quiz::select('category')->distinct()->whereNotNull('category')->pluck('category');
        return view('admin.quizzes.edit', compact('quiz', 'categories'));
    }

    public function update(Request $request, Quiz $quiz)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'question' => 'required|string',
            'option_a' => 'required|string',
            'option_b' => 'required|string',
            'option_c' => 'required|string',
            'option_d' => 'required|string',
            'correct_answer' => 'required|in:a,b,c,d',
            'explanation' => 'nullable|string',
            'category' => 'nullable|string',
            'difficulty' => 'required|integer|between:1,3',
            'description' => 'nullable|string',
        ]);

        $options = [
            'a' => $request->input('option_a'),
            'b' => $request->input('option_b'),
            'c' => $request->input('option_c'),
            'd' => $request->input('option_d'),
        ];

        $quiz->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'question' => $request->input('question'),
            'options' => $options,
            'correct_answer' => $request->input('correct_answer'),
            'explanation' => $request->input('explanation'),
            'category' => $request->input('category'),
            'difficulty' => $request->input('difficulty'),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.quizzes.index')->with('success', 'Cập nhật câu hỏi thành công.');
    }

    public function destroy(Quiz $quiz)
    {
        $quiz->delete();
        return redirect()->route('admin.quizzes.index')->with('success', 'Xóa câu hỏi thành công.');
    }

    public function show(Quiz $quiz)
    {
        return view('admin.quizzes.show', compact('quiz'));
    }
}
