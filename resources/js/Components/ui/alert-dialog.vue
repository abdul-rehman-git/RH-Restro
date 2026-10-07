<script>
import { computed, h, inject, provide } from 'vue';

const alertDialogOpenKey = Symbol('alert-dialog-open');

const AlertDialog = {
    props: {
        open: {
            type: Boolean,
            default: false,
        },
    },
    setup(props, { slots }) {
        provide(alertDialogOpenKey, computed(() => props.open));

        return () => slots.default?.({ open: props.open });
    },
};

const AlertDialogTrigger = {
    props: {
        asChild: {
            type: Boolean,
            default: false,
        },
    },
    setup(props, { slots }) {
        return () => {
            const children = slots.default?.() ?? [];

            if (props.asChild && children.length === 1) {
                return children[0];
            }

            return h('button', { type: 'button' }, children);
        };
    },
};

const AlertDialogContent = {
    props: {
        class: {
            type: String,
            default: '',
        },
    },
    setup(props, { slots }) {
        const isOpen = inject(alertDialogOpenKey, computed(() => false));

        return () =>
            !isOpen.value
                ? null
                :
            h('div', { class: `fixed inset-0 z-50 flex items-center justify-center ${props.class}` }, [
                h('div', { class: 'fixed inset-0 bg-slate-950/50 dark:bg-slate-950/80' }),
                h(
                    'div',
                    {
                        class: 'relative z-10 mx-4 w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900',
                    },
                    slots.default?.(),
                ),
            ]);
    },
};

const AlertDialogHeader = {
    setup(_, { slots }) {
        return () => h('div', { class: 'mb-4' }, slots.default?.());
    },
};

const AlertDialogTitle = {
    setup(_, { slots }) {
        return () => h('h2', { class: 'text-lg font-semibold text-slate-900 dark:text-slate-100' }, slots.default?.());
    },
};

const AlertDialogDescription = {
    setup(_, { slots }) {
        return () => h('p', { class: 'text-sm text-slate-600 dark:text-slate-400' }, slots.default?.());
    },
};

const AlertDialogFooter = {
    setup(_, { slots }) {
        return () => h('div', { class: 'mt-6 flex justify-end gap-3' }, slots.default?.());
    },
};

const AlertDialogAction = {
    setup(_, { slots }) {
        return () =>
            h(
                'button',
                {
                    type: 'button',
                    class: 'inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-800 dark:hover:bg-white',
                },
                slots.default?.(),
            );
    },
};

const AlertDialogCancel = {
    setup(_, { slots }) {
        return () =>
            h(
                'button',
                {
                    type: 'button',
                    class: 'inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800',
                },
                slots.default?.(),
            );
    },
};

export {
    AlertDialog,
    AlertDialogTrigger,
    AlertDialogContent,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogAction,
    AlertDialogCancel,
};
</script>
