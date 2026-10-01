<?php

namespace Database\Seeders;

use App\Models\Responsible;
use Illuminate\Database\Seeder;

class ResponsibleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Arthur', 'Maria', 'João'] as $name) {
            Responsible::query()->updateOrCreate(
                ['name' => $name],
                ['active' => true],
            );
        }
    }
}
