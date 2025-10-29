<?php

namespace App\Services\Sankhya;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class AuthSankhya
{
    public function login()
    {
        try {
            $client = new Client();

            $response = $client->request('POST', env('SNK_HOST') . '/authenticate', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => env('SNK_CLIENT_ID'),
                    'client_secret' => env('SNK_CLIENT_SECRET'),
                ],
                'headers' => [
                    'X-Token' => env('SNK_X_TOKEN'),
                    'Accept' => 'application/json',
                ],
                'verify' => false,
            ]);

            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            if (!isset($data['access_token'])) {
                throw new \Exception('Token de acesso não retornado pela API Sankhya.');
            }

            return $data['access_token'];

        } catch (RequestException $e) {
            \Log::error('Erro ao autenticar Sankhya: ' . $e->getMessage());
            if ($e->hasResponse()) {
                \Log::error('Resposta da API: ' . $e->getResponse()->getBody()->getContents());
            }
            return null;
        }
    }
}
