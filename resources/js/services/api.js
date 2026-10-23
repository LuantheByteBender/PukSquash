export async function getCurrentUser() {
    const { http } = await import('./http.js');
    return http.get('/me');
}