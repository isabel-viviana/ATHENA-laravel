<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ExamController extends Controller
{
    private function fetchDataFromApi($url)
    {
        $response = Http::get($url);

        return $response->json();
    }

    public function exams()
    {
        $url = env('URL_SERVER_API');
        $exams = $this->fetchDataFromApi($url . '/admin/exams');

        return view('exams', compact('exams'));
    }
}
