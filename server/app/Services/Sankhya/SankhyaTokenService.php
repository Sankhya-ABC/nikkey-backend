<?php

namespace App\Services\Sankhya;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SankhyaTokenService
{
    private Client $client;

    private const RETRY_DELAYS = [8, 16, 32];

    public function __construct()
    {
        $this->client = new Client(['verify' => false, 'timeout' => 30]);
    }

    public function token(): string
    {
        // Token do sandbox expira em 300s; usamos 240s para margem de segurança
        $ttl = (int) (config('services.sankhya.token_ttl', 240));
        return Cache::remember('snk_token', $ttl, fn () => $this->authenticate());
    }

    public function forget(): void
    {
        Cache::forget('snk_token');
    }

    private function authenticate(): string
    {
        $attempts = 0;

        while (true) {
            try {
                $response = $this->client->post(config('services.sankhya.host') . '/authenticate', [
                    'form_params' => [
                        'grant_type'    => 'client_credentials',
                        'client_id'     => config('services.sankhya.client_id'),
                        'client_secret' => config('services.sankhya.client_secret'),
                    ],
                    'headers' => [
                        'X-Token' => config('services.sankhya.x_token'),
                        'Accept'  => 'application/json',
                    ],
                    'verify' => false,
                ]);

                $data = json_decode($response->getBody()->getContents(), true);

                if (empty($data['access_token'])) {
                    throw new \RuntimeException('Token de acesso não retornado pela API Sankhya.');
                }

                return $data['access_token'];

            } catch (\Throwable $e) {
                if ($attempts >= count(self::RETRY_DELAYS)) {
                    Log::error('Sankhya: falha na autenticação após ' . ($attempts + 1) . ' tentativas', [
                        'error' => $e->getMessage(),
                    ]);
                    throw new \RuntimeException('Falha ao autenticar no Sankhya: ' . $e->getMessage(), 0, $e);
                }

                $delay = self::RETRY_DELAYS[$attempts];
                Log::warning("Sankhya: tentativa " . ($attempts + 1) . " falhou, retry em {$delay}s", [
                    'error' => $e->getMessage(),
                ]);
                sleep($delay);
                $attempts++;
            }
        }
    }
}
