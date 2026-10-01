<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import type {
    AssignmentMode,
    Responsible,
    SelectOption,
    Ticket,
    TicketPriority,
} from '@/types';

const props = defineProps<{
    ticket?: Ticket;
    responsibles: Responsible[];
    priorities: SelectOption<TicketPriority>[];
}>();

const form = useForm({
    title: props.ticket?.title ?? '',
    description: props.ticket?.description ?? '',
    requester_name: props.ticket?.requester_name ?? '',
    priority: props.ticket?.priority ?? ('MEDIUM' as TicketPriority),
    assignment_mode: (props.ticket ? 'manual' : 'automatic') as AssignmentMode,
    responsible_id: props.ticket?.responsible_id.toString() ?? '',
});

const submit = (): void => {
    if (props.ticket) {
        form.put(`/tickets/${props.ticket.id}`);
        return;
    }

    form.post('/tickets');
};
</script>

<template>
    <form class="space-y-4" @submit.prevent="submit">
        <div class="grid gap-3.5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label
                    for="title"
                    class="mb-1 block text-xs font-semibold text-slate-700"
                >
                    Título
                </label>
                <input
                    id="title"
                    v-model="form.title"
                    type="text"
                    maxlength="255"
                    autofocus
                    class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2.5 text-sm outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-200"
                />
                <InputError class="mt-1" :message="form.errors.title" />
            </div>

            <div class="sm:col-span-2">
                <label
                    for="description"
                    class="mb-1 block text-xs font-semibold text-slate-700"
                >
                    Descrição
                </label>
                <textarea
                    id="description"
                    v-model="form.description"
                    rows="5"
                    maxlength="5000"
                    class="w-full resize-y rounded-sm border border-slate-300 bg-white px-2.5 py-2 text-sm outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-200"
                />
                <InputError class="mt-1" :message="form.errors.description" />
            </div>

            <div>
                <label
                    for="requester_name"
                    class="mb-1 block text-xs font-semibold text-slate-700"
                >
                    Solicitante
                </label>
                <input
                    id="requester_name"
                    v-model="form.requester_name"
                    type="text"
                    maxlength="255"
                    class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2.5 text-sm outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-200"
                />
                <InputError
                    class="mt-1"
                    :message="form.errors.requester_name"
                />
            </div>

            <div>
                <label
                    for="priority"
                    class="mb-1 block text-xs font-semibold text-slate-700"
                >
                    Prioridade
                </label>
                <select
                    id="priority"
                    v-model="form.priority"
                    class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2.5 text-sm outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-200"
                >
                    <option
                        v-for="priority in priorities"
                        :key="priority.value"
                        :value="priority.value"
                    >
                        {{ priority.label }}
                    </option>
                </select>
                <InputError class="mt-1" :message="form.errors.priority" />
            </div>

            <fieldset v-if="!ticket" class="sm:col-span-2">
                <legend class="text-xs font-semibold text-slate-700">
                    Atribuição
                </legend>
                <p class="mt-0.5 text-xs text-slate-500">
                    Defina como o responsável será escolhido.
                </p>
                <div
                    class="mt-1.5 grid gap-px border border-slate-300 bg-slate-300 sm:grid-cols-2"
                >
                    <label
                        class="flex cursor-pointer items-start gap-2 bg-slate-50 p-2.5 hover:bg-white"
                    >
                        <input
                            v-model="form.assignment_mode"
                            type="radio"
                            value="automatic"
                            class="mt-0.5 size-3.5 accent-sky-600"
                        />
                        <span>
                            <span class="block text-xs font-semibold">
                                Automática
                            </span>
                            <span
                                class="mt-0.5 block text-[11px] text-slate-500"
                            >
                                Escolhe quem tem menos chamados em aberto.
                            </span>
                        </span>
                    </label>
                    <label
                        class="flex cursor-pointer items-start gap-2 bg-slate-50 p-2.5 hover:bg-white"
                    >
                        <input
                            v-model="form.assignment_mode"
                            type="radio"
                            value="manual"
                            class="mt-0.5 size-3.5 accent-sky-600"
                        />
                        <span>
                            <span class="block text-xs font-semibold"
                                >Manual</span
                            >
                            <span
                                class="mt-0.5 block text-[11px] text-slate-500"
                            >
                                Você seleciona o responsável.
                            </span>
                        </span>
                    </label>
                </div>
                <InputError
                    class="mt-1"
                    :message="form.errors.assignment_mode"
                />
            </fieldset>

            <div v-if="ticket || form.assignment_mode === 'manual'">
                <label
                    for="responsible_id"
                    class="mb-1 block text-xs font-semibold text-slate-700"
                >
                    Responsável
                </label>
                <select
                    id="responsible_id"
                    v-model="form.responsible_id"
                    class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2.5 text-sm outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-200"
                >
                    <option value="" disabled>Selecione um responsável</option>
                    <option
                        v-for="responsible in responsibles"
                        :key="responsible.id"
                        :value="responsible.id"
                    >
                        {{ responsible.name }}
                    </option>
                </select>
                <InputError
                    class="mt-1"
                    :message="form.errors.responsible_id"
                />
            </div>
        </div>

        <div
            class="flex items-center justify-end gap-1.5 border-t border-slate-200 pt-3.5"
        >
            <Link
                :href="ticket ? `/tickets/${ticket.id}` : '/tickets'"
                class="inline-flex h-8 items-center rounded-sm border border-slate-300 bg-white px-3 text-xs font-medium text-slate-700 hover:bg-slate-100"
            >
                Cancelar
            </Link>
            <button
                type="submit"
                :disabled="form.processing"
                class="h-8 rounded-sm border border-sky-700 bg-sky-600 px-3.5 text-xs font-semibold text-white hover:bg-sky-700 disabled:cursor-not-allowed disabled:opacity-60"
            >
                {{ form.processing ? 'Salvando...' : 'Salvar chamado' }}
            </button>
        </div>
    </form>
</template>
