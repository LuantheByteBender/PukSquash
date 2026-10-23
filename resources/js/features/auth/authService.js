import {
    createUserWithEmailAndPassword,
    signInWithEmailAndPassword,
    signOut,
    sendEmailVerification,
    sendPasswordResetEmail,
} from 'firebase/auth';

async function getAuthInstance() {
    try {
        const { auth } = await import('../../services/firebase.js');
        return auth;
    } catch {
        throw new Error('Firebase authentication is unavailable. Check the VITE_FIREBASE_* values in .env.');
    }
}

function normalizeEmail(email) {
    return String(email ?? '').trim();
}

export function friendlyAuthError(error) {
    const code = error?.code;

    if (
        code === 'auth/invalid-credential' ||
        code === 'auth/wrong-password' ||
        code === 'auth/user-not-found'
    ) {
        return 'Incorrect email or password.';
    }

    if (code === 'auth/requires-recent-login') {
        return 'For security reasons, please sign in again and retry.';
    }

    if (code === 'auth/too-many-requests') {
        return 'Too many sign-in attempts. Please try again later.';
    }

    if (code === 'auth/invalid-email') {
        return 'Please enter a valid email address.';
    }

    if (code === 'auth/email-already-in-use') {
        return 'An account already exists for this email address.';
    }

    if (code === 'auth/weak-password') {
        return 'Your password is too weak. Please choose a stronger password.';
    }

    if (code === 'auth/network-request-failed') {
        return 'Network error. Please check your connection and try again.';
    }

    return error?.message || 'Something went wrong. Please try again.';
}

export async function registerUser(email, password) {
    try {
        const auth = await getAuthInstance();
        const result = await createUserWithEmailAndPassword(
            auth,
            normalizeEmail(email),
            password
        );

        await sendEmailVerification(result.user);

        return result;
    } catch (error) {
        throw new Error(friendlyAuthError(error));
    }
}

export async function loginUser(email, password) {
    try {
        const auth = await getAuthInstance();
        return await signInWithEmailAndPassword(
            auth,
            normalizeEmail(email),
            password
        );
    } catch (error) {
        throw new Error(friendlyAuthError(error));
    }
}

export function logoutUser() {
    return getAuthInstance()
        .then((auth) => signOut(auth));
}

export async function sendPasswordReset(email) {
    try {
        const auth = await getAuthInstance();
        await sendPasswordResetEmail(auth, normalizeEmail(email));
    } catch (error) {
        throw new Error(friendlyAuthError(error));
    }
}