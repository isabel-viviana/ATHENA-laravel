<?php

namespace App\Http\Controllers\AiTutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiChatController extends Controller
{
    private function fetchDataFromApi($url)
    {
        $response = Http::get($url);

        return $response->json();
    }

    public function chat()
    {
        $url = env('URL_SERVER_API');
        $messages = $this->fetchDataFromApi($url . '/ai/chat');

        return view('chat', compact('messages'));
    }

    public function sendMessage(Request $request)
    {
        $data = $request->validate([
            'message' => ['required', 'string'],
        ]);

        $url = env('URL_SERVER_API');
        $response = Http::post($url . '/ai/chat/message', $data);

        return view('chat', ['response' => $response->json()]);
    }
}
