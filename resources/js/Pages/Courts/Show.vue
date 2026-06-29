<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    court: {
        type: Object,
        required: true,
    },
});

// Generate time slots 08:00 - 22:00
const allSlots = [];
for (let i = 8; i <= 22; i++) {
    allSlots.push(`${String(i).padStart(2, '0')}:00`);
}

const today = new Date().toISOString().split('T')[0];

const selectedDate = ref(today);
const bookedSlots = ref([]);
const loadingSlots = ref(false);
const fetchError = ref(null);

const startTime = ref(null);
const endTime = ref(null);

// Compute total price based on selected slots
const totalPrice = computed(() => {
    if (!startTime.value || !endTime.value) return 0;
    const h1 = parseInt(startTime.value.split(':')[0]);
    const h2 = parseInt(endTime.value.split(':')[0]);
    return (h2 - h1) * props.court.price_per_hour;
});

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(angka || 0);
};

// Fetch availability using GET (no CSRF needed)
const fetchAvailability = async () => {
    if (!selectedDate.value) return;
    loadingSlots.value = true;
    fetchError.value = null;
    startTime.value = null;
    endTime.value = null;

    try {
        const params = new URLSearchParams({
            court_id: props.court.id,
            date: selectedDate.value,
        });
        const res = await fetch(`/api/check-availability?${params}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!res.ok) throw new Error(`Server error: ${res.status}`);
        const data = await res.json();
        const booked = [];
        if (Array.isArray(data)) {
            data.forEach((r) => {
                const s = parseInt(r.start_time.split(':')[0]);
                const e = parseInt(r.end_time.split(':')[0]);
                for (let i = s; i < e; i++) {
                    booked.push(`${String(i).padStart(2, '0')}:00`);
                }
            });
        }
        bookedSlots.value = booked;
    } catch (err) {
        console.warn('Availability fetch failed:', err);
        bookedSlots.value = [];
        fetchError.value = 'Gagal memuat jadwal. Silakan coba lagi.';
    } finally {
        loadingSlots.value = false;
    }
};

onMounted(() => {
    fetchAvailability();
});

const onDateChange = () => {
    fetchAvailability();
};

// Slot selection
const isBooked = (time) => bookedSlots.value.includes(time);

const isSelected = (time) => {
    if (!startTime.value) return false;
    const cur = parseInt(time.split(':')[0]);
    const s = parseInt(startTime.value.split(':')[0]);
    if (endTime.value) {
        const e = parseInt(endTime.value.split(':')[0]);
        return cur >= s && cur < e;
    }
    return cur === s;
};

const toggleSlot = (time) => {
    if (isBooked(time)) return;

    const clickedHour = parseInt(time.split(':')[0]);

    if (!startTime.value) {
        startTime.value = time;
        endTime.value = `${String(clickedHour + 1).padStart(2, '0')}:00`;
        return;
    }

    const startHour = parseInt(startTime.value.split(':')[0]);

    if (clickedHour === startHour) {
        // Deselect
        startTime.value = null;
        endTime.value = null;
        return;
    }

    if (clickedHour > startHour) {
        // Check for conflicts in range
        let conflict = false;
        for (let i = startHour + 1; i <= clickedHour; i++) {
            if (bookedSlots.value.includes(`${String(i).padStart(2, '0')}:00`)) {
                conflict = true;
                break;
            }
        }
        if (conflict) {
            startTime.value = time;
            endTime.value = `${String(clickedHour + 1).padStart(2, '0')}:00`;
        } else {
            endTime.value = `${String(clickedHour + 1).padStart(2, '0')}:00`;
        }
    } else {
        // Clicked before start, set as new start
        startTime.value = time;
        endTime.value = `${String(clickedHour + 1).padStart(2, '0')}:00`;
    }
};

// Submit booking using Inertia useForm
const form = useForm({
    court_id: props.court.id,
    date: today,
    start_time: '',
    end_time: '',
    total_price: 0,
});

const submitBooking = () => {
    form.date = selectedDate.value;
    form.start_time = startTime.value;
    form.end_time = endTime.value;
    form.total_price = totalPrice.value;
    form.post('/reservations', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="court.name" />

    <div class="min-h-screen bg-gray-50 text-gray-900 font-sans pb-20">
        <!-- Navbar -->
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <Link href="/" class="flex items-center gap-2 text-gray-500 hover:text-blue-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span class="font-semibold text-sm">Kembali</span>
                    </Link>
                    <div class="font-black text-xl text-blue-600">Volley<span class="text-orange-500">Zone</span></div>
                </div>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Left: Court Info -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                        <div class="h-48 rounded-xl bg-gray-100 overflow-hidden mb-6">
                            <img v-if="court.image_path" :src="court.image_path" alt="Court" class="w-full h-full object-cover" />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>

                        <h1 class="text-2xl font-bold mb-1">{{ court.name }}</h1>
                        <p class="text-blue-600 font-bold text-xl mb-4">
                            {{ formatRupiah(court.price_per_hour) }}
                            <span class="text-sm text-gray-400 font-normal">/ jam</span>
                        </p>
                        <p class="text-gray-500 text-sm mb-6 leading-relaxed">{{ court.description }}</p>

                        <div v-if="court.facilities && court.facilities.length">
                            <h3 class="font-semibold text-xs text-gray-400 mb-3 uppercase tracking-widest">Fasilitas</h3>
                            <div class="flex flex-wrap gap-2">
                                <span v-for="fac in court.facilities" :key="fac"
                                    class="bg-blue-50 text-blue-700 text-xs px-3 py-1.5 rounded-lg font-semibold">
                                    {{ fac }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Booking -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-gray-100">
                        <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Jadwal &amp; Reservasi
                        </h2>

                        <!-- Validation errors -->
                        <div v-if="Object.keys(form.errors).length > 0" class="mb-6 p-4 bg-red-50 text-red-600 rounded-xl text-sm">
                            <ul class="list-disc list-inside space-y-1">
                                <li v-for="(err, key) in form.errors" :key="key">{{ err }}</li>
                            </ul>
                        </div>

                        <!-- Fetch error -->
                        <div v-if="fetchError" class="mb-4 p-3 bg-yellow-50 text-yellow-700 rounded-xl text-sm">
                            {{ fetchError }}
                        </div>

                        <!-- Date picker -->
                        <div class="mb-8">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Tanggal</label>
                            <input
                                type="date"
                                v-model="selectedDate"
                                :min="today"
                                @change="onDateChange"
                                class="w-full md:w-1/2 rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                            />
                        </div>

                        <!-- Time slots -->
                        <div class="mb-8">
                            <div class="flex justify-between items-center mb-4">
                                <label class="block text-sm font-semibold text-gray-700">Pilih Slot Waktu</label>
                                <div class="flex gap-4 text-xs font-medium text-gray-500">
                                    <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-green-500 inline-block"></span>Tersedia</div>
                                    <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-400 inline-block"></span>Terisi</div>
                                    <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-600 inline-block"></span>Dipilih</div>
                                </div>
                            </div>

                            <div v-if="loadingSlots" class="py-10 text-center text-gray-400 text-sm animate-pulse">
                                Memuat jadwal...
                            </div>

                            <div v-else class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                                <button
                                    v-for="time in allSlots"
                                    :key="time"
                                    type="button"
                                    :disabled="isBooked(time)"
                                    @click="toggleSlot(time)"
                                    :class="[
                                        'py-3 rounded-xl font-bold text-sm transition-all duration-150',
                                        isBooked(time)
                                            ? 'bg-red-50 text-red-400 border border-red-100 cursor-not-allowed opacity-70'
                                            : isSelected(time)
                                                ? 'bg-blue-600 text-white shadow-lg scale-105'
                                                : 'bg-green-50 text-green-700 border border-green-200 hover:bg-green-100 cursor-pointer'
                                    ]"
                                >
                                    {{ time }}
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mt-3">* Klik jam mulai lalu klik jam selesai untuk memilih beberapa jam.</p>
                        </div>

                        <!-- Summary & Submit -->
                        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                            <h3 class="font-bold text-base mb-4 pb-2 border-b border-gray-200">Ringkasan Booking</h3>

                            <div class="space-y-3 mb-6 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Tanggal</span>
                                    <span class="font-semibold">{{ selectedDate }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Waktu Bermain</span>
                                    <span class="font-semibold">
                                        <template v-if="startTime">{{ startTime }} – {{ endTime }}</template>
                                        <template v-else>-</template>
                                    </span>
                                </div>
                                <div class="flex justify-between items-center pt-3 border-t border-gray-200">
                                    <span class="font-bold text-gray-800">Total Bayar</span>
                                    <span class="font-black text-xl text-orange-500">{{ formatRupiah(totalPrice) }}</span>
                                </div>
                            </div>

                            <!-- Not logged in -->
                            <div v-if="!$page.props.auth.user" class="p-4 bg-yellow-50 rounded-xl text-yellow-700 text-sm font-medium text-center mb-2">
                                Silakan
                                <Link href="/login" class="underline font-bold">Masuk</Link>
                                atau
                                <Link href="/register" class="underline font-bold">Daftar</Link>
                                untuk melanjutkan booking.
                            </div>

                            <!-- Submit button -->
                            <button
                                v-else
                                @click="submitBooking"
                                :disabled="!startTime || form.processing"
                                :class="[
                                    'w-full py-4 rounded-xl font-bold text-lg transition-all duration-200',
                                    (!startTime || form.processing)
                                        ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                                        : 'bg-orange-500 text-white hover:bg-orange-600 shadow-xl shadow-orange-500/30 hover:-translate-y-0.5'
                                ]"
                            >
                                {{ form.processing ? 'Memproses...' : 'Lanjutkan Booking' }}
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
