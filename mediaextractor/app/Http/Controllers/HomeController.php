<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\SettingService; // <-- IMPORT SERVICE

class HomeController extends Controller
{
    protected SettingService $settingService;


    public function __construct(SettingService $settingService)
    {

        $this->settingService = $settingService;
    }


    public function index(): View
    {

        $footer = $this->settingService->getFooter();


        return view('layouts.app', compact('footer'));
    }
}
