import { auth } from './firebase.js';

import { startLoading, stopLoading } from './loading.js';

export async function getCurrentUser() {
    const user = auth.currentUser;

    if (!user) {
        throw new Error('User is not logged in.');
    }

    const token = await user.getIdToken();

    startLoading();

    try {
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
    } finally {
        stopLoading();
    }
}