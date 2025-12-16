<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NewsResource;
use App\Models\News;
use App\Models\Category;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * Get all news
     */
    public function index(Request $request)
    {
        $query = News::query();

        // Search by title or description
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Filter by category/type
        if ($request->filled('category')) {
            $query->where('id', $request->category);
        }

        // Pagination
        $per_page = $request->get('per_page', 10);
        $news = $query->paginate($per_page);

        return NewsResource::collection($news)
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Get news details
     */
    public function show($id)
    {
        $news = News::find($id);

        if (!$news) {
            return response()->json([
                'success' => false,
                'message' => 'News not found',
            ], 404);
        }

        return new NewsResource($news);
    }

    /**
     * Get all news categories
     */
    public function categories()
    {
        $categories = Category::all();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }
}
