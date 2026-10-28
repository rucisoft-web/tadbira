<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({ sectors: Object });

const form = useForm({
    name: '',
    email: '',
    organization_name: '',
    sector: 'kementerian',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Daftar" />

        <h1 class="mb-1 text-xl font-bold text-zamrud-950">Daftarkan organisasi Anda</h1>
        <p class="mb-6 text-sm text-slate-500">Istilah anggaran menyesuaikan sektor yang dipilih.</p>

        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <InputLabel for="organization_name" value="Nama organisasi / satuan kerja" />
                <TextInput id="organization_name" v-model="form.organization_name" type="text" class="mt-1 block w-full" required autofocus />
                <InputError class="mt-2" :message="form.errors.organization_name" />
            </div>

            <div>
                <InputLabel for="sector" value="Sektor" />
                <select id="sector" v-model="form.sector" class="masukan mt-1 block w-full">
                    <option v-for="(label, key) in sectors" :key="key" :value="key">{{ label }}</option>
                </select>
                <InputError class="mt-2" :message="form.errors.sector" />
            </div>

            <div>
                <InputLabel for="name" value="Nama Anda" />
                <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required autocomplete="name" />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Surel" />
                <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <InputLabel for="password" value="Kata sandi" />
                    <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>
                <div>
                    <InputLabel for="password_confirmation" value="Ulangi kata sandi" />
                    <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                    <InputError class="mt-2" :message="form.errors.password_confirmation" />
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <Link :href="route('login')" class="text-sm text-slate-600 underline hover:text-zamrud-800">Sudah punya akun?</Link>
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Daftar</PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
