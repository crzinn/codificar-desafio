<script setup lang="ts">
import TicketCard from '@/components/tickets/TicketCard.vue';
import type {
    SelectOption,
    StatusOption,
    Ticket,
    TicketPriority,
    TicketStatus,
} from '@/types';

defineProps<{
    status: TicketStatus;
    title: string;
    tickets: Ticket[];
    priorities: SelectOption<TicketPriority>[];
    statuses: StatusOption[];
}>();

const headerClasses: Record<TicketStatus, string> = {
    OPEN: 'border-amber-600 bg-amber-500',
    IN_PROGRESS: 'border-green-700 bg-green-600',
    DONE: 'border-sky-700 bg-sky-600',
};
</script>

<template>
    <section
        class="min-w-0 overflow-hidden border border-slate-300 bg-[#e8f1f3]"
    >
        <header
            class="flex items-center justify-between gap-3 border-b px-2.5 py-1.5 text-white"
            :class="headerClasses[status]"
        >
            <h2 class="text-xs font-semibold">{{ title }}</h2>
            <span class="text-xs font-medium">({{ tickets.length }})</span>
        </header>

        <div class="space-y-1.5 p-1.5">
            <TicketCard
                v-for="ticket in tickets"
                :key="ticket.id"
                :ticket="ticket"
                :priorities="priorities"
                :statuses="statuses"
            />
            <div
                v-if="tickets.length === 0"
                class="border border-dashed border-slate-300 bg-white p-4 text-center text-xs text-slate-500"
            >
                Nenhum chamado
            </div>
        </div>
    </section>
</template>
