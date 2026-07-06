import { usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';

import type { Auth } from '@/types';

export const useWorkspaceEcho = <T = unknown>(
    event: string | string[],
    callback: (payload: T) => void,
) => {
    const page = usePage();
    const workspaceId = (page.props.auth as Auth | undefined)?.currentWorkspace?.id;

    if (!workspaceId) {
        return;
    }

    try {
        return useEcho<T>(`workspace.${workspaceId}`, event, callback);
    } catch {
        console.warn('Unable to initialize real-time notifications');
    }
};
