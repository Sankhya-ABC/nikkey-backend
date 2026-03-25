<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [];

    protected function schedule(Schedule $schedule): void
    {
        // ============================================================
        // DADOS OPERACIONAIS — atualizam com frequência
        // ============================================================

        // Ordens de Serviço: core do sistema, atualiza a cada 15min
        $schedule->command('sankhya:buscar-os')
            ->everyFifteenMinutes()
            ->withoutOverlapping(10)
            ->onOneServer()
            ->runInBackground();

        // Dependentes das OSs
        $schedule->command('sankhya:buscar-previsoes-execucao')
            ->everyFifteenMinutes()
            ->withoutOverlapping(10)
            ->onOneServer()
            ->runInBackground();

        $schedule->command('sankhya:buscar-os-ambientes')
            ->everyThirtyMinutes()
            ->withoutOverlapping(10)
            ->onOneServer()
            ->runInBackground();

        $schedule->command('sankhya:buscar-produtos-utilizados')
            ->everyThirtyMinutes()
            ->withoutOverlapping(10)
            ->onOneServer()
            ->runInBackground();

        $schedule->command('sankhya:buscar-produtos-previstos')
            ->everyThirtyMinutes()
            ->withoutOverlapping(10)
            ->onOneServer()
            ->runInBackground();

        // ============================================================
        // DADOS SEMI-ESTÁTICOS — atualizam a cada hora
        // ============================================================

        $schedule->command('sankhya:buscar-clientes')
            ->hourly()
            ->withoutOverlapping(20)
            ->onOneServer()
            ->runInBackground();

        $schedule->command('sankhya:buscar-evidencias-pragas')
            ->hourly()
            ->withoutOverlapping(20)
            ->onOneServer()
            ->runInBackground();

        // ============================================================
        // TABELAS DE CATÁLOGO — atualizam 1x por dia (madrugada)
        // Espaçadas de 5 em 5 minutos para não sobrecarregar a API
        // ============================================================

        $schedule->command('sankhya:buscar-tipo-equipamento')
            ->dailyAt('03:00')->withoutOverlapping()->onOneServer();

        $schedule->command('sankhya:buscar-tecnica-execucao')
            ->dailyAt('03:05')->withoutOverlapping()->onOneServer();

        $schedule->command('sankhya:buscar-servicos')
            ->dailyAt('03:10')->withoutOverlapping()->onOneServer();

        $schedule->command('sankhya:buscar-produtos')
            ->dailyAt('03:15')->withoutOverlapping()->onOneServer();

        $schedule->command('sankhya:buscar-pragas')
            ->dailyAt('03:20')->withoutOverlapping()->onOneServer();

        $schedule->command('sankhya:buscar-metodologias')
            ->dailyAt('03:25')->withoutOverlapping()->onOneServer();

        $schedule->command('sankhya:buscar-tipos-evidencia')
            ->dailyAt('03:30')->withoutOverlapping()->onOneServer();

        $schedule->command('sankhya:buscar-ambientes')
            ->dailyAt('03:35')->withoutOverlapping()->onOneServer();

        // ============================================================
        // SYNC COMPLETO — 1x por dia como garantia de consistência
        // Roda às 02:00 antes dos catálogos das 03:00
        // ============================================================

        $schedule->command('sankhya:sync-all')
            ->dailyAt('02:00')
            ->withoutOverlapping(60)
            ->onOneServer();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}