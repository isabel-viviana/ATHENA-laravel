<?php

namespace App\Http\Controllers\Ranking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RankingController extends Controller
{
    private function fetchDataFromApi($url)
    {
        $response = Http::get($url);

        return $response->json();
    }

    public function ranking()
    {
        $url = env('URL_SERVER_API');
        $ranking = $this->fetchDataFromApi($url . '/ranking');

        return view('ranking', compact('ranking'));
    }
}
