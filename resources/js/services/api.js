import { auth } from './firebase.js';

async function apiRequest(url, options = {}) {
    const user = auth.currentUser;

    if (!user) throw new Error('User is not logged in.');

    const token = await user.getIdToken(true);

    const response = await fetch(url, {
        ...options,
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json',
            'Content-Type': 'application/json',
            ...(options.headers || {}),
        },
    });

    if (!response.ok) {
        const text = await response.text();
        throw new Error(`API error (${response.status}): ${text}`);
    }

    if (response.status === 204) {
        return null;
    }

    return response.json();
}

export const getCurrentUser = () => apiRequest('/api/me');

export const getCourts = () => apiRequest('/api/courts');
export const getCourt = (courtId) => apiRequest(`/api/courts/${courtId}`);
export const createCourt = (data) =>
    apiRequest('/api/courts', {
        method: 'POST',
        body: JSON.stringify(data),
    });
export const updateCourt = (courtId, data) =>
    apiRequest(`/api/courts/${courtId}`, {
        method: 'PUT',
        body: JSON.stringify(data),
    });
export const deleteCourt = (courtId) =>
    apiRequest(`/api/courts/${courtId}`, {
        method: 'DELETE',
    });

export const getBookings = () => apiRequest('/api/bookings');
export const getBooking = (bookingId) => apiRequest(`/api/bookings/${bookingId}`);
export const createBooking = (data) =>
    apiRequest('/api/bookings', {
        method: 'POST',
        body: JSON.stringify(data),
    });
export const updateBooking = (bookingId, data) =>
    apiRequest(`/api/bookings/${bookingId}`, {
        method: 'PUT',
        body: JSON.stringify(data),
    });
export const deleteBooking = (bookingId) =>
    apiRequest(`/api/bookings/${bookingId}`, {
        method: 'DELETE',
    });