<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    //
    public function index()
    {
        return view('front.home');
    }

    public function booking()
    {
        return view('front.booking');
    }
    
    public function courts()
    {
        return view('front.courts');
    }

    public function courtsDetail()
    {
        return view('front.courts.courts_detail');
    }

    public function tournaments()
    {
        return view('front.tournaments');
    }
    
    public function tournamentsDetail()
    {
        return view('front.tournaments.tournaments_detail');
    }
    
    public function social()
    {
        return view('front.social_play');
    }

    public function news()
    {
        $news = News::paginate(6);
        return view('front.news', ['news' => $news]);
    }
}
