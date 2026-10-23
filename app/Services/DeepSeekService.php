<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Cliente da API DeepSeek (endpoint compatível com /chat/completions).
 */
class DeepSeekService
{
    public function __construct(
        protected string $apiKey,
        protected string $baseUrl = 'https://api.deepseek.com',
        protected string $model = 'deepseek-chat',
        protected int $timeout = 90,
    ) {
        if ($this->apiKey === '') {
            throw new RuntimeException('Chave da API DeepSeek não configurada. Defina DEEPSEEK_API_KEY no .env.');
        }
    }

    public static function fromConfig(): self
    {
        return new static(
            (string) config('services.deepseek.api_key'),
            (string) config('services.deepseek.base_url'),
            (string) config('services.deepseek.model'),
            (int) config('services.deepseek.timeout'),
        );
    }

    public static function configured(): bool
    {
        return filled(config('services.deepseek.api_key'));
    }

    /**
     * Envia a conversa para o DeepSeek e devolve o texto e o uso de tokens.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{texto: string, usage: array|null}
     */
    public function chat(array $messages): array
    {
        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => 8192,
            'temperature' => 0.7,
            'stream' => false,
            'thinking' => ['type' => 'disabled'],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ])
                ->timeout($this->timeout)
                ->retry(2, 2000)
                ->post(rtrim($this->baseUrl, '/').'/chat/completions', $payload);
        } catch (ConnectionException $e) {
            throw new RuntimeException('Erro de conexão com a API DeepSeek: '.$e->getMessage());
        }

        if ($response->failed()) {
            $msg = $response->json('error.message') ?? 'Erro HTTP '.$response->status();

            throw new RuntimeException('Erro da API DeepSeek: '.$msg);
        }

        $data = $response->json();
        $texto = (string) ($data['choices'][0]['message']['content'] ?? '');
        $usage = $data['usage'] ?? null;

        if ($texto === '') {
            throw new RuntimeException('A API DeepSeek retornou uma resposta vazia.');
        }

        return ['texto' => $texto, 'usage' => $usage];
    }
}
