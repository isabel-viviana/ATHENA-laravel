<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    private function fetchDataFromApi($url)
    {
        $response = Http::get($url);

        return $response->json();
    }

    public function dashboard()
    {
        $url = env('URL_SERVER_API');
        $data = $this->fetchDataFromApi($url . '/dashboard');

        return view('dashboard', compact('data'));
    }
}
