<script setup>
import { onMounted, ref } from 'vue';
import { refreshAccount, updateAccount, useAccount } from '../composables/useAccount.js';

const isEditing = ref(false);
const { profile: accountProfile } = useAccount();
const error = ref('');
const saving = ref(false);

const coach = ref({
    firstName: '',
    lastName: '',
    email: '',
    phone: '',
    speciality: 'Squash Coaching',
    experience: '5 years',
    availability: 'Monday - Friday',
    bio: 'Experienced squash coach helping players improve their technique, fitness and confidence on court.'
});

function applyFromAccount(next) {
    if (!next) return;

    const full = String(next.full_name ?? '').trim();
    const parts = full.split(/\s+/).filter(Boolean);

    coach.value.firstName = parts[0] ?? full;
    coach.value.lastName = parts.slice(1).join(' ');
    coach.value.email = String(next.email ?? '');
}

onMounted(async () => {
    error.value = '';

    try {
        const next = await refreshAccount();
        applyFromAccount(next);
    } catch (e) {
        error.value = e?.message ?? String(e);
    }
});

const saveProfile = async () => {
    error.value = '';
    saving.value = true;

    try {
        const fullName = `${coach.value.firstName} ${coach.value.lastName}`.trim();
        await updateAccount({
            full_name: fullName,
            student_number: null,
        });

        applyFromAccount(accountProfile.value);
        isEditing.value = false;
    } catch (e) {
        error.value = e?.message ?? String(e);
    } finally {
        saving.value = false;
    }
};
</script>

<template>
    <div class="min-h-screen bg-gray-50">

        <div class="mx-auto max-w-5xl px-6 py-10">
            <div class="rounded-xl bg-white p-6 shadow-md">
                <h1 class="text-xl font-bold text-gray-900">Profile</h1>
                <p class="mt-2 text-gray-600">This page has moved. Use the Profile link in the menu.</p>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="bg-purple-900 text-white shadow-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">

                <div class="text-2xl font-bold">
                    PukSquash
                </div>

                <div class="flex items-center gap-6 text-sm font-medium">
                    <button class="transition hover:text-purple-200">
                        Home
                    </button>

                    <button class="transition hover:text-purple-200">
                        Coach Bookings
                    </button>

                    <button class="transition hover:text-purple-200">
                        Logout
                    </button>
                </div>

            </div>
        </nav>


        <!-- Main Content -->
        <main class="mx-auto max-w-5xl px-6 py-10">

            <!-- Heading -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    Coach Profile
                </h1>

                <p class="mt-2 text-gray-600">
                    View and manage your coaching profile.
                </p>
            </div>

            <p v-if="error" class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                {{ error }}
            </p>


            <!-- Profile Card -->
            <div class="overflow-hidden rounded-xl bg-white shadow-md">

                <!-- Profile Header -->
                <div class="bg-purple-900 px-8 py-8 text-white">

                    <div class="flex items-center gap-6">

                        <!-- Coach Avatar -->
                        <div
                            class="flex h-24 w-24 items-center justify-center rounded-full bg-white text-3xl font-bold text-purple-900"
                        >
                            {{ coach.firstName.charAt(0) }}
                        </div>

                        <div>
                            <h2 class="text-2xl font-bold">
                                {{ coach.firstName }} {{ coach.lastName }}
                            </h2>

                            <p class="mt-1 text-purple-200">
                                PukSquash Coach
                            </p>
                        </div>

                    </div>

                </div>


                <!-- Information -->
                <div class="p-8">

                    <div class="grid gap-6 md:grid-cols-2">

                        <!-- First Name -->
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">
                                First Name
                            </label>

                            <input
                                v-model="coach.firstName"
                                :disabled="!isEditing"
                                type="text"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3
                                       focus:border-purple-700 focus:outline-none
                                       focus:ring-2 focus:ring-purple-200
                                       disabled:bg-gray-100"
                            />
                        </div>


                        <!-- Last Name -->
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">
                                Last Name
                            </label>

                            <input
                                v-model="coach.lastName"
                                :disabled="!isEditing"
                                type="text"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3
                                       focus:border-purple-700 focus:outline-none
                                       focus:ring-2 focus:ring-purple-200
                                       disabled:bg-gray-100"
                            />
                        </div>


                        <!-- Email -->
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">
                                Email Address
                            </label>

                            <input
                                v-model="coach.email"
                                disabled
                                type="email"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3
                                       focus:border-purple-700 focus:outline-none
                                       focus:ring-2 focus:ring-purple-200
                                       disabled:bg-gray-100"
                            />
                        </div>


                        <!-- Phone -->
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">
                                Phone Number
                            </label>

                            <input
                                v-model="coach.phone"
                                disabled
                                type="tel"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3
                                       focus:border-purple-700 focus:outline-none
                                       focus:ring-2 focus:ring-purple-200
                                       disabled:bg-gray-100"
                            />
                        </div>


                        <!-- Speciality -->
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">
                                Coaching Speciality
                            </label>

                            <input
                                v-model="coach.speciality"
                                disabled
                                type="text"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3
                                       focus:border-purple-700 focus:outline-none
                                       focus:ring-2 focus:ring-purple-200
                                       disabled:bg-gray-100"
                            />
                        </div>


                        <!-- Experience -->
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">
                                Coaching Experience
                            </label>

                            <input
                                v-model="coach.experience"
                                disabled
                                type="text"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3
                                       focus:border-purple-700 focus:outline-none
                                       focus:ring-2 focus:ring-purple-200
                                       disabled:bg-gray-100"
                            />
                        </div>


                        <!-- Availability -->
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-semibold text-gray-700">
                                Availability
                            </label>

                            <input
                                v-model="coach.availability"
                                disabled
                                type="text"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3
                                       focus:border-purple-700 focus:outline-none
                                       focus:ring-2 focus:ring-purple-200
                                       disabled:bg-gray-100"
                            />
                        </div>


                        <!-- Bio -->
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-semibold text-gray-700">
                                Coach Biography
                            </label>

                            <textarea
                                v-model="coach.bio"
                                disabled
                                rows="4"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3
                                       focus:border-purple-700 focus:outline-none
                                       focus:ring-2 focus:ring-purple-200
                                       disabled:bg-gray-100"
                            ></textarea>
                        </div>

                    </div>


                    <!-- Buttons -->
                    <div class="mt-8 flex justify-end gap-4">

                        <button
                            v-if="!isEditing"
                            @click="isEditing = true"
                            class="rounded-lg bg-purple-900 px-6 py-3
                                   font-semibold text-white transition
                                   hover:bg-purple-800"
                        >
                            Edit Profile
                        </button>

                        <template v-else>

                            <button
                                @click="isEditing = false"
                                class="rounded-lg border border-gray-300 px-6 py-3
                                       font-semibold text-gray-700 transition
                                       hover:bg-gray-100"
                            >
                                Cancel
                            </button>

                            <button
                                @click="saveProfile"
                                :disabled="saving"
                                class="rounded-lg bg-purple-900 px-6 py-3
                                       font-semibold text-white transition
                                       hover:bg-purple-800"
                            >
                                Save Changes
                            </button>

                        </template>

                    </div>

                </div>

            </div>

        </main>

    </div>
</template>