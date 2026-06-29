<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    reservation: Object,
});

const formatRupiah = (n) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);

const formatDate = (d) => {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
};

const statusConfig = {
    pending: {
        label: 'Menunggu Konfirmasi',
        bg: 'bg-yellow-100',
        text: 'text-yellow-700',
        icon: '⏳',
    },
    success: {
        label: 'Dikonfirmasi',
        bg: 'bg-green-100',
        text: 'text-green-700',
        icon: '✅',
    },
    cancelled: {
        label: 'Dibatalkan',
        bg: 'bg-red-100',
        text: 'text-red-600',
        icon: '❌',
    },
    expired: {
        label: 'Kadaluarsa',
        bg: 'bg-gray-100',
        text: 'text-gray-500',
        icon: '🕑',
    },
};

const status = statusConfig[props.reservation.status] ?? statusConfig.pending;

const cancelForm = useForm({});
const cancelBooking = () => {
    if (confirm('Yakin ingin membatalkan booking ini?')) {
        cancelForm.patch(`/reservations/${props.reservation.id}/cancel`);
    }
};
</script>

<template>
    <Head :title="`Booking ${reservation.booking_code}`" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-orange-50 font-sans">
        <!-- Topbar -->
        <header class="bg-white/80 backdrop-blur border-b border-gray-100 sticky top-0 z-30">
            <div class="max-w-3xl mx-auto px-4 h-16 flex items-center justify-between">
                <Link href="/" class="font-black text-xl text-blue-600">Volley<span class="text-orange-500">Zone</span></Link>
                <Link href="/reservations" class="text-sm font-semibold text-gray-500 hover:text-blue-600 transition">
                    Lihat Semua Booking →
                </Link>
            </div>
        </header>

        <div class="max-w-3xl mx-auto px-4 py-12">

            <!-- Success Banner (only for pending/confirmed) -->
            <div v-if="reservation.status !== 'cancelled'" class="mb-8 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
                    <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Booking Berhasil! 🎉</h1>
                <p class="text-gray-500">Lapangan sudah dipesan. Simpan kode booking Anda di bawah ini.</p>
            </div>
            <div v-else class="mb-8 text-center">
                <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Detail Booking</h1>
            </div>

            <!-- Booking Code Card -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <!-- Header gradient -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-6 text-white">
                    <p class="text-blue-200 text-sm font-medium mb-1">Kode Booking</p>
                    <p class="text-3xl font-black tracking-widest">{{ reservation.booking_code }}</p>
                    <div class="mt-3">
                        <span :class="[status.bg, status.text, 'text-xs font-bold px-3 py-1 rounded-full']">
                            {{ status.icon }} {{ status.label }}
                        </span>
                    </div>
                </div>

                <!-- Details -->
                <div class="px-8 py-6 space-y-5">
                    <!-- Court info -->
                    <div class="flex items-center gap-4 pb-5 border-b border-gray-100">
                        <div class="w-16 h-16 rounded-2xl overflow-hidden bg-gray-100 shrink-0">
                            <img v-if="reservation.court?.image_path" :src="reservation.court.image_path" alt="court" class="w-full h-full object-cover" />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                        </div>
                        <div>
                            <p class="font-bold text-lg text-gray-900">{{ reservation.court?.name ?? '-' }}</p>
                            <p class="text-sm text-gray-400">{{ reservation.court?.description?.substring(0, 60) }}...</p>
                        </div>
                    </div>

                    <!-- Booking details grid -->
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <p class="text-xs text-gray-400 font-medium uppercase tracking-wider mb-1">Tanggal Bermain</p>
                            <p class="font-bold text-gray-800">{{ formatDate(reservation.date) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 font-medium uppercase tracking-wider mb-1">Waktu Bermain</p>
                            <p class="font-bold text-gray-800">{{ reservation.start_time }} – {{ reservation.end_time }} WIB</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 font-medium uppercase tracking-wider mb-1">Durasi</p>
                            <p class="font-bold text-gray-800">
                                {{
                                    (() => {
                                        const s = parseInt(reservation.start_time?.split(':')[0] ?? 0);
                                        const e = parseInt(reservation.end_time?.split(':')[0] ?? 0);
                                        const h = e - s;
                                        return h + ' jam';
                                    })()
                                }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 font-medium uppercase tracking-wider mb-1">Status</p>
                            <span :class="[status.bg, status.text, 'text-xs font-bold px-2.5 py-1 rounded-full']">
                                {{ status.label }}
                            </span>
                        </div>
                    </div>

                    <!-- Total price -->
                    <div class="flex items-center justify-between pt-5 border-t border-gray-100">
                        <p class="font-bold text-gray-700">Total Pembayaran</p>
                        <p class="text-2xl font-black text-orange-500">{{ formatRupiah(reservation.total_price) }}</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-3">
                <Link href="/" class="flex-1 text-center py-3.5 rounded-2xl border border-gray-200 text-gray-600 font-semibold text-sm hover:bg-gray-50 transition">
                    ← Kembali ke Beranda
                </Link>
                <Link href="/reservations" class="flex-1 text-center py-3.5 rounded-2xl bg-blue-600 text-white font-bold text-sm hover:bg-blue-700 transition shadow-md shadow-blue-500/30">
                    Lihat Semua Bookingku
                </Link>
                <button
                    v-if="reservation.status === 'pending'"
                    @click="cancelBooking"
                    :disabled="cancelForm.processing"
                    class="flex-1 py-3.5 rounded-2xl border border-red-200 text-red-500 font-semibold text-sm hover:bg-red-50 transition disabled:opacity-60"
                >
                    Batalkan Booking
                </button>
            </div>

            <!-- Info note -->
            <div v-if="reservation.status === 'pending'" class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-2xl text-sm text-yellow-700">
                <strong>ℹ️ Info:</strong> Booking Anda sedang menunggu konfirmasi. Tunjukkan kode booking
                <strong class="tracking-wider">{{ reservation.booking_code }}</strong> kepada petugas saat tiba di lapangan.
            </div>
        </div>
    </div>
</template>
