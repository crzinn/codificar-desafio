<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowRight, Clock3, UserRound } from '@lucide/vue';
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

const statusForm = useForm<{ status: TicketStatus | null }>({
    status: props.ticket.next_status,
});

const priorityLabel =
    props.priorities.find(
        (priority) => priority.value === props.ticket.priority,
    )?.label ?? props.ticket.priority;

const nextStatusLabel =
    props.statuses.find((status) => status.value === props.ticket.next_status)
        ?.label ?? null;

const priorityClasses: Record<TicketPriority, string> = {
    LOW: 'bg-emerald-500',
    MEDIUM: 'bg-amber-500',
    HIGH: 'bg-red-500',
};

const advanceStatus = (): void => {
    if (!statusForm.status) {
        return;
    }

    statusForm.patch(`/tickets/${props.ticket.id}/status`, {
        preserveScroll: true,
    });
};

const formatDate = (value: string): string =>
    new Intl.DateTimeFormat('pt-BR', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value));
</script>

<template>
    <article class="border border-slate-300 bg-white p-2.5">
        <div class="flex items-center justify-between gap-3 text-[11px]">
            <span class="font-semibold text-slate-500">#{{ ticket.id }}</span>
            <span class="flex items-center gap-1.5 text-slate-600">
                <span
                    class="size-1.5"
                    :class="priorityClasses[ticket.priority]"
                />
                {{ priorityLabel }}
            </span>
        </div>

        <Link
            :href="`/tickets/${ticket.id}`"
            class="mt-1 block text-[13px] leading-4.5 font-semibold text-slate-900 hover:text-sky-700 hover:underline"
        >
            {{ ticket.title }}
        </Link>

        <p class="mt-1 line-clamp-2 text-xs leading-4 text-slate-600">
            {{ ticket.description }}
        </p>

        <div
            class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 border-t border-slate-100 pt-1.5 text-[11px] text-slate-500"
        >
            <p class="flex min-w-0 items-center gap-1.5">
                <UserRound class="size-3 shrink-0" />
                {{ ticket.responsible.name }}
            </p>
            <p class="flex items-center gap-1.5">
                <Clock3 class="size-3 shrink-0" />
                {{ formatDate(ticket.opened_at) }}
            </p>
        </div>

        <div
            v-if="ticket.next_status && nextStatusLabel"
            class="mt-1.5 flex justify-end"
        >
            <button
                type="button"
                :disabled="statusForm.processing"
                class="inline-flex items-center gap-1 rounded-sm border border-slate-300 bg-slate-50 px-2 py-1 text-[11px] font-medium text-slate-700 hover:border-sky-400 hover:bg-sky-50 hover:text-sky-700 disabled:opacity-60"
                @click="advanceStatus"
            >
                {{
                    statusForm.processing
                        ? 'Atualizando...'
                        : `Mover para ${nextStatusLabel.toLowerCase()}`
                }}
                <ArrowRight class="size-3" />
            </button>
        </div>
        <p v-if="statusForm.errors.status" class="mt-2 text-xs text-red-600">
            {{ statusForm.errors.status }}
        </p>
    </article>
</template>
