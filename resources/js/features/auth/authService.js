import {
    createUserWithEmailAndPassword,
    signInWithEmailAndPassword,
    signOut,
    sendEmailVerification,
} from 'firebase/auth';

import { auth } from '../../services/firebase.js';

export async function registerUser(email, password) {
    const result = await createUserWithEmailAndPassword(
        auth,
        email,
        password
    );

    await sendEmailVerification(result.user);

    return result;
}

export function loginUser(email, password) {
    return signInWithEmailAndPassword(
        auth,
        email,
        password
    );
}

export function logoutUser() {
    return signOut(auth);
}