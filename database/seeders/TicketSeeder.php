<?php

namespace Database\Seeders;

use App\Models\Responsible;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        if (Ticket::query()->exists()) {
            return;
        }

        $arthur = Responsible::query()->where('name', 'Arthur')->firstOrFail();
        $maria = Responsible::query()->where('name', 'Maria')->firstOrFail();
        $joao = Responsible::query()->where('name', 'João')->firstOrFail();

        Ticket::factory()->for($arthur)->highPriority()->open()->create([
            'title' => 'Computador não inicializa',
            'requester_name' => 'Ana Souza',
            'opened_at' => now()->subDays(2),
        ]);
        Ticket::factory()->for($arthur)->lowPriority()->inProgress()->create([
            'title' => 'Instalação de novo monitor',
            'requester_name' => 'Carlos Lima',
            'opened_at' => now()->subDay(),
        ]);
        Ticket::factory()->for($maria)->mediumPriority()->open()->create([
            'title' => 'Impressora sem conexão',
            'requester_name' => 'Beatriz Alves',
            'opened_at' => now()->subHours(8),
        ]);
        Ticket::factory()->for($joao)->lowPriority()->done()->create([
            'title' => 'Troca de teclado',
            'requester_name' => 'Diego Rocha',
            'opened_at' => now()->subDays(3),
        ]);
    }
}
