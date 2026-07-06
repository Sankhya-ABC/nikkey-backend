<?php

namespace App\Services\Sankhya;

use Illuminate\Support\Facades\Log;

class SankhyaAuthService
{
    public function login(): ?string
    {
        try {
            return app(SankhyaTokenService::class)->token();
        } catch (\Throwable $e) {
            Log::error('Erro ao autenticar Sankhya: ' . $e->getMessage());
            return null;
        }
    }
}

