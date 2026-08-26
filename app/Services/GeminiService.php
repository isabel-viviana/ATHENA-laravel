<?php

namespace App\Services;

use Gemini;
use Illuminate\Support\Facades\Http;
use Throwable;
use RuntimeException;

class GeminiService
{
    /**
     * Envía un prompt a Gemini Flash y retorna el texto generado.
     * Sanitiza activamente cualquier excepción para evitar exponer credenciales.
     */
    public function generateText(string $prompt): string
    {
        $apiKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY');
        $model = config('services.gemini.model', 'gemini-2.5-flash');

        if (empty($apiKey)) {
            throw new RuntimeException('La clave GEMINI_API_KEY no se encuentra configurada en el archivo de entorno.');
        }

        try {
            // 1. Utilizar el SDK oficial google-gemini-php con el modelo gemini-2.5-flash
            if (class_exists(Gemini::class)) {
                try {
                    $client = Gemini::client($apiKey);
                    $result = $client->generativeModel($model)->generateContent($prompt);
                    $text = $result->text();
                    if (!empty($text)) {
                        return trim($text);
                    }
                } catch (Throwable $e) {
                    // Fallback mediante el método de conveniencia geminiFlash()
                    try {
                        $client = Gemini::client($apiKey);
                        $result = $client->geminiFlash()->generateContent($prompt);
                        $text = $result->text();
                        if (!empty($text)) {
                            return trim($text);
                        }
                    } catch (Throwable $e2) {
                        // Continuar a fallback HTTP si se requiere
                    }
                }
            }

            // 2. Fallback HTTP directo a la API de Google Generative AI usando el modelo configurado
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $apiKey;
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $generatedText = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if ($generatedText) {
                    return trim($generatedText);
                }
            }

            $statusCode = $response->status();
            throw new RuntimeException("Error en la solicitud a Gemini API (HTTP {$statusCode}).");

        } catch (Throwable $e) {
            // Sanitización estricta de cualquier mensaje para evitar filtración de claves
            $cleanMessage = preg_replace('/key=[a-zA-Z0-9_\-]+/', 'key=***HIDDEN***', $e->getMessage());
            if (!empty($apiKey)) {
                $cleanMessage = str_replace($apiKey, '***HIDDEN***', $cleanMessage);
            }

            throw new RuntimeException("Fallo en la comunicación con Gemini: " . $cleanMessage, 500);
        }
    }
}
