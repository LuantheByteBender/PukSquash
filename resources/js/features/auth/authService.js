import {
    createUserWithEmailAndPassword,
    signInWithEmailAndPassword,
    signOut,
    sendEmailVerification,
} from 'firebase/auth';

import { auth } from '../../services/firebase.js';
import { startLoading, stopLoading } from '../../services/loading.js';

function normalizeEmail(email) {
    return String(email ?? '').trim();
}

function friendlyAuthError(error) {
    const code = error?.code;

    if (
        code === 'auth/invalid-credential' ||
        code === 'auth/wrong-password' ||
        code === 'auth/user-not-found'
    ) {
        return 'Incorrect email or password.';
    }

    if (code === 'auth/too-many-requests') {
        return 'Too many sign-in attempts. Please try again later.';
    }

    if (code === 'auth/invalid-email') {
        return 'Please enter a valid email address.';
    }

    if (code === 'auth/network-request-failed') {
        return 'Network error. Please check your connection and try again.';
    }

    return error?.message || 'Something went wrong. Please try again.';
}

export async function registerUser(email, password) {
    startLoading();

    try {
        const result = await createUserWithEmailAndPassword(
            auth,
            normalizeEmail(email),
            password
        );

        await sendEmailVerification(result.user);

        return result;
    } catch (error) {
        throw new Error(friendlyAuthError(error));
    } finally {
        stopLoading();
    }
}

export async function loginUser(email, password) {
    startLoading();

    try {
        return await signInWithEmailAndPassword(
            auth,
            normalizeEmail(email),
            password
        );
    } catch (error) {
        throw new Error(friendlyAuthError(error));
    } finally {
        stopLoading();
    }
}

export function logoutUser() {
    startLoading();

    return signOut(auth).finally(() => {
        stopLoading();
    });
}