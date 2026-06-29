<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    courts: Array,
});
</script>

<template>
    <Head title="Beranda" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 font-sans">
        <!-- Navbar with Glassmorphism -->
        <nav class="fixed w-full z-50 bg-white/70 dark:bg-gray-900/70 backdrop-blur-md border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-black text-blue-600 dark:text-blue-400">Volley<span class="text-orange-500">Zone</span></span>
                    </div>
                    <div class="flex gap-4 items-center">
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="text-sm font-semibold hover:text-blue-500 transition">Dashboard</Link>
                        <template v-else>
                            <Link :href="route('login')" class="text-sm font-semibold hover:text-blue-500 transition">Masuk</Link>
                            <Link :href="route('register')" class="text-sm font-semibold bg-blue-600 text-white px-5 py-2.5 rounded-full hover:bg-blue-700 transition shadow-lg shadow-blue-500/30">Daftar</Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="relative pt-32 pb-20 sm:pt-40 sm:pb-24 lg:pb-32 overflow-hidden flex items-center justify-center min-h-[70vh]">
            <!-- Decorative background elements -->
            <div class="absolute inset-y-0 w-full h-full pointer-events-none overflow-hidden">
                <div class="absolute -top-1/2 -right-1/4 w-[1000px] h-[1000px] rounded-full bg-gradient-to-br from-blue-100 to-transparent dark:from-blue-900/20 blur-3xl opacity-60"></div>
                <div class="absolute -bottom-1/2 -left-1/4 w-[800px] h-[800px] rounded-full bg-gradient-to-tr from-orange-100 to-transparent dark:from-orange-900/20 blur-3xl opacity-60"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-sm font-medium mb-6">
                    <span class="flex h-2 w-2 rounded-full bg-blue-600"></span>
                    Sistem Reservasi Lapangan Terpercaya
                </div>
                <h1 class="text-5xl sm:text-7xl font-extrabold tracking-tight leading-tight">
                    Booking Lapangan Voli <br class="hidden sm:block"/> 
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-orange-500">Lebih Mudah & Cepat</span>
                </h1>
                <p class="mt-6 text-lg sm:text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto leading-relaxed">
                    Temukan dan pesan lapangan voli terbaik di sekitarmu. Cek jadwal real-time, lakukan pembayaran instan, dan siap bermain bersama tim kebanggaanmu!
                </p>
                <div class="mt-10 flex justify-center gap-4">
                    <a href="#courts" class="bg-orange-500 text-white px-8 py-4 rounded-full font-bold text-lg hover:bg-orange-600 shadow-xl shadow-orange-500/40 transition transform hover:-translate-y-1">Cari Lapangan Sekarang</a>
                </div>
            </div>
        </div>

        <!-- Courts Section -->
        <div id="courts" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-extrabold mb-4">Pilihan Lapangan Terbaik</h2>
                <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">Kami menyediakan lapangan berkualitas standar nasional dan internasional untuk memaksimalkan performa permainan Anda.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div v-for="court in courts" :key="court.id" class="group bg-white dark:bg-gray-800 rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-300 flex flex-col border border-gray-100 dark:border-gray-700">
                    <div class="h-56 bg-gray-200 dark:bg-gray-700 relative overflow-hidden">
                        <img v-if="court.image_path" :src="court.image_path" alt="Court Image" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" />
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-100 dark:bg-gray-700">
                            <svg class="w-16 h-16 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div class="absolute top-4 right-4 bg-white/90 dark:bg-gray-900/90 backdrop-blur-md text-sm font-bold px-4 py-1.5 rounded-full text-blue-600 dark:text-blue-400 shadow-sm">
                            Rp {{ number_format(court.price_per_hour, 0, ',', '.') }} <span class="text-xs font-normal text-gray-500">/ jam</span>
                        </div>
                    </div>
                    <div class="p-8 flex-1 flex flex-col">
                        <h3 class="text-2xl font-bold mb-3 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">{{ court.name }}</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-6 line-clamp-2 leading-relaxed">{{ court.description }}</p>
                        
                        <div class="flex flex-wrap gap-2 mb-8 mt-auto">
                            <span v-for="facility in court.facilities" :key="facility" class="bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs px-3 py-1.5 rounded-lg font-semibold">
                                {{ facility }}
                            </span>
                        </div>

                        <Link :href="route('courts.show', court.id)" class="w-full block text-center bg-gray-900 dark:bg-white text-white dark:text-gray-900 py-3.5 rounded-xl font-bold hover:bg-blue-600 dark:hover:bg-blue-500 hover:text-white dark:hover:text-white transition shadow-md">
                            Lihat Jadwal & Booking
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 py-12 mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-gray-500 dark:text-gray-400">
                <p>&copy; 2026 VolleyZone. Hak Cipta Dilindungi.</p>
            </div>
        </footer>
    </div>
</template>

<script>
export default {
    methods: {
        number_format(number, decimals, dec_point, thousands_sep) {
            var n = !isFinite(+number) ? 0 : +number, 
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? '.' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? ',' : dec_point,
                toFixedFix = function (n, prec) {
                    var k = Math.pow(10, prec);
                    return Math.round(n * k) / k;
                },
                s = (prec ? toFixedFix(n, prec) : Math.round(n)).toString().split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }
    }
}
</script>
