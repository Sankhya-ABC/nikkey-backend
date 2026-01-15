<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SankhyaSyncAll extends Command
{
    protected $signature = 'sankhya:sync-all';
    protected $description = 'Executa todas as sincronizações da Sankhya.';

    public function handle()
    {
        $this->call('sankhya:buscar-tipo-equipamento');
        $this->call('sankhya:buscar-tecnica-execucao');
        $this->call('sankhya:buscar-servicos');
        $this->call('sankhya:buscar-produtos');
        $this->call('sankhya:buscar-pragas');
        $this->call('sankhya:buscar-metodologias');
        $this->call('sankhya:buscar-clientes');
        $this->call('sankhya:buscar-tipos-evidencia');
        $this->call('sankhya:buscar-ambientes');

        $this->call('sankhya:buscar-os');
        $this->call('sankhya:buscar-previsoes-execucao');
        $this->call('sankhya:buscar-os-ambientes');

        $this->call('sankhya:buscar-produtos-previstos');
        $this->call('sankhya:buscar-produtos-utilizados');
        $this->call('sankhya:buscar-evidencias-pragas');

        $this->info('Sincronização completa!');
    }
}
