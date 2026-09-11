<script setup>
import { ref } from 'vue';

const isEditing = ref(false);

const profile = ref({
    firstName: 'Karla',
    lastName: 'Pienaar',
    studentNumber: '48172170',
    email: '48172170@nwu.ac.za',
    phone: '0832320400',
});

const editProfile = () => {
    isEditing.value = true;
};

const saveProfile = () => {
    isEditing.value = false;
};
</script>

<template>
    <div class="min-h-screen bg-gray-50">

        <!-- Navigation -->
        <nav class="bg-purple-900 text-white shadow-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">

                <!-- Logo / Name -->
                <div class="text-2xl font-bold">
                    PukSquash
                </div>

                <!-- Navigation Links -->
                <div class="flex items-center gap-6 text-sm font-medium">
                    <button class="transition hover:text-purple-200">
                        Home
                    </button>

                    <button class="transition hover:text-purple-200">
                        My Bookings
                    </button>

                    <button class="transition hover:text-purple-200">
                        Logout
                    </button>
                </div>

            </div>
        </nav>


        <!-- Main Content -->
        <main class="mx-auto max-w-5xl px-6 py-10">

            <!-- Page Heading -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    My Profile
                </h1>

                <p class="mt-2 text-gray-600">
                    View and manage your PukSquash profile information.
                </p>
            </div>


            <!-- Profile Card -->
            <div class="overflow-hidden rounded-xl bg-white shadow-md">

                <!-- Profile Header -->
                <div class="bg-purple-900 px-8 py-8 text-white">

                    <div class="flex items-center gap-6">

                        <!-- Profile Circle -->
                        <div
                            class="flex h-24 w-24 items-center justify-center rounded-full bg-white text-3xl font-bold text-purple-900"
                        >
                            {{ profile.firstName.charAt(0) }}
                        </div>

                        <!-- Name -->
                        <div>
                            <h2 class="text-2xl font-bold">
                                {{ profile.firstName }} {{ profile.lastName }}
                            </h2>

                            <p class="mt-1 text-purple-200">
                                NWU Student
                            </p>
                        </div>

                    </div>

                </div>


                <!-- Profile Information -->
                <div class="p-8">

                    <div class="grid gap-6 md:grid-cols-2">

                        <!-- First Name -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-semibold text-gray-700"
                            >
                                First Name
                            </label>

                            <input
                                v-model="profile.firstName"
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
                            <label
                                class="mb-2 block text-sm font-semibold text-gray-700"
                            >
                                Last Name
                            </label>

                            <input
                                v-model="profile.lastName"
                                :disabled="!isEditing"
                                type="text"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3
                                       focus:border-purple-700 focus:outline-none
                                       focus:ring-2 focus:ring-purple-200
                                       disabled:bg-gray-100"
                            />
                        </div>


                        <!-- Student Number -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-semibold text-gray-700"
                            >
                                Student Number
                            </label>

                            <input
                                v-model="profile.studentNumber"
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
                            <label
                                class="mb-2 block text-sm font-semibold text-gray-700"
                            >
                                Email Address
                            </label>

                            <input
                                v-model="profile.email"
                                :disabled="!isEditing"
                                type="email"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3
                                       focus:border-purple-700 focus:outline-none
                                       focus:ring-2 focus:ring-purple-200
                                       disabled:bg-gray-100"
                            />
                        </div>


                        <!-- Phone -->
                        <div class="md:col-span-2">
                            <label
                                class="mb-2 block text-sm font-semibold text-gray-700"
                            >
                                Phone Number
                            </label>

                            <input
                                v-model="profile.phone"
                                :disabled="!isEditing"
                                type="tel"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3
                                       focus:border-purple-700 focus:outline-none
                                       focus:ring-2 focus:ring-purple-200
                                       disabled:bg-gray-100"
                            />
                        </div>

                    </div>


                    <!-- Buttons -->
                    <div class="mt-8 flex justify-end gap-4">

                        <!-- Edit Button -->
                        <button
                            v-if="!isEditing"
                            @click="editProfile"
                            class="rounded-lg bg-purple-900 px-6 py-3
                                   font-semibold text-white transition
                                   hover:bg-purple-800"
                        >
                            Edit Profile
                        </button>


                        <!-- Save / Cancel -->
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