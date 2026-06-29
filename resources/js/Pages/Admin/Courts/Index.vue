<script setup>
import { ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    courts: Array,
    flash:  Object,
});

const confirmingDelete = ref(null);

const deleteCourt = (court) => {
    router.delete(`/admin/courts/${court.id}`, {
        onSuccess: () => { confirmingDelete.value = null; },
    });
};

const formatRupiah = (n) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);
</script>

<template>
    <Head title="Kelola Lapangan" />

    <div class="min-h-screen bg-gray-50 font-sans">
        <!-- Topbar -->
        <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link href="/" class="font-black text-xl text-blue-600">Volley<span class="text-orange-500">Zone</span></Link>
                    <span class="text-gray-300">/</span>
                    <span class="text-sm font-semibold text-gray-600">Admin Panel</span>
                </div>
                <div class="flex items-center gap-4">
                    <Link href="/" class="text-sm text-gray-500 hover:text-blue-600 transition">← Beranda</Link>
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="text-sm text-red-500 hover:text-red-700 font-semibold transition"
                    >Keluar</Link>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

            <!-- Flash notification -->
            <div v-if="flash?.success" class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-5 py-3.5 rounded-2xl text-sm font-medium">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ flash.success }}
            </div>

            <!-- Page header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900">Kelola Lapangan</h1>
                    <p class="text-gray-500 text-sm mt-1">Tambah, edit, atau hapus data lapangan voli.</p>
                </div>
                <Link
                    href="/admin/courts/create"
                    class="inline-flex items-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-blue-700 shadow-md shadow-blue-500/30 transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Tambah Lapangan
                </Link>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left font-semibold text-gray-500 px-6 py-4 uppercase tracking-wider text-xs">Lapangan</th>
                            <th class="text-left font-semibold text-gray-500 px-6 py-4 uppercase tracking-wider text-xs">Harga / Jam</th>
                            <th class="text-left font-semibold text-gray-500 px-6 py-4 uppercase tracking-wider text-xs">Fasilitas</th>
                            <th class="text-left font-semibold text-gray-500 px-6 py-4 uppercase tracking-wider text-xs">Status</th>
                            <th class="text-right font-semibold text-gray-500 px-6 py-4 uppercase tracking-wider text-xs">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-if="!courts || courts.length === 0">
                            <td colspan="5" class="text-center py-16 text-gray-400">
                                Belum ada lapangan. Tambahkan lapangan pertama Anda!
                            </td>
                        </tr>
                        <tr v-for="court in courts" :key="court.id" class="hover:bg-gray-50/50 transition">
                            <!-- Lapangan info -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 rounded-xl overflow-hidden bg-gray-100 shrink-0">
                                        <img v-if="court.image_path" :src="court.image_path" alt="foto" class="w-full h-full object-cover" />
                                        <div v-else class="w-full h-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900">{{ court.name }}</p>
                                        <p class="text-gray-400 text-xs mt-0.5 line-clamp-1 max-w-xs">{{ court.description }}</p>
                                    </div>
                                </div>
                            </td>
                            <!-- Harga -->
                            <td class="px-6 py-4 font-semibold text-blue-600">{{ formatRupiah(court.price_per_hour) }}</td>
                            <!-- Fasilitas -->
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    <span v-for="f in (court.facilities || []).slice(0,3)" :key="f"
                                        class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-md">{{ f }}</span>
                                    <span v-if="(court.facilities || []).length > 3"
                                        class="bg-gray-100 text-gray-500 text-xs px-2 py-0.5 rounded-md">
                                        +{{ court.facilities.length - 3 }}
                                    </span>
                                </div>
                            </td>
                            <!-- Status -->
                            <td class="px-6 py-4">
                                <span :class="court.is_active
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-red-100 text-red-600'"
                                    class="text-xs font-bold px-3 py-1 rounded-full">
                                    {{ court.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <!-- Aksi -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="`/admin/courts/${court.id}/edit`"
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold bg-blue-50 text-blue-600 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Edit
                                    </Link>
                                    <button @click="confirmingDelete = court"
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold bg-red-50 text-red-500 hover:bg-red-100 px-3 py-1.5 rounded-lg transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Transition enter-active-class="duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100"
            leave-active-class="duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="confirmingDelete" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="confirmingDelete = null"></div>
                <div class="relative bg-white rounded-3xl shadow-2xl p-8 max-w-sm w-full z-10">
                    <div class="w-14 h-14 bg-red-100 rounded-2xl flex items-center justify-center mx-auto mb-5">
                        <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-center text-gray-900 mb-2">Hapus Lapangan?</h3>
                    <p class="text-gray-500 text-sm text-center mb-6">
                        Lapangan <strong>"{{ confirmingDelete?.name }}"</strong> akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.
                    </p>
                    <div class="flex gap-3">
                        <button @click="confirmingDelete = null"
                            class="flex-1 py-3 rounded-xl border border-gray-200 text-gray-600 font-semibold hover:bg-gray-50 transition">
                            Batal
                        </button>
                        <button @click="deleteCourt(confirmingDelete)"
                            class="flex-1 py-3 rounded-xl bg-red-500 text-white font-bold hover:bg-red-600 transition">
                            Ya, Hapus
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>
