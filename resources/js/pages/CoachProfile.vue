<script setup>
import { ref } from 'vue';

const isEditing = ref(false);

const coach = ref({
    firstName: 'Coach',
    lastName: 'Example',
    email: 'coach@nwu.ac.za',
    phone: '082 123 4567',
    speciality: 'Squash Coaching',
    experience: '5 years',
    availability: 'Monday - Friday',
    bio: 'Experienced squash coach helping players improve their technique, fitness and confidence on court.'
});

const saveProfile = () => {
    isEditing.value = false;
};
</script>

<template>
    <div class="min-h-screen bg-gray-50">

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
                                :disabled="!isEditing"
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
                                :disabled="!isEditing"
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
                                :disabled="!isEditing"
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
                                :disabled="!isEditing"
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
                                :disabled="!isEditing"
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
                                :disabled="!isEditing"
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