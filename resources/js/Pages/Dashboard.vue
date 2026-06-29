<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    role:                String,
    stats:               Object,
    // admin only
    recentReservations:  Array,
    pendingReservations: Array,
    // user only
    upcoming:            Array,
    reservations:        Array,
});

const isAdmin = computed(() => props.role === 'admin');

const formatRupiah = (n) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n ?? 0);

const formatDate = (d) => {
    if (!d) return '-';
    return new Date(d + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const statusConfig = {
    pending:   { label: 'Menunggu',     bg: 'bg-yellow-100', text: 'text-yellow-700' },
    success:   { label: 'Dikonfirmasi', bg: 'bg-green-100',  text: 'text-green-700'  },
    cancelled: { label: 'Dibatalkan',   bg: 'bg-red-100',    text: 'text-red-500'    },
    expired:   { label: 'Kadaluarsa',   bg: 'bg-gray-100',   text: 'text-gray-500'   },
};
const getStatus = (s) => statusConfig[s] ?? statusConfig.pending;

const confirmReservation = (id) => {
    router.patch(`/admin/reservations/${id}/confirm`);
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>
        </template>

        <!-- ==================== ADMIN DASHBOARD ==================== -->
        <div v-if="isAdmin" class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

                <!-- Welcome banner -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-3xl p-7 text-white flex items-center justify-between shadow-xl shadow-blue-500/20">
                    <div>
                        <p class="text-blue-200 text-sm font-medium mb-1">Selamat datang kembali,</p>
                        <h2 class="text-3xl font-black">Admin VolleyZone 👋</h2>
                        <p class="text-blue-200 text-sm mt-2">Pantau dan kelola semua aktivitas lapangan dari sini.</p>
                    </div>
                    <div class="hidden sm:flex gap-3">
                        <Link href="/admin/reservations"
                            class="bg-white text-blue-600 font-bold text-sm px-5 py-2.5 rounded-xl hover:bg-blue-50 transition shadow-md">
                            Kelola Reservasi
                        </Link>
                        <Link href="/admin/courts"
                            class="bg-blue-500 text-white font-bold text-sm px-5 py-2.5 rounded-xl hover:bg-blue-400 transition border border-blue-400">
                            Kelola Lapangan
                        </Link>
                    </div>
                </div>

                <!-- Stats grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <p class="text-3xl font-black text-gray-800">{{ stats.activeCourts }}</p>
                        <p class="text-xs text-gray-400 font-medium mt-1">Lapangan Aktif</p>
                        <p class="text-xs text-gray-300 mt-0.5">dari {{ stats.totalCourts }} total</p>
                    </div>

                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                        <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <p class="text-3xl font-black text-gray-800">{{ stats.totalUsers }}</p>
                        <p class="text-xs text-gray-400 font-medium mt-1">Pengguna Terdaftar</p>
                    </div>

                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                        <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-3xl font-black text-yellow-500">{{ stats.pendingCount }}</p>
                        <p class="text-xs text-gray-400 font-medium mt-1">Menunggu Konfirmasi</p>
                        <Link v-if="stats.pendingCount > 0" href="/admin/reservations?status=pending"
                            class="text-xs text-yellow-600 font-semibold hover:underline mt-1 inline-block">Konfirmasi sekarang →</Link>
                    </div>

                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                        <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-2xl font-black text-orange-500">{{ formatRupiah(stats.totalRevenue) }}</p>
                        <p class="text-xs text-gray-400 font-medium mt-1">Total Pendapatan</p>
                        <p class="text-xs text-gray-300 mt-0.5">dari {{ stats.confirmedCount }} booking sukses</p>
                    </div>
                </div>

                <!-- Two column: Pending + Recent -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- Pending reservations - action needed -->
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-50">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-yellow-400 animate-pulse inline-block"></span>
                                <h3 class="font-bold text-gray-800">Perlu Dikonfirmasi</h3>
                            </div>
                            <Link href="/admin/reservations?status=pending"
                                class="text-xs font-semibold text-blue-600 hover:underline">Lihat semua →</Link>
                        </div>

                        <div v-if="!pendingReservations || pendingReservations.length === 0"
                            class="py-10 text-center text-gray-400 text-sm">
                            Tidak ada reservasi yang menunggu. ✅
                        </div>

                        <div v-else class="divide-y divide-gray-50">
                            <div v-for="r in pendingReservations" :key="r.id"
                                class="flex items-center gap-4 px-6 py-4 hover:bg-yellow-50/40 transition">
                                <div class="flex-1 min-w-0">
                                    <p class="font-mono font-bold text-xs text-gray-700 truncate">{{ r.booking_code }}</p>
                                    <p class="text-sm font-semibold text-gray-800 truncate">{{ r.user?.name }} — {{ r.court?.name }}</p>
                                    <p class="text-xs text-gray-400">{{ formatDate(r.date) }} · {{ r.start_time }}–{{ r.end_time }}</p>
                                </div>
                                <button @click="confirmReservation(r.id)"
                                    class="shrink-0 bg-green-500 text-white text-xs font-bold px-3 py-1.5 rounded-xl hover:bg-green-600 transition shadow-sm">
                                    ✓ Konfirmasi
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Recent reservations -->
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-50">
                            <h3 class="font-bold text-gray-800">Reservasi Terbaru</h3>
                            <Link href="/admin/reservations" class="text-xs font-semibold text-blue-600 hover:underline">Lihat semua →</Link>
                        </div>

                        <div v-if="!recentReservations || recentReservations.length === 0"
                            class="py-10 text-center text-gray-400 text-sm">
                            Belum ada reservasi.
                        </div>

                        <div v-else class="divide-y divide-gray-50">
                            <div v-for="r in recentReservations" :key="r.id"
                                class="flex items-center gap-3 px-6 py-3.5 hover:bg-gray-50/50 transition">
                                <div class="w-8 h-8 rounded-lg overflow-hidden bg-gray-100 shrink-0">
                                    <img v-if="r.court?.image_path" :src="r.court.image_path" alt="" class="w-full h-full object-cover" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-800 truncate">{{ r.user?.name }}</p>
                                    <p class="text-xs text-gray-400 truncate">{{ r.court?.name }} · {{ formatDate(r.date) }}</p>
                                </div>
                                <span :class="[getStatus(r.status).bg, getStatus(r.status).text, 'text-xs font-bold px-2.5 py-0.5 rounded-full whitespace-nowrap shrink-0']">
                                    {{ getStatus(r.status).label }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ==================== USER DASHBOARD ==================== -->
        <div v-else class="py-8">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

                <!-- Welcome banner -->
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-3xl p-7 text-white flex items-center justify-between shadow-xl shadow-orange-500/20">
                    <div>
                        <p class="text-orange-200 text-sm font-medium mb-1">Halo,</p>
                        <h2 class="text-3xl font-black">{{ $page.props.auth.user?.name }} 👋</h2>
                        <p class="text-orange-100 text-sm mt-2">Siap bermain voli hari ini? Yuk, booking lapangan sekarang!</p>
                    </div>
                    <Link href="/"
                        class="hidden sm:inline-block bg-white text-orange-600 font-bold text-sm px-5 py-2.5 rounded-xl hover:bg-orange-50 transition shadow-md whitespace-nowrap">
                        Cari Lapangan
                    </Link>
                </div>

                <!-- Stats user -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm text-center">
                        <p class="text-3xl font-black text-gray-800">{{ stats.total }}</p>
                        <p class="text-xs text-gray-400 font-medium mt-1">Total Booking</p>
                    </div>
                    <div class="bg-blue-50 rounded-2xl p-5 border border-blue-100 shadow-sm text-center">
                        <p class="text-3xl font-black text-blue-600">{{ stats.upcoming }}</p>
                        <p class="text-xs text-blue-400 font-medium mt-1">Akan Datang</p>
                    </div>
                    <div class="bg-green-50 rounded-2xl p-5 border border-green-100 shadow-sm text-center">
                        <p class="text-3xl font-black text-green-600">{{ stats.confirmed }}</p>
                        <p class="text-xs text-green-400 font-medium mt-1">Dikonfirmasi</p>
                    </div>
                    <div class="bg-red-50 rounded-2xl p-5 border border-red-100 shadow-sm text-center">
                        <p class="text-3xl font-black text-red-400">{{ stats.cancelled }}</p>
                        <p class="text-xs text-red-400 font-medium mt-1">Dibatalkan</p>
                    </div>
                </div>

                <!-- Upcoming bookings highlight -->
                <div v-if="upcoming && upcoming.length > 0" class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-50">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse inline-block"></span>
                            <h3 class="font-bold text-gray-800">Booking Mendatang</h3>
                        </div>
                        <Link href="/reservations" class="text-xs font-semibold text-blue-600 hover:underline">Lihat semua →</Link>
                    </div>
                    <div class="divide-y divide-gray-50">
                        <div v-for="r in upcoming" :key="r.id"
                            class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50/50 transition">
                            <div class="w-14 h-14 rounded-2xl overflow-hidden bg-gray-100 shrink-0">
                                <img v-if="r.court?.image_path" :src="r.court.image_path" alt="" class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span :class="[getStatus(r.status).bg, getStatus(r.status).text, 'text-xs font-bold px-2 py-0.5 rounded-full']">
                                        {{ getStatus(r.status).label }}
                                    </span>
                                    <span class="text-xs font-mono text-gray-400">{{ r.booking_code }}</span>
                                </div>
                                <p class="font-bold text-gray-800">{{ r.court?.name }}</p>
                                <p class="text-sm text-gray-400">📅 {{ formatDate(r.date) }} · 🕐 {{ r.start_time }}–{{ r.end_time }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="font-black text-orange-500 text-base">{{ formatRupiah(r.total_price) }}</p>
                                <Link :href="`/reservations/${r.id}`"
                                    class="text-xs text-blue-500 hover:underline font-semibold">Detail →</Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty state for new users -->
                <div v-if="!reservations || reservations.length === 0"
                    class="bg-white rounded-3xl border border-gray-100 shadow-sm py-16 text-center px-8">
                    <div class="w-20 h-20 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-5">
                        <svg class="w-9 h-9 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-700 mb-2">Belum Ada Booking</h3>
                    <p class="text-gray-400 text-sm mb-6 max-w-xs mx-auto">Yuk mulai pengalaman bermain voli Anda dengan memesan lapangan pertama!</p>
                    <Link href="/" class="inline-block bg-orange-500 text-white font-bold px-7 py-3 rounded-xl hover:bg-orange-600 transition shadow-lg shadow-orange-500/30">
                        Cari Lapangan Sekarang
                    </Link>
                </div>

                <!-- Recent reservations -->
                <div v-else class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-50">
                        <h3 class="font-bold text-gray-800">Riwayat Booking</h3>
                        <Link href="/reservations" class="text-xs font-semibold text-blue-600 hover:underline">Lihat semua →</Link>
                    </div>
                    <div class="divide-y divide-gray-50">
                        <div v-for="r in reservations" :key="r.id"
                            class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50/50 transition">
                            <div class="w-10 h-10 rounded-xl overflow-hidden bg-gray-100 shrink-0">
                                <img v-if="r.court?.image_path" :src="r.court.image_path" alt="" class="w-full h-full object-cover" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-800 truncate text-sm">{{ r.court?.name }}</p>
                                <p class="text-xs text-gray-400">{{ formatDate(r.date) }} · {{ r.start_time }}–{{ r.end_time }}</p>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <span :class="[getStatus(r.status).bg, getStatus(r.status).text, 'text-xs font-bold px-2.5 py-0.5 rounded-full']">
                                    {{ getStatus(r.status).label }}
                                </span>
                                <p class="font-bold text-gray-700 text-sm">{{ formatRupiah(r.total_price) }}</p>
                                <Link :href="`/reservations/${r.id}`" class="text-xs text-blue-500 hover:underline">Detail</Link>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </AuthenticatedLayout>
</template>
