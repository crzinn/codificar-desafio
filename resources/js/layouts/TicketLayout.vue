<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ClipboardList, Plus } from '@lucide/vue';
import { Toaster } from '@/components/ui/sonner';
</script>

<template>
    <div class="min-h-screen bg-[#eef7f8] text-slate-900">
        <header class="border-b border-slate-300 bg-white shadow-xs">
            <div
                class="mx-auto flex min-h-13 max-w-[1600px] items-center justify-between gap-4 px-3 sm:px-5"
            >
                <Link
                    href="/tickets"
                    class="flex items-center gap-2 font-semibold text-slate-800"
                >
                    <span
                        class="flex size-7 items-center justify-center rounded-sm bg-sky-600 text-white"
                    >
                        <ClipboardList class="size-4" />
                    </span>
                    <span class="text-sm">Central de Chamados</span>
                </Link>

                <nav class="flex items-center gap-1">
                    <Link
                        href="/tickets"
                        class="border-b-2 border-sky-500 px-3 py-4 text-xs font-medium text-sky-700 hover:bg-sky-50"
                    >
                        Chamados
                    </Link>
                    <Link
                        href="/tickets/create"
                        class="ml-1 inline-flex h-8 items-center gap-1.5 rounded-sm border border-sky-700 bg-sky-600 px-3 text-xs font-semibold text-white hover:bg-sky-700"
                    >
                        <Plus class="size-3.5" />
                        <span class="hidden sm:inline">Novo chamado</span>
                    </Link>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-[1600px] px-3 py-4 sm:px-5">
            <slot />
        </main>

        <Toaster />
    </div>
</template>
