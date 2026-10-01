<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ChevronLeft, Pencil } from '@lucide/vue';
import type {
    SelectOption,
    StatusOption,
    Ticket,
    TicketPriority,
    TicketStatus,
} from '@/types';

const props = defineProps<{
    ticket: Ticket;
    priorities: SelectOption<TicketPriority>[];
    statuses: StatusOption[];
}>();

const priorityLabel =
    props.priorities.find(
        (priority) => priority.value === props.ticket.priority,
    )?.label ?? props.ticket.priority;

const statusLabel =
    props.statuses.find((status) => status.value === props.ticket.status)
        ?.label ?? props.ticket.status;

const priorityClasses: Record<TicketPriority, string> = {
    LOW: 'text-emerald-700',
    MEDIUM: 'text-amber-700',
    HIGH: 'text-red-700',
};

const statusClasses: Record<TicketStatus, string> = {
    OPEN: 'text-amber-700',
    IN_PROGRESS: 'text-green-700',
    DONE: 'text-sky-700',
};

const formatDate = (value: string): string =>
    new Intl.DateTimeFormat('pt-BR', {
        dateStyle: 'long',
        timeStyle: 'short',
    }).format(new Date(value));
</script>

<template>
    <Head :title="`Chamado #${ticket.id}`" />

    <div class="mx-auto max-w-5xl space-y-2">
        <div class="flex items-center justify-between gap-4">
            <Link
                href="/tickets"
                class="inline-flex items-center gap-1 text-xs font-medium text-slate-600 hover:text-sky-700"
            >
                <ChevronLeft class="size-3.5" />
                Voltar para chamados
            </Link>
            <Link
                :href="`/tickets/${ticket.id}/edit`"
                class="inline-flex h-8 items-center gap-1.5 rounded-sm border border-sky-700 bg-sky-600 px-3 text-xs font-semibold text-white hover:bg-sky-700"
            >
                <Pencil class="size-3.5" />
                Editar
            </Link>
        </div>

        <article class="overflow-hidden border border-slate-300 bg-white">
            <header
                class="flex flex-col gap-3 border-b border-slate-300 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5"
            >
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold text-slate-500">
                        Chamado #{{ ticket.id }}
                    </p>
                    <h1 class="mt-0.5 text-lg font-semibold text-slate-900">
                        {{ ticket.title }}
                    </h1>
                </div>
                <div
                    class="grid shrink-0 grid-cols-2 divide-x divide-slate-300 border border-slate-300 bg-white"
                >
                    <div class="min-w-28 px-3 py-1.5">
                        <p class="text-[10px] font-medium text-slate-500">
                            Status
                        </p>
                        <p
                            class="text-xs font-semibold"
                            :class="statusClasses[ticket.status]"
                        >
                            {{ statusLabel }}
                        </p>
                    </div>
                    <div class="min-w-28 px-3 py-1.5">
                        <p class="text-[10px] font-medium text-slate-500">
                            Prioridade
                        </p>
                        <p
                            class="text-xs font-semibold"
                            :class="priorityClasses[ticket.priority]"
                        >
                            {{ priorityLabel }}
                        </p>
                    </div>
                </div>
            </header>

            <div class="grid md:grid-cols-[minmax(0,2fr)_minmax(15rem,1fr)]">
                <section class="px-4 py-4 sm:px-5">
                    <h2 class="text-xs font-semibold text-slate-700">
                        Descrição
                    </h2>
                    <p
                        class="mt-1.5 text-sm leading-5 whitespace-pre-wrap text-slate-700"
                    >
                        {{ ticket.description }}
                    </p>
                </section>

                <dl
                    class="divide-y divide-slate-200 border-t border-slate-300 bg-slate-50 px-4 md:border-t-0 md:border-l md:px-5"
                >
                    <div class="py-2.5">
                        <dt class="text-[11px] font-medium text-slate-500">
                            Solicitante
                        </dt>
                        <dd class="mt-0.5 text-xs font-semibold">
                            {{ ticket.requester_name }}
                        </dd>
                    </div>
                    <div class="py-2.5">
                        <dt class="text-[11px] font-medium text-slate-500">
                            Responsável
                        </dt>
                        <dd class="mt-0.5 text-xs font-semibold">
                            {{ ticket.responsible.name }}
                        </dd>
                    </div>
                    <div class="py-2.5">
                        <dt class="text-[11px] font-medium text-slate-500">
                            Aberto em
                        </dt>
                        <dd class="mt-0.5 text-xs font-semibold">
                            {{ formatDate(ticket.opened_at) }}
                        </dd>
                    </div>
                </dl>
            </div>
        </article>
    </div>
</template>
