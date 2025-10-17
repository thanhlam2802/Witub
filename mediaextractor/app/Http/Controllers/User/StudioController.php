<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudioController extends Controller
{

    public function index(): View
    {

        return view('layouts.service_layout');
    }
}
