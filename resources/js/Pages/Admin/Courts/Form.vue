<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    court: Object, // null = mode tambah, filled = mode edit
});

const isEditing = computed(() => !!props.court);

// Parse fasilitas dari court (bisa array atau string JSON)
const parseFacilities = () => {
    if (!props.court?.facilities) return [];
    if (Array.isArray(props.court.facilities)) return [...props.court.facilities];
    try { return JSON.parse(props.court.facilities); } catch { return []; }
};

const facilities = ref(parseFacilities());
const newFacility = ref('');

const addFacility = () => {
    const val = newFacility.value.trim();
    if (val && !facilities.value.includes(val)) {
        facilities.value.push(val);
    }
    newFacility.value = '';
};

const removeFacility = (index) => {
    facilities.value.splice(index, 1);
};

const handleFacilityKeydown = (e) => {
    if (e.key === 'Enter') {
        e.preventDefault();
        addFacility();
    }
};

// Image preview
const imagePreview = ref(props.court?.image_path || null);
const imageFile = ref(null);

const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    imageFile.value = file;
    imagePreview.value = URL.createObjectURL(file);
};

const removeImage = () => {
    imagePreview.value = null;
    imageFile.value = null;
};

const form = useForm({
    name:           props.court?.name ?? '',
    description:    props.court?.description ?? '',
    price_per_hour: props.court?.price_per_hour ?? '',
    is_active:      props.court?.is_active ?? true,
    facilities:     [],
    image:          null,
    _method:        isEditing.value ? 'PUT' : 'POST',
});

const submit = () => {
    form.facilities = [...facilities.value];
    form.image      = imageFile.value;

    if (isEditing.value) {
        form.post(`/admin/courts/${props.court.id}`, {
            forceFormData: true,
        });
    } else {
        form.post('/admin/courts', {
            forceFormData: true,
        });
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Edit Lapangan' : 'Tambah Lapangan'" />

    <div class="min-h-screen bg-gray-50 font-sans">
        <!-- Topbar -->
        <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link href="/" class="font-black text-xl text-blue-600">Volley<span class="text-orange-500">Zone</span></Link>
                    <span class="text-gray-300">/</span>
                    <Link href="/admin/courts" class="text-sm text-gray-500 hover:text-blue-600 transition">Kelola Lapangan</Link>
                    <span class="text-gray-300">/</span>
                    <span class="text-sm font-semibold text-gray-700">{{ isEditing ? 'Edit' : 'Tambah' }}</span>
                </div>
                <Link href="/admin/courts" class="text-sm text-gray-500 hover:text-blue-600 transition">← Kembali</Link>
            </div>
        </header>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900">
                    {{ isEditing ? 'Edit Lapangan' : 'Tambah Lapangan Baru' }}
                </h1>
                <p class="text-gray-500 text-sm mt-1">
                    {{ isEditing ? `Mengubah data lapangan "${court.name}"` : 'Isi detail lapangan voli baru.' }}
                </p>
            </div>

            <form @submit.prevent="submit" enctype="multipart/form-data">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    <!-- Left column: Image upload -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                            <h2 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Foto Lapangan
                            </h2>

                            <!-- Preview area -->
                            <div class="relative w-full aspect-video rounded-2xl overflow-hidden bg-gray-100 mb-4 border-2 border-dashed border-gray-200">
                                <img v-if="imagePreview" :src="imagePreview" alt="Preview" class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-400 gap-2">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span class="text-xs">Belum ada foto</span>
                                </div>

                                <button v-if="imagePreview" type="button" @click="removeImage"
                                    class="absolute top-2 right-2 w-7 h-7 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition shadow">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <label class="w-full cursor-pointer">
                                <div class="w-full py-2.5 rounded-xl border border-blue-200 bg-blue-50 text-blue-600 font-semibold text-sm text-center hover:bg-blue-100 transition">
                                    {{ imagePreview ? 'Ganti Foto' : 'Pilih Foto' }}
                                </div>
                                <input type="file" accept="image/jpg,image/jpeg,image/png,image/webp" class="hidden" @change="handleImageChange" />
                            </label>
                            <p class="text-xs text-gray-400 text-center mt-2">JPG, PNG, WEBP. Maks 2MB.</p>

                            <p v-if="form.errors.image" class="text-red-500 text-xs mt-2">{{ form.errors.image }}</p>
                        </div>
                    </div>

                    <!-- Right column: Court details -->
                    <div class="lg:col-span-2 space-y-6">

                        <!-- Basic Info -->
                        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                            <h2 class="font-bold text-gray-800 mb-5 flex items-center gap-2">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Informasi Lapangan
                            </h2>

                            <div class="space-y-4">
                                <!-- Name -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Lapangan <span class="text-red-500">*</span></label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        placeholder="cth: Lapangan Utama (Indoor)"
                                        class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                                    />
                                    <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                                </div>

                                <!-- Description -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Deskripsi <span class="text-red-500">*</span></label>
                                    <textarea
                                        v-model="form.description"
                                        rows="3"
                                        placeholder="Deskripsikan lapangan ini..."
                                        class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition resize-none"
                                    ></textarea>
                                    <p v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</p>
                                </div>

                                <!-- Price -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Harga per Jam (Rp) <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-gray-400 font-semibold">Rp</span>
                                        <input
                                            v-model="form.price_per_hour"
                                            type="number"
                                            min="0"
                                            step="1000"
                                            placeholder="150000"
                                            class="w-full rounded-xl border border-gray-200 pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                                        />
                                    </div>
                                    <p v-if="form.errors.price_per_hour" class="text-red-500 text-xs mt-1">{{ form.errors.price_per_hour }}</p>
                                </div>

                                <!-- Status toggle -->
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-700">Status Lapangan</p>
                                        <p class="text-xs text-gray-400">Lapangan tidak aktif tidak akan ditampilkan di beranda.</p>
                                    </div>
                                    <button
                                        type="button"
                                        @click="form.is_active = !form.is_active"
                                        :class="form.is_active ? 'bg-green-500' : 'bg-gray-300'"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none"
                                    >
                                        <span :class="form.is_active ? 'translate-x-6' : 'translate-x-1'"
                                            class="inline-block w-4 h-4 bg-white rounded-full shadow transition-transform duration-200"></span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Facilities -->
                        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                            <h2 class="font-bold text-gray-800 mb-5 flex items-center gap-2">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                Fasilitas
                            </h2>

                            <!-- Existing facilities as chips -->
                            <div class="flex flex-wrap gap-2 mb-4 min-h-[36px]">
                                <span v-if="facilities.length === 0" class="text-gray-400 text-sm">Belum ada fasilitas ditambahkan.</span>
                                <span v-for="(fac, i) in facilities" :key="i"
                                    class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 text-sm font-semibold px-3 py-1.5 rounded-xl">
                                    {{ fac }}
                                    <button type="button" @click="removeFacility(i)"
                                        class="text-blue-400 hover:text-red-500 transition focus:outline-none">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </span>
                            </div>

                            <!-- Add new facility input -->
                            <div class="flex gap-2">
                                <input
                                    v-model="newFacility"
                                    @keydown="handleFacilityKeydown"
                                    type="text"
                                    placeholder="cth: AC, Tribun, Parkir..."
                                    class="flex-1 rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                                />
                                <button
                                    type="button"
                                    @click="addFacility"
                                    class="px-4 py-2.5 bg-blue-600 text-white rounded-xl font-bold text-sm hover:bg-blue-700 transition"
                                >Tambah</button>
                            </div>
                            <p class="text-xs text-gray-400 mt-2">Tekan Enter atau klik Tambah untuk menambah fasilitas.</p>
                        </div>

                        <!-- Submit -->
                        <div class="flex gap-3 justify-end">
                            <Link href="/admin/courts"
                                class="px-6 py-3 rounded-xl border border-gray-200 text-gray-600 font-semibold text-sm hover:bg-gray-50 transition">
                                Batal
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-8 py-3 bg-orange-500 text-white font-bold text-sm rounded-xl hover:bg-orange-600 shadow-lg shadow-orange-500/30 transition disabled:opacity-60 disabled:cursor-not-allowed"
                            >
                                {{ form.processing ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Simpan Lapangan') }}
                            </button>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
