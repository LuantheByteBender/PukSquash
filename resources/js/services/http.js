/**
 * Shared HTTP transport for the Laravel API.
 *
 * Two rules this file exists to enforce:
 *
 * 1. Identity travels in the Authorization header as a Firebase ID token and
 *    nowhere else. We never put a user id in a request body to say "this is me" —
 *    Laravel derives the acting user from the verified token. This is what stops a
 *    modified client from acting as somebody else.
 *
 * 2. A 409 is a normal, expected outcome, not a crash. It means another student
 *    took the court cell (or the second player position) first. Callers catch
 *    ApiConflict, tell the user plainly and refresh the availability grid.
 */

export class ApiError extends Error {
    constructor(message, status, code) {
        super(message);
        this.name = 'ApiError';
        this.status = status;
        this.code = code;
    }
}

/** 409 — the cell or player position was taken while the user was deciding. */
export class ApiConflict extends ApiError {
    constructor(message, code) {
        super(message, 409, code);
        this.name = 'ApiConflict';
    }
}

/**
 * Attach the Firebase ID token.
 *
 * Firebase is imported lazily because `services/firebase.js` throws at module
 * load when the VITE_FIREBASE_* values are missing. A static import would take
 * the whole application down on a fresh clone; this way an unauthenticated
 * request is simply sent without a token and Laravel answers 401, which is the
 * correct outcome anyway.
 */
async function authHeader(forceRefresh = false) {
    try {
        const { auth, authReady } = await import('./firebase.js');
        try {
            await authReady;
        } catch {
        }
        const user = auth?.currentUser;

        if (!user) {
            return {};
        }

        const token = await user.getIdToken(forceRefresh);

        return { Authorization: `Bearer ${token}` };
    } catch {
        return {};
    }
}

function defaultMessage(status) {
    if (status === 401) return 'Your session has expired. Please sign in again.';
    if (status === 403) return 'You are not authorised to do that.';
    if (status === 404) return 'That could not be found.';
    if (status === 422) return 'Some of the details are not valid.';
    if (status >= 500) return 'The server had a problem. Please try again in a moment.';

    return 'Something went wrong. Please try again.';
}

async function doFetch(method, path, body, forceRefresh) {
    const response = await fetch(`/api${path}`, {
        method,
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            ...(await authHeader(forceRefresh)),
        },
        body: body === undefined ? undefined : JSON.stringify(body),
    });

    return response;
}

export async function request(method, path, body) {
    let response = await doFetch(method, path, body, false);

    if (response.status === 401) {
        response = await doFetch(method, path, body, true);
    }

    if (response.status === 204) {
        return null;
    }

    let payload = null;

    try {
        payload = await response.json();
    } catch {
        // Empty or non-JSON body — leave payload as null.
    }

    if (response.ok) {
        return payload;
    }

    const message = payload?.message || defaultMessage(response.status);

    if (response.status === 409) {
        throw new ApiConflict(message, payload?.code);
    }

    throw new ApiError(message, response.status, payload?.code);
}

export const http = {
    get: (path) => request('GET', path),
    post: (path, body) => request('POST', path, body),
    patch: (path, body) => request('PATCH', path, body),
    put: (path, body) => request('PUT', path, body),
    delete: (path, body) => request('DELETE', path, body),
};

/** Build a query string, dropping empty values. */
export function query(params) {
    const search = new URLSearchParams(
        Object.entries(params).filter(
            ([, value]) => value !== undefined && value !== null && value !== ''
        )
    ).toString();

    return search ? `?${search}` : '';
}
