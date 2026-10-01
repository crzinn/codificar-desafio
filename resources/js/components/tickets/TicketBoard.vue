<script setup lang="ts">
import { computed } from 'vue';
import TicketColumn from '@/components/tickets/TicketColumn.vue';
import type {
    SelectOption,
    StatusOption,
    Ticket,
    TicketPriority,
} from '@/types';

const props = defineProps<{
    tickets: Ticket[];
    priorities: SelectOption<TicketPriority>[];
    statuses: StatusOption[];
}>();

const columns = computed(() =>
    props.statuses.map((status) => ({
        status: status.value,
        title: status.board_label,
        tickets: props.tickets.filter(
            (ticket) => ticket.status === status.value,
        ),
    })),
);
</script>

<template>
    <div class="grid items-start gap-2.5 md:grid-cols-3">
        <TicketColumn
            v-for="column in columns"
            :key="column.status"
            :status="column.status"
            :title="column.title"
            :tickets="column.tickets"
            :priorities="priorities"
            :statuses="statuses"
        />
    </div>
</template>
