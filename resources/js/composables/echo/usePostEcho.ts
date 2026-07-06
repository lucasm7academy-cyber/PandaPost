import { useEcho } from '@laravel/echo-vue';

export const usePostEcho = <T = unknown>(
    postId: string,
    event: string | string[],
    callback: (payload: T) => void,
) => {
    try {
        return useEcho<T>(`post.${postId}`, event, callback);
    } catch {
        console.warn('Unable to initialize real-time notifications');
    }
};
