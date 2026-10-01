<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ChevronLeft } from '@lucide/vue';
import TicketForm from '@/components/tickets/TicketForm.vue';
import type {
    Responsible,
    SelectOption,
    Ticket,
    TicketPriority,
} from '@/types';

defineProps<{
    ticket: Ticket;
    responsibles: Responsible[];
    priorities: SelectOption<TicketPriority>[];
}>();
</script>

<template>
    <Head :title="`Editar chamado #${ticket.id}`" />

    <div class="mx-auto max-w-4xl space-y-2">
        <Link
            :href="`/tickets/${ticket.id}`"
            class="inline-flex items-center gap-1 text-xs font-medium text-slate-600 hover:text-sky-700"
        >
            <ChevronLeft class="size-3.5" />
            Voltar para o chamado
        </Link>

        <div class="border border-slate-300 bg-white">
            <div
                class="border-b border-slate-300 bg-slate-50 px-4 py-2.5 sm:px-5"
            >
                <h1 class="text-lg font-medium text-slate-800">
                    Editar chamado #{{ ticket.id }}
                </h1>
                <p class="mt-0.5 text-xs text-slate-500">
                    Atualize os dados ou o responsável pelo atendimento.
                </p>
            </div>

            <div class="p-4 sm:p-5">
                <TicketForm
                    :ticket="ticket"
                    :responsibles="responsibles"
                    :priorities="priorities"
                />
            </div>
        </div>
    </div>
</template>
