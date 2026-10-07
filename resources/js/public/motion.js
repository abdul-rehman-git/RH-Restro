const REVEAL_STATE_KEY = Symbol('reveal-state');
const COUNT_STATE_KEY = Symbol('count-state');

const revealPresets = {
    fade: {
        animation: 'custom-fade-in',
        hiddenClass: 'motion-reveal-fade',
    },
    fadeUp: {
        animation: 'custom-fade-in-up',
        hiddenClass: 'motion-reveal-up',
    },
    fadeDown: {
        animation: 'custom-fade-in-down',
        hiddenClass: 'motion-reveal-down',
    },
    fadeLeft: {
        animation: 'custom-fade-in-left',
        hiddenClass: 'motion-reveal-left',
    },
    fadeRight: {
        animation: 'custom-fade-in-right',
        hiddenClass: 'motion-reveal-right',
    },
    zoom: {
        animation: 'custom-zoom-in',
        hiddenClass: 'motion-reveal-zoom',
    },
};

const prefersReducedMotion = () =>
    typeof window !== 'undefined'
    && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const resolveRevealOptions = (value = {}) => {
    if (typeof value === 'string') {
        value = { preset: value };
    }

    const preset = revealPresets[value.preset] || revealPresets.fadeUp;

    return {
        animation: value.animation || preset.animation,
        hiddenClass: value.hiddenClass || preset.hiddenClass,
        delay: Number(value.delay || 0),
        duration: Number(value.duration || 700),
        threshold: value.threshold ?? 0.16,
        rootMargin: value.rootMargin ?? '0px 0px -10% 0px',
        once: value.once ?? true,
    };
};

const cleanupReveal = (el) => {
    const state = el[REVEAL_STATE_KEY];

    if (!state) {
        return;
    }

    state.observer?.disconnect();
    delete el[REVEAL_STATE_KEY];
};

const revealElement = (el, options) => {
    el.classList.add('motion-reveal-visible', options.animation);
    el.style.setProperty('--motion-duration', `${options.duration}ms`);
    el.style.setProperty('--motion-delay', `${options.delay}ms`);
    el.classList.remove(options.hiddenClass);
};

const mountReveal = (el, bindingValue) => {
    cleanupReveal(el);

    const options = resolveRevealOptions(bindingValue);

    if (prefersReducedMotion()) {
        el.classList.add('motion-reveal-visible');
        el.classList.remove(options.hiddenClass);

        return;
    }

    el.classList.add('motion-reveal', options.hiddenClass);
    el.style.setProperty('--motion-duration', `${options.duration}ms`);
    el.style.setProperty('--motion-delay', `${options.delay}ms`);

    const observer = new IntersectionObserver(
        ([entry]) => {
            if (!entry?.isIntersecting) {
                return;
            }

            revealElement(el, options);

            if (options.once) {
                observer.disconnect();
            }
        },
        {
            threshold: options.threshold,
            rootMargin: options.rootMargin,
        },
    );

    observer.observe(el);
    el[REVEAL_STATE_KEY] = { observer };
};

const revealDirective = {
    mounted(el, binding) {
        mountReveal(el, binding.value);
    },
    unmounted(el) {
        cleanupReveal(el);
    },
};

const staggerDirective = {
    mounted(el, binding) {
        const value = typeof binding.value === 'string' ? { preset: binding.value } : (binding.value || {});
        const selector = value.selector || ':scope > *';
        const items = Array.from(el.querySelectorAll(selector));
        const stagger = Number(value.stagger || 90);
        const baseDelay = Number(value.delay || 0);

        el[REVEAL_STATE_KEY] = {
            cleanups: items.map((item, index) => {
                mountReveal(item, {
                    ...value,
                    delay: baseDelay + (index * stagger),
                });

                return () => cleanupReveal(item);
            }),
        };
    },
    unmounted(el) {
        el[REVEAL_STATE_KEY]?.cleanups?.forEach((cleanup) => cleanup());
        delete el[REVEAL_STATE_KEY];
    },
};

const resolveCountOptions = (value) => {
    if (typeof value === 'object' && value !== null) {
        return {
            value: value.value,
            duration: Number(value.duration || 1400),
        };
    }

    return {
        value,
        duration: 1400,
    };
};

const parseCountTarget = (rawValue) => {
    const value = String(rawValue ?? '').trim();
    const match = value.match(/^([^0-9]*)(\d+(?:\.\d+)?)(.*)$/);

    if (!match) {
        return null;
    }

    const [, prefix, numberPart, suffix] = match;
    const decimalPlaces = numberPart.includes('.') ? numberPart.split('.')[1].length : 0;

    return {
        prefix,
        suffix,
        decimalPlaces,
        target: Number(numberPart),
        rawValue: value,
    };
};

const cleanupCount = (el) => {
    const state = el[COUNT_STATE_KEY];

    if (!state) {
        return;
    }

    state.observer?.disconnect();
    state.frame && cancelAnimationFrame(state.frame);
    delete el[COUNT_STATE_KEY];
};

const formatCountValue = (value, decimalPlaces) =>
    decimalPlaces > 0 ? value.toFixed(decimalPlaces) : Math.round(value).toString();

const mountCountUp = (el, bindingValue) => {
    cleanupCount(el);

    const options = resolveCountOptions(bindingValue);
    const parsed = parseCountTarget(options.value ?? el.textContent);

    if (!parsed) {
        return;
    }

    const setValue = (value) => {
        el.textContent = `${parsed.prefix}${formatCountValue(value, parsed.decimalPlaces)}${parsed.suffix}`;
    };

    if (prefersReducedMotion()) {
        el.textContent = parsed.rawValue;

        return;
    }

    setValue(0);

    const startAnimation = () => {
        const startedAt = performance.now();

        const tick = (now) => {
            const progress = Math.min((now - startedAt) / options.duration, 1);
            const eased = 1 - ((1 - progress) ** 3);
            const value = parsed.target * eased;

            setValue(value);

            if (progress < 1) {
                el[COUNT_STATE_KEY].frame = requestAnimationFrame(tick);

                return;
            }

            el.textContent = parsed.rawValue;
        };

        el[COUNT_STATE_KEY].frame = requestAnimationFrame(tick);
    };

    const observer = new IntersectionObserver(
        ([entry]) => {
            if (!entry?.isIntersecting) {
                return;
            }

            startAnimation();
            observer.disconnect();
        },
        {
            threshold: 0.4,
        },
    );

    observer.observe(el);
    el[COUNT_STATE_KEY] = { observer, frame: null };
};

const countUpDirective = {
    mounted(el, binding) {
        mountCountUp(el, binding.value);
    },
    unmounted(el) {
        cleanupCount(el);
    },
};

export const installPublicMotion = (app) => {
    app.directive('reveal', revealDirective);
    app.directive('stagger', staggerDirective);
    app.directive('count-up', countUpDirective);
};
