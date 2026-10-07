<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SubscriptionController extends Controller
{
    private function fetchDataFromApi($url)
    {
        $response = Http::get($url);

        return $response->json();
    }

    public function subscription()
    {
        $url = env('URL_SERVER_API');
        $subscription = $this->fetchDataFromApi($url . '/subscription');

        return view('subscription', compact('subscription'));
    }

    public function payments()
    {
        $url = env('URL_SERVER_API');
        $payments = $this->fetchDataFromApi($url . '/payments');

        return view('payments', compact('payments'));
    }
}
