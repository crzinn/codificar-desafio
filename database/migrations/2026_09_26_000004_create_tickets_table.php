<?php

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('requester_name');
            $table->string('priority', 16)->default(TicketPriority::MEDIUM->value)->index();
            $table->string('status', 16)->default(TicketStatus::OPEN->value)->index();
            $table->foreignId('responsible_id')
                ->constrained()
                ->restrictOnDelete();
            $table->timestampTz('opened_at')->index();
            $table->timestamps();

            $table->index(['responsible_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
