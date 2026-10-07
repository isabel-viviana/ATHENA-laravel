<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AdminDashboardController extends Controller
{
    private function fetchDataFromApi($url)
    {
        $response = Http::get($url);

        return $response->json();
    }

    public function dashboardAdmin()
    {
        $url = env('URL_SERVER_API');
        $data = $this->fetchDataFromApi($url . '/admin/dashboard');

        return view('dashboard_admin', compact('data'));
    }

    public function dashboardSuper()
    {
        $url = env('URL_SERVER_API');
        $data = $this->fetchDataFromApi($url . '/admin/dashboard-super');

        return view('dashboard_super', compact('data'));
    }

    public function dashboardSupport()
    {
        $url = env('URL_SERVER_API');
        $data = $this->fetchDataFromApi($url . '/admin/dashboard-support');

        return view('dashboard_support', compact('data'));
    }
}
