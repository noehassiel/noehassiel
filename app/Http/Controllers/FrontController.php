<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Resend\Laravel\Facades\Resend;

class FrontController extends Controller
{
    public function index()
    {

        Resend::emails()->send([
            'from' => 'noehassiel <no-reply@noehassiel.com>',
            'to' => 'hello@noehassiel.com',
            'subject' => 'hello world',
            'text' => 'hello world',
        ]);

        return view('welcome');
    }
}
