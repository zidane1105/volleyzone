<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    reservations: Array,
});

const page = usePage();
const flash = computed(() => page.props.flash ?? {});

const formatRupiah = (n) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);

const formatDate = (d) => {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const statusConfig = {
    pending: {
        label: 'Menunggu',
        bg: 'bg-yellow-100',
        text: 'text-yellow-700',
        dot: 'bg-yellow-500',
    },
    success: {
        label: 'Dikonfirmasi',
        bg: 'bg-green-100',
        text: 'text-green-700',
        dot: 'bg-green-500',
    },
    cancelled: {
        label: 'Dibatalkan',
        bg: 'bg-red-100',
        text: 'text-red-600',
        dot: 'bg-red-400',
    },
    expired: {
        label: 'Kadaluarsa',
        bg: 'bg-gray-100',
        text: 'text-gray-500',
        dot: 'bg-gray-400',
    },
};

const getStatus = (s) => statusConfig[s] ?? statusConfig.pending;

const upcoming = computed(() =>
    (props.reservations ?? []).filter(r => r.status !== 'cancelled' && new Date(r.date) >= new Date(new Date().toDateString()))
);
const past = computed(() =>
    (props.reservations ?? []).filter(r => r.status === 'cancelled' || new Date(r.date) < new Date(new Date().toDateString()))
);
</script>

<template>
    <Head title="Booking Saya" />

    <div class="min-h-screen bg-gray-50 font-sans">
        <!-- Topbar -->
        <header class="bg-white border-b border-gray-100 sticky top-0 z-30">
            <div class="max-w-5xl mx-auto px-4 h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link href="/" class="font-black text-xl text-blue-600">Volley<span class="text-orange-500">Zone</span></Link>
                    <span class="text-gray-300">/</span>
                    <span class="text-sm font-semibold text-gray-600">Booking Saya</span>
                </div>
                <Link href="/" class="text-sm text-gray-500 hover:text-blue-600 transition">← Beranda</Link>
            </div>
        </header>

        <div class="max-w-5xl mx-auto px-4 py-10">

            <!-- Flash success -->
            <div v-if="flash?.success" class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-5 py-3.5 rounded-2xl text-sm font-medium">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ flash.success }}
            </div>

            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900">Booking Saya</h1>
                    <p class="text-gray-400 text-sm mt-1">Riwayat dan status semua pemesanan lapangan Anda.</p>
                </div>
                <Link href="/" class="inline-flex items-center gap-2 bg-orange-500 text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-orange-600 shadow-md shadow-orange-500/30 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Booking Lagi
                </Link>
            </div>

            <!-- Empty state -->
            <div v-if="!reservations || reservations.length === 0" class="text-center py-24 bg-white rounded-3xl border border-gray-100 shadow-sm">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-5">
                    <svg class="w-9 h-9 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-700 mb-2">Belum Ada Booking</h3>
                <p class="text-gray-400 text-sm mb-6">Yuk, pesan lapangan voli pertama Anda sekarang!</p>
                <Link href="/" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-xl font-bold text-sm hover:bg-blue-700 transition">
                    Cari Lapangan
                </Link>
            </div>

            <!-- Upcoming bookings -->
            <div v-if="upcoming.length > 0" class="mb-10">
                <h2 class="text-lg font-bold text-gray-700 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-500 inline-block"></span>
                    Booking Mendatang ({{ upcoming.length }})
                </h2>
                <div class="space-y-4">
                    <div v-for="r in upcoming" :key="r.id"
                        class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition overflow-hidden flex">
                        <!-- Court photo -->
                        <div class="w-24 sm:w-36 shrink-0 bg-gray-100">
                            <img v-if="r.court?.image_path" :src="r.court.image_path" alt="court" class="w-full h-full object-cover" />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 px-5 py-4 flex flex-col sm:flex-row sm:items-center gap-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span :class="[getStatus(r.status).bg, getStatus(r.status).text, 'text-xs font-bold px-2.5 py-0.5 rounded-full']">
                                        {{ getStatus(r.status).label }}
                                    </span>
                                    <span class="text-xs text-gray-400 font-mono">{{ r.booking_code }}</span>
                                </div>
                                <p class="font-bold text-gray-900 text-base">{{ r.court?.name ?? '-' }}</p>
                                <p class="text-sm text-gray-500 mt-0.5">
                                    📅 {{ formatDate(r.date) }} &nbsp;·&nbsp; 🕐 {{ r.start_time }} – {{ r.end_time }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3 sm:flex-col sm:items-end sm:gap-1">
                                <p class="font-black text-orange-500 text-lg">{{ formatRupiah(r.total_price) }}</p>
                                <Link :href="`/reservations/${r.id}`"
                                    class="text-xs font-semibold bg-blue-50 text-blue-600 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                                    Lihat Detail →
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Past / Cancelled bookings -->
            <div v-if="past.length > 0">
                <h2 class="text-lg font-bold text-gray-400 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-gray-300 inline-block"></span>
                    Riwayat & Dibatalkan ({{ past.length }})
                </h2>
                <div class="space-y-3">
                    <div v-for="r in past" :key="r.id"
                        class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex opacity-70">
                        <div class="w-24 sm:w-36 shrink-0 bg-gray-100">
                            <img v-if="r.court?.image_path" :src="r.court.image_path" alt="court" class="w-full h-full object-cover grayscale" />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                        </div>
                        <div class="flex-1 px-5 py-4 flex flex-col sm:flex-row sm:items-center gap-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span :class="[getStatus(r.status).bg, getStatus(r.status).text, 'text-xs font-bold px-2.5 py-0.5 rounded-full']">
                                        {{ getStatus(r.status).label }}
                                    </span>
                                    <span class="text-xs text-gray-400 font-mono">{{ r.booking_code }}</span>
                                </div>
                                <p class="font-bold text-gray-500 text-base">{{ r.court?.name ?? '-' }}</p>
                                <p class="text-sm text-gray-400 mt-0.5">
                                    📅 {{ formatDate(r.date) }} &nbsp;·&nbsp; 🕐 {{ r.start_time }} – {{ r.end_time }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3 sm:flex-col sm:items-end sm:gap-1">
                                <p class="font-bold text-gray-400 text-base">{{ formatRupiah(r.total_price) }}</p>
                                <Link :href="`/reservations/${r.id}`"
                                    class="text-xs font-semibold text-gray-400 hover:text-gray-600 px-3 py-1.5 rounded-lg transition whitespace-nowrap border border-gray-200">
                                    Lihat Detail
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>
