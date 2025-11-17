<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function show($slug)
    {
        $article = News::where('slug', $slug)->firstOrFail();
        $relatedNews = News::where('id', '!=', $article->id)->latest()->take(3)->get();
        
        return view('front.news-detail', compact('article', 'relatedNews'));
    }
}
