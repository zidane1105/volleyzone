<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    reservations:  Array,
    counts:        Object,
    currentStatus: String,
    flash:         Object,
});

const processing = ref(null);

const confirmReservation = (id) => {
    processing.value = `confirm-${id}`;
    router.patch(`/admin/reservations/${id}/confirm`, {}, {
        onFinish: () => { processing.value = null; },
    });
};

const cancelReservation = (r) => {
    if (!confirm(`Batalkan reservasi ${r.booking_code}?`)) return;
    processing.value = `cancel-${r.id}`;
    router.patch(`/admin/reservations/${r.id}/cancel`, {}, {
        onFinish: () => { processing.value = null; },
    });
};

const filterStatus = (status) => {
    router.get('/admin/reservations', { status }, { preserveState: true, replace: true });
};

const formatRupiah = (n) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);

const formatDate = (d) => {
    if (!d) return '-';
    return new Date(d + 'T00:00:00').toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
};

const statusConfig = {
    pending:   { label: 'Menunggu',     bg: 'bg-yellow-100', text: 'text-yellow-700', dot: 'bg-yellow-400' },
    success:   { label: 'Dikonfirmasi', bg: 'bg-green-100',  text: 'text-green-700',  dot: 'bg-green-500'  },
    cancelled: { label: 'Dibatalkan',   bg: 'bg-red-100',    text: 'text-red-600',    dot: 'bg-red-400'    },
    expired:   { label: 'Kadaluarsa',   bg: 'bg-gray-100',   text: 'text-gray-500',   dot: 'bg-gray-400'   },
};
const getStatus = (s) => statusConfig[s] ?? statusConfig.pending;

// Tabs: key harus sesuai dengan nilai status di DB dan key di counts
const tabs = [
    { key: 'all',       label: 'Semua',         countKey: 'all' },
    { key: 'pending',   label: 'Menunggu',       countKey: 'pending' },
    { key: 'success',   label: 'Dikonfirmasi',   countKey: 'success' },
    { key: 'cancelled', label: 'Dibatalkan',     countKey: 'cancelled' },
];
</script>

<template>
    <Head title="Kelola Reservasi — Admin" />

    <div class="min-h-screen bg-gray-50 font-sans">
        <!-- Topbar -->
        <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link href="/" class="font-black text-xl text-blue-600">Volley<span class="text-orange-500">Zone</span></Link>
                    <span class="text-gray-300">/</span>
                    <span class="text-sm font-semibold text-gray-500">Admin</span>
                    <span class="text-gray-300">/</span>
                    <span class="text-sm font-bold text-gray-800">Kelola Reservasi</span>
                </div>
                <nav class="flex items-center gap-5 text-sm font-semibold">
                    <Link href="/admin/courts"
                        class="text-gray-500 hover:text-blue-600 transition">
                        Kelola Lapangan
                    </Link>
                    <Link href="/admin/reservations"
                        class="text-blue-600 border-b-2 border-blue-600 pb-0.5">
                        Kelola Reservasi
                    </Link>
                    <Link href="/"
                        class="text-gray-400 hover:text-gray-700 transition">
                        Beranda
                    </Link>
                    <Link href="/logout" method="post" as="button"
                        class="text-red-500 hover:text-red-700 transition">
                        Keluar
                    </Link>
                </nav>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

            <!-- Flash messages -->
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0">
                <div v-if="flash?.success"
                    class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-5 py-3.5 rounded-2xl text-sm font-medium shadow-sm">
                    <svg class="w-5 h-5 shrink-0 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ flash.success }}
                </div>
            </Transition>

            <div v-if="flash?.error"
                class="mb-6 flex items-center gap-3 bg-red-50 border border-red-200 text-red-600 px-5 py-3.5 rounded-2xl text-sm font-medium">
                ⚠️ {{ flash.error }}
            </div>

            <!-- Page header -->
            <div class="mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900">Kelola Reservasi</h1>
                <p class="text-gray-400 text-sm mt-1">Konfirmasi atau batalkan pesanan lapangan dari pengguna.</p>
            </div>

            <!-- Stats cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
                <button @click="filterStatus('all')"
                    :class="currentStatus === 'all' ? 'ring-2 ring-gray-400 ring-offset-2' : 'hover:shadow-md'"
                    class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm text-left transition-all">
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wider mb-2">Total</p>
                    <p class="text-4xl font-black text-gray-800">{{ counts.all }}</p>
                </button>
                <button @click="filterStatus('pending')"
                    :class="currentStatus === 'pending' ? 'ring-2 ring-yellow-400 ring-offset-2' : 'hover:shadow-md'"
                    class="bg-yellow-50 rounded-2xl p-5 border border-yellow-100 shadow-sm text-left transition-all">
                    <p class="text-xs text-yellow-600 font-medium uppercase tracking-wider mb-2">Menunggu</p>
                    <p class="text-4xl font-black text-yellow-500">{{ counts.pending }}</p>
                </button>
                <button @click="filterStatus('success')"
                    :class="currentStatus === 'success' ? 'ring-2 ring-green-400 ring-offset-2' : 'hover:shadow-md'"
                    class="bg-green-50 rounded-2xl p-5 border border-green-100 shadow-sm text-left transition-all">
                    <p class="text-xs text-green-600 font-medium uppercase tracking-wider mb-2">Dikonfirmasi</p>
                    <p class="text-4xl font-black text-green-500">{{ counts.success }}</p>
                </button>
                <button @click="filterStatus('cancelled')"
                    :class="currentStatus === 'cancelled' ? 'ring-2 ring-red-400 ring-offset-2' : 'hover:shadow-md'"
                    class="bg-red-50 rounded-2xl p-5 border border-red-100 shadow-sm text-left transition-all">
                    <p class="text-xs text-red-500 font-medium uppercase tracking-wider mb-2">Dibatalkan</p>
                    <p class="text-4xl font-black text-red-400">{{ counts.cancelled }}</p>
                </button>
            </div>

            <!-- Filter tabs -->
            <div class="flex gap-1.5 mb-6 bg-gray-100 p-1.5 rounded-2xl w-fit">
                <button v-for="tab in tabs" :key="tab.key"
                    @click="filterStatus(tab.key)"
                    :class="[
                        'px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-150',
                        currentStatus === tab.key
                            ? 'bg-white text-gray-900 shadow-sm'
                            : 'text-gray-500 hover:text-gray-800 hover:bg-white/50'
                    ]"
                >
                    {{ tab.label }}
                    <span :class="currentStatus === tab.key ? 'opacity-100' : 'opacity-50'"
                        class="ml-1.5 text-xs bg-gray-100 rounded-full px-1.5 py-0.5">
                        {{ counts[tab.countKey] }}
                    </span>
                </button>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-x-auto">
                <table class="w-full text-sm min-w-[800px]">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-6 py-4 bg-gray-50">Kode & Pengguna</th>
                            <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-6 py-4 bg-gray-50">Lapangan</th>
                            <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-6 py-4 bg-gray-50">Jadwal Booking</th>
                            <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-6 py-4 bg-gray-50">Total</th>
                            <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-6 py-4 bg-gray-50">Status</th>
                            <th class="text-right text-xs font-semibold text-gray-400 uppercase tracking-wider px-6 py-4 bg-gray-50">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">

                        <!-- Empty state -->
                        <tr v-if="!reservations || reservations.length === 0">
                            <td colspan="6" class="py-20 text-center">
                                <div class="flex flex-col items-center gap-3 text-gray-400">
                                    <svg class="w-12 h-12 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <p class="font-medium">Tidak ada reservasi
                                        <template v-if="currentStatus !== 'all'"> dengan status ini</template>
                                    </p>
                                </div>
                            </td>
                        </tr>

                        <!-- Reservation rows -->
                        <tr v-for="r in reservations" :key="r.id"
                            :class="r.status === 'pending' ? 'bg-yellow-50/40 hover:bg-yellow-50' : 'hover:bg-gray-50/70'"
                            class="transition-colors duration-100">

                            <!-- Kode & User -->
                            <td class="px-6 py-4">
                                <p class="font-mono font-black text-sm text-gray-800 tracking-widest mb-0.5">{{ r.booking_code }}</p>
                                <p class="text-xs font-semibold text-gray-600">{{ r.user?.name ?? '-' }}</p>
                                <p class="text-xs text-gray-400">{{ r.user?.email ?? '' }}</p>
                            </td>

                            <!-- Lapangan -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl overflow-hidden bg-gray-100 shrink-0">
                                        <img v-if="r.court?.image_path" :src="r.court.image_path" alt="" class="w-full h-full object-cover" />
                                        <div v-else class="w-full h-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <span class="font-semibold text-gray-800 text-xs leading-tight">{{ r.court?.name ?? '-' }}</span>
                                </div>
                            </td>

                            <!-- Jadwal -->
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-800 text-sm">{{ formatDate(r.date) }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">🕐 {{ r.start_time }} – {{ r.end_time }} WIB</p>
                            </td>

                            <!-- Total -->
                            <td class="px-6 py-4">
                                <p class="font-black text-orange-500">{{ formatRupiah(r.total_price) }}</p>
                            </td>

                            <!-- Status badge -->
                            <td class="px-6 py-4">
                                <span :class="[getStatus(r.status).bg, getStatus(r.status).text]"
                                    class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full whitespace-nowrap">
                                    <span :class="getStatus(r.status).dot" class="w-1.5 h-1.5 rounded-full inline-block"></span>
                                    {{ getStatus(r.status).label }}
                                </span>
                            </td>

                            <!-- Aksi -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">

                                    <!-- PENDING: Tombol Konfirmasi + Batalkan -->
                                    <template v-if="r.status === 'pending'">
                                        <button
                                            @click="confirmReservation(r.id)"
                                            :disabled="processing === `confirm-${r.id}`"
                                            class="inline-flex items-center gap-1.5 text-xs font-bold bg-green-500 text-white hover:bg-green-600 px-3 py-2 rounded-xl transition disabled:opacity-50 shadow-sm shadow-green-500/30 whitespace-nowrap">
                                            <svg v-if="processing !== `confirm-${r.id}`" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <svg v-else class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                            </svg>
                                            Konfirmasi
                                        </button>
                                        <button
                                            @click="cancelReservation(r)"
                                            :disabled="processing === `cancel-${r.id}`"
                                            class="inline-flex items-center gap-1 text-xs font-semibold text-red-500 hover:bg-red-50 px-3 py-2 rounded-xl border border-red-200 transition disabled:opacity-50 whitespace-nowrap">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Batalkan
                                        </button>
                                    </template>

                                    <!-- SUCCESS: Hanya tampilkan tombol Batalkan -->
                                    <template v-else-if="r.status === 'success'">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-green-600 font-bold flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                Dikonfirmasi
                                            </span>
                                            <button
                                                @click="cancelReservation(r)"
                                                :disabled="processing === `cancel-${r.id}`"
                                                class="text-xs font-semibold text-red-400 hover:text-red-600 hover:bg-red-50 px-2.5 py-1.5 rounded-lg border border-red-100 transition disabled:opacity-50 whitespace-nowrap">
                                                Batalkan
                                            </button>
                                        </div>
                                    </template>

                                    <!-- CANCELLED / EXPIRED: Tidak ada aksi -->
                                    <template v-else>
                                        <span class="text-xs text-gray-400 italic">—</span>
                                    </template>

                                </div>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

        </div>
    </div>
</template>
