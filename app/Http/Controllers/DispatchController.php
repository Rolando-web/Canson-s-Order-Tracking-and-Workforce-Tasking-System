<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DispatchController extends Controller
{
    public function index()
    {
        return view('pages.dispatch');
    }
}
