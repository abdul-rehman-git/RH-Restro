<script>
import { h, inject, onMounted, onUnmounted, provide, ref, watch } from 'vue';

const dropdownMenuKey = Symbol('dropdown-menu');

const DropdownMenu = {
    props: {
        open: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['update:open'],
    setup(props, { emit, slots }) {
        const isOpen = ref(props.open);

        watch(
            () => props.open,
            (value) => {
                isOpen.value = value;
            },
        );

        const setOpen = (value) => {
            isOpen.value = value;
            emit('update:open', value);
        };

        const toggle = () => setOpen(!isOpen.value);

        const handleEscape = (event) => {
            if (event.key === 'Escape' && isOpen.value) {
                setOpen(false);
            }
        };

        onMounted(() => document.addEventListener('keydown', handleEscape));
        onUnmounted(() => document.removeEventListener('keydown', handleEscape));

        provide(dropdownMenuKey, {
            isOpen,
            setOpen,
            toggle,
        });

        return () => slots.default?.();
    },
};

const DropdownMenuTrigger = {
    props: {
        asChild: {
            type: Boolean,
            default: false,
        },
    },
    setup(props, { slots }) {
        const context = inject(dropdownMenuKey, null);

        return () => {
            const children = slots.default?.() ?? [];
            const onClick = (event) => {
                event.preventDefault();
                context?.toggle();
            };

            if (props.asChild && children.length === 1) {
                return h(children[0], {
                    ...children[0].props,
                    onClick,
                });
            }

            return h('button', { type: 'button', onClick }, children);
        };
    },
};

const DropdownMenuContent = {
    props: {
        align: {
            type: String,
            default: 'end',
        },
        sideOffset: {
            type: Number,
            default: 4,
        },
        class: {
            type: String,
            default: '',
        },
    },
    setup(props, { slots }) {
        const context = inject(dropdownMenuKey, null);

        return () => {
            if (!context?.isOpen.value) {
                return null;
            }

            return h(
                'div',
                {
                    class: `absolute z-50 min-w-[8rem] rounded-lg border border-slate-200 bg-white p-1 shadow-lg dark:border-slate-700 dark:bg-slate-900 ${props.class}`,
                    style: `right: ${props.align === 'end' ? 0 : 'auto'}; top: 100%; margin-top: ${props.sideOffset}px;`,
                },
                slots.default?.(),
            );
        };
    },
};

export { DropdownMenu, DropdownMenuTrigger, DropdownMenuContent };
</script>
