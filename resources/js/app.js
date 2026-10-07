import '../css/app.css';
import 'vue-sonner/style.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import ThemeProvider from '@/Context/ThemeContext.vue';
import { installPublicMotion } from '@/public/motion.js';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

const syncCsrfToken = (token) => {
    if (!token) {
        return;
    }

    const metaTag = document.head.querySelector('meta[name="csrf-token"]');

    if (metaTag) {
        metaTag.setAttribute('content', token);
    }

    if (window.axios) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    }
};



createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        syncCsrfToken(props.initialPage.props.csrfToken);

        router.on('navigate', (event) => {
            syncCsrfToken(event.detail.page.props.csrfToken);
        });

        const app = createApp({
            render: () => h(ThemeProvider, null, { default: () => h(App, props) }),
        });
        app.use(plugin);
        app.use(ZiggyVue);
        installPublicMotion(app);
        app.mount(el);
    },
    progress: {
        color: '#F59E0B',
    },
});
