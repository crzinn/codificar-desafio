<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import TicketBoard from '@/components/tickets/TicketBoard.vue';
import type {
    Responsible,
    SelectOption,
    StatusOption,
    Ticket,
    TicketPriority,
    TicketStatus,
} from '@/types';

const props = defineProps<{
    tickets: Ticket[];
    filters: {
        status?: TicketStatus;
        priority?: TicketPriority;
        responsible_id?: number;
    };
    responsibles: Responsible[];
    priorities: SelectOption<TicketPriority>[];
    statuses: StatusOption[];
}>();

const form = useForm({
    status: props.filters.status ?? '',
    priority: props.filters.priority ?? '',
    responsible_id: props.filters.responsible_id?.toString() ?? '',
});

const applyFilters = (): void => {
    form.get('/tickets', {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = (): void => {
    form.defaults({ status: '', priority: '', responsible_id: '' }).reset();
    applyFilters();
};
</script>

<template>
    <Head title="Chamados" />

    <div class="space-y-3">
        <div>
            <h1 class="text-xl font-medium text-slate-800">Chamados</h1>
            <p class="mt-0.5 text-xs text-slate-500">
                {{ tickets.length }}
                {{
                    tickets.length === 1
                        ? 'chamado exibido'
                        : 'chamados exibidos'
                }}
            </p>
        </div>

        <form
            class="flex flex-wrap items-end gap-2 border border-slate-300 bg-white p-2.5"
            @submit.prevent="applyFilters"
        >
            <label
                class="flex min-w-44 flex-1 items-center gap-2 text-xs font-medium text-slate-600 sm:flex-none"
            >
                <span>Status</span>
                <select
                    v-model="form.status"
                    class="h-8 min-w-0 flex-1 rounded-sm border border-slate-300 bg-white px-2 text-xs text-slate-900 outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-200 sm:w-36"
                >
                    <option value="">Todos</option>
                    <option
                        v-for="status in statuses"
                        :key="status.value"
                        :value="status.value"
                    >
                        {{ status.label }}
                    </option>
                </select>
            </label>
            <label
                class="flex min-w-52 flex-1 items-center gap-2 text-xs font-medium text-slate-600 sm:flex-none"
            >
                <span>Prioridade</span>
                <select
                    v-model="form.priority"
                    class="h-8 min-w-0 flex-1 rounded-sm border border-slate-300 bg-white px-2 text-xs text-slate-900 outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-200 sm:w-36"
                >
                    <option value="">Todas</option>
                    <option
                        v-for="priority in priorities"
                        :key="priority.value"
                        :value="priority.value"
                    >
                        {{ priority.label }}
                    </option>
                </select>
            </label>
            <label
                class="flex min-w-64 flex-1 items-center gap-2 text-xs font-medium text-slate-600 lg:flex-none"
            >
                <span>Responsável</span>
                <select
                    v-model="form.responsible_id"
                    class="h-8 min-w-0 flex-1 rounded-sm border border-slate-300 bg-white px-2 text-xs text-slate-900 outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-200 sm:w-44"
                >
                    <option value="">Todos</option>
                    <option
                        v-for="responsible in responsibles"
                        :key="responsible.id"
                        :value="responsible.id"
                    >
                        {{ responsible.name }}
                    </option>
                </select>
            </label>
            <div class="ml-auto flex gap-1.5">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex h-8 items-center justify-center rounded-sm border border-sky-700 bg-sky-600 px-4 text-xs font-semibold text-white hover:bg-sky-700 disabled:opacity-60"
                >
                    Filtrar
                </button>
                <button
                    type="button"
                    class="h-8 rounded-sm border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 hover:bg-slate-100"
                    @click="clearFilters"
                >
                    Limpar
                </button>
            </div>
        </form>

        <TicketBoard
            :tickets="tickets"
            :priorities="priorities"
            :statuses="statuses"
        />
    </div>
</template>
