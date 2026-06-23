<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class AboutController extends Controller
{
    /**
     * TOP画面(MOT2の紹介ページ)を表示
     *
     * @return View
     */
    public function index(): View
    {
        return view('about/index');
    }
}
