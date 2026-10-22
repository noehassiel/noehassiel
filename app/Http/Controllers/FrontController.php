<?php

namespace App\Http\Controllers;

use App\Services\GitHubContributions;
use Illuminate\View\View;

class FrontController extends Controller
{
    public function index(GitHubContributions $contributions): View
    {
        return view('welcome', ['contributions' => $contributions->recent()]);
    }
}
