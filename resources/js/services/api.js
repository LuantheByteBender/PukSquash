import { auth } from './firebase.js';

export async function getCurrentUser() {
    const user = auth.currentUser;

    if (!user) {
        throw new Error('User is not logged in.');
    }

    const token = await user.getIdToken(true);

    const response = await fetch('/api/me', {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json',
        },
    });

    const text = await response.text();

    console.log('Status:', response.status);
    console.log('Response:', text);

    if (!response.ok) {
        throw new Error(
            `Backend authentication failed (${response.status}): ${text}`
        );
    }

    return JSON.parse(text);
}

export async function getCourts() {
    const user = auth.currentUser;

    if (!user) {
        throw new Error('User is not logged in.');
    }

    const token = await user.getIdToken(true);

    const response = await fetch('/api/courts', {
        method: 'GET',
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json',
        },
    });

    const text = await response.text();

    console.log('Courts status:', response.status);
    console.log('Courts response:', text);

    if (!response.ok) {
        throw new Error(
            `Failed to retrieve courts (${response.status}): ${text}`
        );
    }

    const result = JSON.parse(text);

    return result.data;
}