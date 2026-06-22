<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AboutController extends Controller
{
    /**
     * TOP画面(MOT2の紹介ページ)を表示
     */
    public function index()
    {
        return view('about/index');
    }
}
