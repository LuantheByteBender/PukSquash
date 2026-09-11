<script setup>
import { ref, computed } from 'vue';

const selectedFilter = ref('upcoming');

const bookings = ref([
    {
        id: 1,
        player: 'Liam Jacobs',
        date: '12 September 2026',
        time: '09:00 - 10:00',
        court: 'Court 1',
        status: 'Confirmed',
    },
    {
        id: 2,
        player: 'Emma Smith',
        date: '12 September 2026',
        time: '11:00 - 12:00',
        court: 'Court 3',
        status: 'Confirmed',
    },
    {
        id: 3,
        player: 'Daniel Williams',
        date: '14 September 2026',
        time: '14:00 - 15:00',
        court: 'Court 2',
        status: 'Pending',
    },
    {
        id: 4,
        player: 'Sarah Adams',
        date: '16 September 2026',
        time: '10:00 - 11:00',
        court: 'Court 4',
        status: 'Confirmed',
    },
]);

const filteredBookings = computed(() => {
    if (selectedFilter.value === 'all') {
        return bookings.value;
    }

    if (selectedFilter.value === 'confirmed') {
        return bookings.value.filter(
            (booking) => booking.status === 'Confirmed'
        );
    }

    if (selectedFilter.value === 'pending') {
        return bookings.value.filter(
            (booking) => booking.status === 'Pending'
        );
    }

    return bookings.value;
});

const cancelBooking = (id) => {
    const booking = bookings.value.find((booking) => booking.id === id);

    if (booking) {
        booking.status = 'Cancelled';
    }
};
</script>

<template>
    <div class="min-h-screen bg-gray-50">

        <!-- Navigation -->
        <nav
            class="text-white shadow-md"
            style="background-color: #6C3D91;"
        >
            <div
                class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4"
            >

                <!-- Logo -->
                <div class="text-2xl font-bold">
                    PukSquash
                </div>

                <!-- Navigation Links -->
                <div class="flex items-center gap-6 text-sm font-medium">

                    <button class="transition hover:opacity-80">
                        Home
                    </button>

                    <button class="border-b-2 border-white pb-1">
                        Coach Bookings
                    </button>

                    <button class="transition hover:opacity-80">
                        Coach Profile
                    </button>

                    <button class="transition hover:opacity-80">
                        Logout
                    </button>

                </div>
            </div>
        </nav>


        <!-- Main Content -->
        <main class="mx-auto max-w-7xl px-6 py-10">

            <!-- Page Heading -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    Coach Bookings
                </h1>

                <p class="mt-2 text-gray-600">
                    View and manage your upcoming squash court bookings.
                </p>
            </div>


            <!-- Summary Cards -->
            <div class="mb-8 grid gap-6 md:grid-cols-3">

                <!-- Total -->
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">
                        Total Bookings
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900">
                        {{ bookings.length }}
                    </p>
                </div>


                <!-- Confirmed -->
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">
                        Confirmed
                    </p>

                    <p class="mt-2 text-3xl font-bold" style="color: #6C3D91;">
                        {{
                            bookings.filter(
                                (booking) => booking.status === 'Confirmed'
                            ).length
                        }}
                    </p>
                </div>


                <!-- Pending -->
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">
                        Pending
                    </p>

                    <p class="mt-2 text-3xl font-bold" style="color: #00889C;">
                        {{
                            bookings.filter(
                                (booking) => booking.status === 'Pending'
                            ).length
                        }}
                    </p>
                </div>

            </div>


            <!-- Filters -->
            <div class="mb-6 flex flex-wrap gap-3">

                <button
                    @click="selectedFilter = 'upcoming'"
                    :class="
                        selectedFilter === 'upcoming'
                            ? 'text-white'
                            : 'bg-white text-gray-700 border border-gray-300'
                    "
                    :style="
                        selectedFilter === 'upcoming'
                            ? 'background-color: #6C3D91;'
                            : ''
                    "
                    class="rounded-lg px-5 py-2.5 text-sm font-semibold transition hover:opacity-90"
                >
                    Upcoming
                </button>

                <button
                    @click="selectedFilter = 'all'"
                    :class="
                        selectedFilter === 'all'
                            ? 'text-white'
                            : 'bg-white text-gray-700 border border-gray-300'
                    "
                    :style="
                        selectedFilter === 'all'
                            ? 'background-color: #6C3D91;'
                            : ''
                    "
                    class="rounded-lg px-5 py-2.5 text-sm font-semibold transition hover:opacity-90"
                >
                    All
                </button>

                <button
                    @click="selectedFilter = 'confirmed'"
                    :class="
                        selectedFilter === 'confirmed'
                            ? 'text-white'
                            : 'bg-white text-gray-700 border border-gray-300'
                    "
                    :style="
                        selectedFilter === 'confirmed'
                            ? 'background-color: #6C3D91;'
                            : ''
                    "
                    class="rounded-lg px-5 py-2.5 text-sm font-semibold transition hover:opacity-90"
                >
                    Confirmed
                </button>

                <button
                    @click="selectedFilter = 'pending'"
                    :class="
                        selectedFilter === 'pending'
                            ? 'text-white'
                            : 'bg-white text-gray-700 border border-gray-300'
                    "
                    :style="
                        selectedFilter === 'pending'
                            ? 'background-color: #6C3D91;'
                            : ''
                    "
                    class="rounded-lg px-5 py-2.5 text-sm font-semibold transition hover:opacity-90"
                >
                    Pending
                </button>

            </div>


            <!-- Bookings Table -->
            <div class="overflow-hidden rounded-xl bg-white shadow-sm">

                <!-- Table Header -->
                <div class="border-b border-gray-200 px-6 py-5">
                    <h2 class="text-lg font-bold text-gray-900">
                        Booking Schedule
                    </h2>
                </div>


                <!-- Desktop Table -->
                <div class="hidden overflow-x-auto md:block">

                    <table class="w-full">

                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                                >
                                    Player
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                                >
                                    Date
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                                >
                                    Time
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                                >
                                    Court
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                                >
                                    Status
                                </th>

                                <th
                                    class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-gray-500"
                                >
                                    Action
                                </th>
                            </tr>
                        </thead>


                        <tbody class="divide-y divide-gray-200">

                            <tr
                                v-for="booking in filteredBookings"
                                :key="booking.id"
                                class="transition hover:bg-gray-50"
                            >

                                <!-- Player -->
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">

                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold text-white"
                                            style="background-color: #6C3D91;"
                                        >
                                            {{ booking.player.charAt(0) }}
                                        </div>

                                        <span class="font-semibold text-gray-900">
                                            {{ booking.player }}
                                        </span>

                                    </div>
                                </td>


                                <!-- Date -->
                                <td class="px-6 py-5 text-sm text-gray-700">
                                    {{ booking.date }}
                                </td>


                                <!-- Time -->
                                <td class="px-6 py-5 text-sm text-gray-700">
                                    {{ booking.time }}
                                </td>


                                <!-- Court -->
                                <td class="px-6 py-5 text-sm font-medium text-gray-700">
                                    {{ booking.court }}
                                </td>


                                <!-- Status -->
                                <td class="px-6 py-5">

                                    <span
                                        v-if="booking.status === 'Confirmed'"
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                        style="background-color: #E8F4F5; color: #00889C;"
                                    >
                                        Confirmed
                                    </span>

                                    <span
                                        v-else-if="booking.status === 'Pending'"
                                        class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-700"
                                    >
                                        Pending
                                    </span>

                                    <span
                                        v-else
                                        class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-500"
                                    >
                                        Cancelled
                                    </span>

                                </td>


                                <!-- Action -->
                                <td class="px-6 py-5 text-right">

                                    <button
                                        v-if="booking.status !== 'Cancelled'"
                                        @click="cancelBooking(booking.id)"
                                        class="text-sm font-semibold text-red-600 transition hover:text-red-800"
                                    >
                                        Cancel
                                    </button>

                                    <span
                                        v-else
                                        class="text-sm text-gray-400"
                                    >
                                        Cancelled
                                    </span>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>


                <!-- Mobile Cards -->
                <div class="space-y-4 p-4 md:hidden">

                    <div
                        v-for="booking in filteredBookings"
                        :key="booking.id"
                        class="rounded-lg border border-gray-200 p-5"
                    >

                        <div class="flex items-start justify-between">

                            <div class="flex items-center gap-3">

                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold text-white"
                                    style="background-color: #6C3D91;"
                                >
                                    {{ booking.player.charAt(0) }}
                                </div>

                                <div>
                                    <p class="font-semibold text-gray-900">
                                        {{ booking.player }}
                                    </p>

                                    <p class="text-sm text-gray-500">
                                        {{ booking.court }}
                                    </p>
                                </div>

                            </div>

                            <span
                                v-if="booking.status === 'Confirmed'"
                                class="rounded-full px-3 py-1 text-xs font-semibold"
                                style="background-color: #E8F4F5; color: #00889C;"
                            >
                                Confirmed
                            </span>

                            <span
                                v-else-if="booking.status === 'Pending'"
                                class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-700"
                            >
                                Pending
                            </span>

                            <span
                                v-else
                                class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-500"
                            >
                                Cancelled
                            </span>

                        </div>


                        <div class="mt-4 grid grid-cols-2 gap-4 text-sm">

                            <div>
                                <p class="text-gray-500">
                                    Date
                                </p>

                                <p class="mt-1 font-medium text-gray-900">
                                    {{ booking.date }}
                                </p>
                            </div>

                            <div>
                                <p class="text-gray-500">
                                    Time
                                </p>

                                <p class="mt-1 font-medium text-gray-900">
                                    {{ booking.time }}
                                </p>
                            </div>

                        </div>


                        <button
                            v-if="booking.status !== 'Cancelled'"
                            @click="cancelBooking(booking.id)"
                            class="mt-5 w-full rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50"
                        >
                            Cancel Booking
                        </button>

                    </div>

                </div>


                <!-- Empty State -->
                <div
                    v-if="filteredBookings.length === 0"
                    class="px-6 py-16 text-center"
                >
                    <div class="text-4xl">
                        📅
                    </div>

                    <h3 class="mt-4 text-lg font-bold text-gray-900">
                        No bookings found
                    </h3>

                    <p class="mt-2 text-sm text-gray-500">
                        There are currently no bookings matching this filter.
                    </p>
                </div>

            </div>

        </main>

    </div>
</template>