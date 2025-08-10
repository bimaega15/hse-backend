<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthenticationController extends Controller
{

    public function index(): View
    {
        return view('auth.authentication.login');
    }
}
