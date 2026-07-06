<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SankhyaSyncAll extends Command
{
    protected $signature = 'sankhya:sync-all';
    protected $description = 'Executa todas as sincronizações da Sankhya.';

    public function handle()
    {
        $this->call('sankhya:sync-tipos-equipamento');
        $this->call('sankhya:sync-tecnicas-execucao');
        $this->call('sankhya:sync-servicos');
        $this->call('sankhya:sync-produtos');
        $this->call('sankhya:sync-pragas');
        $this->call('sankhya:sync-metodologias');
        $this->call('sankhya:sync-clientes');
        $this->call('sankhya:sync-tipos-evidencia');
        $this->call('sankhya:sync-ambientes');

        $this->call('sankhya:sync-os');
        $this->call('sankhya:sync-previsoes-execucao');
        $this->call('sankhya:sync-os-ambientes');

        $this->call('sankhya:sync-produtos-previstos');
        $this->call('sankhya:sync-produtos-utilizados');
        $this->call('sankhya:sync-evidencias-pragas');

        $this->call('sankhya:sync-tipos-nao-conformidade');
        $this->call('sankhya:sync-nao-conformidades');
        $this->call('sankhya:sync-pontos-monitoramento');

        $this->call('sankhya:sync-empresas');
        $this->call('sankhya:sync-tecnicos');
        $this->call('sankhya:sync-certificados');

        $this->info('Sincronizacao completa!');
    }
}

