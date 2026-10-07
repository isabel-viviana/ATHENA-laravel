<?php

namespace App\Http\Controllers\Statistic;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class StatisticController extends Controller
{
    private function fetchDataFromApi($url)
    {
        $response = Http::get($url);

        return $response->json();
    }

    public function analysis()
    {
        $url = env('URL_SERVER_API');
        $analysis = $this->fetchDataFromApi($url . '/statistics/analysis');

        return view('analysis', compact('analysis'));
    }

    public function statistics()
    {
        $url = env('URL_SERVER_API');
        $statistics = $this->fetchDataFromApi($url . '/statistics');

        return view('statistics', compact('statistics'));
    }
}
