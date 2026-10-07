# Hero Section Redesign Plan

## Overview
Complete replacement of the inline hero section in `Home.vue` (lines 66–138) with a premium Awwwards-style luxury hero. All changes stay within `Home.vue` + `luxury.css`.

---

## File 1: `resources/css/public/luxury.css`

### Insert before line 508 (`@media (prefers-reduced-motion: reduce)`)

Add the following block:

```css
/* ============================================
   PREMIUM HERO ANIMATIONS & STYLES
   ============================================ */

/* Hero Premium Background */
.hero-premium-bg {
  overflow: hidden;
}

@keyframes gradient-shift {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}

.hero-gradient-animated {
  background: linear-gradient(135deg, #FAF7F2 0%, #F5F1E8 25%, #FFF 50%, #F5F1E8 75%, #FAF7F2 100%);
  background-size: 200% 200%;
  animation: gradient-shift 12s ease-in-out infinite;
}

.dark .hero-gradient-animated {
  background: linear-gradient(135deg, #0A0A0A 0%, #1A1A1A 25%, #111 50%, #1A1A1A 75%, #0A0A0A 100%);
  background-size: 200% 200%;
  animation: gradient-shift 12s ease-in-out infinite;
}

/* Glow Orbs */
@keyframes glow-pulse {
  0%, 100% { opacity: 0.3; transform: scale(1); }
  50% { opacity: 0.6; transform: scale(1.08); }
}

.hero-glow-orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  pointer-events: none;
  animation: glow-pulse 6s ease-in-out infinite;
  will-change: transform, opacity;
}

/* Light Rays */
@keyframes rays-sweep {
  0% { transform: translateX(-30%) translateY(-20%) rotate(0deg); }
  100% { transform: translateX(30%) translateY(20%) rotate(3deg); }
}

.hero-rays {
  position: absolute;
  inset: -50%;
  background: repeating-linear-gradient(
    55deg,
    transparent,
    transparent 60px,
    rgba(212, 175, 55, 0.03) 60px,
    rgba(212, 175, 55, 0.03) 62px
  );
  animation: rays-sweep 25s linear infinite;
  pointer-events: none;
}

.dark .hero-rays {
  background: repeating-linear-gradient(
    55deg,
    transparent,
    transparent 60px,
    rgba(212, 175, 55, 0.06) 60px,
    rgba(212, 175, 55, 0.06) 62px
  );
}

/* Premium Badge */
@keyframes ping-dot {
  0%, 100% { transform: scale(1); opacity: 0.8; }
  50% { transform: scale(1.8); opacity: 0; }
}

.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.4rem 1.25rem;
  border-radius: 9999px;
  border: 1px solid rgba(212, 175, 55, 0.25);
  background: linear-gradient(135deg, rgba(212, 175, 55, 0.12) 0%, transparent 100%);
  backdrop-filter: blur(4px);
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: #D4AF37;
}

.hero-badge-dot {
  position: relative;
  width: 8px;
  height: 8px;
  flex-shrink: 0;
}

.hero-badge-dot-ring {
  position: absolute;
  inset: 0;
  border-radius: 50%;
  background: #D4AF37;
  animation: ping-dot 2s cubic-bezier(0, 0, 0.2, 1) infinite;
}

.hero-badge-dot-core {
  position: relative;
  display: block;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: #D4AF37;
}

/* Circular Image Container */
@keyframes hero-float {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  25% { transform: translateY(-8px) rotate(0.5deg); }
  50% { transform: translateY(-2px) rotate(-0.2deg); }
  75% { transform: translateY(4px) rotate(0.3deg); }
}

.hero-image-wrapper {
  animation: hero-float 8s ease-in-out infinite;
  will-change: transform;
}

.hero-image-group {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Glow behind image */
.hero-glow-backdrop {
  position: absolute;
  width: 90%;
  height: 90%;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(212, 175, 55, 0.25) 0%, rgba(212, 175, 55, 0.08) 40%, transparent 70%);
  animation: glow-pulse 5s ease-in-out infinite;
  pointer-events: none;
  transition: opacity 0.6s ease;
}

.hero-image-group:hover .hero-glow-backdrop {
  opacity: 1.4;
}

/* The image itself */
.hero-image-ring {
  position: relative;
  width: 100%;
  max-width: 420px;
  aspect-ratio: 1 / 1;
  border-radius: 50%;
  overflow: hidden;
  z-index: 2;
}

.hero-image-ring img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.7s cubic-bezier(0.22, 1, 0.36, 1);
  will-change: transform;
}

.hero-image-group:hover .hero-image-ring img {
  transform: scale(1.08);
}

/* Glowing circular border */
.hero-ring-border {
  position: absolute;
  inset: -6px;
  border-radius: 50%;
  border: 1.5px solid rgba(212, 175, 55, 0.35);
  box-shadow:
    0 0 15px rgba(212, 175, 55, 0.1),
    inset 0 0 15px rgba(212, 175, 55, 0.05);
  pointer-events: none;
  transition:
    border-color 0.5s ease,
    box-shadow 0.5s ease;
  z-index: 3;
}

.hero-image-group:hover .hero-ring-border {
  border-color: rgba(212, 175, 55, 0.65);
  box-shadow:
    0 0 30px rgba(212, 175, 55, 0.25),
    inset 0 0 25px rgba(212, 175, 55, 0.1);
}

/* Dark overlay on image bottom */
.hero-image-overlay {
  position: absolute;
  inset: 0;
  border-radius: 50%;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.2) 0%, transparent 50%);
  pointer-events: none;
  z-index: 2;
}

/* Orbiting Dots */
.hero-orbit {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  z-index: 4;
  pointer-events: none;
}

@keyframes hero-orbit {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.hero-orbit-dot {
  position: absolute;
  width: 10px;
  height: 10px;
  margin: -5px 0 0 -5px;
  border-radius: 50%;
  background: #D4AF37;
  box-shadow: 0 0 12px rgba(212, 175, 55, 0.7), 0 0 25px rgba(212, 175, 55, 0.3);
  transition:
    box-shadow 0.5s ease,
    transform 0.5s ease;
  will-change: transform;
}

.hero-image-group:hover .hero-orbit-dot {
  box-shadow: 0 0 18px rgba(212, 175, 55, 0.9), 0 0 35px rgba(212, 175, 55, 0.4);
}

/* Orbit track radii via transform */
.hero-orbit-dot-1 {
  animation: hero-orbit 5s linear infinite;
  transform: rotate(0deg) translateX(180px);
}
.hero-image-group:hover .hero-orbit-dot-1 {
  animation-duration: 3s;
}

.hero-orbit-dot-2 {
  animation: hero-orbit 7s linear infinite reverse;
  transform: rotate(90deg) translateX(200px);
}
.hero-image-group:hover .hero-orbit-dot-2 {
  animation-duration: 4.5s;
}

.hero-orbit-dot-3 {
  animation: hero-orbit 4.5s linear infinite;
  transform: rotate(180deg) translateX(170px);
}
.hero-image-group:hover .hero-orbit-dot-3 {
  animation-duration: 2.8s;
}

.hero-orbit-dot-4 {
  animation: hero-orbit 8.5s linear infinite reverse;
  transform: rotate(270deg) translateX(195px);
}
.hero-image-group:hover .hero-orbit-dot-4 {
  animation-duration: 5.5s;
}

/* Floating Particles */
@keyframes particle-drift-1 {
  0%, 100% { transform: translate(0, 0); opacity: 0; }
  15% { opacity: 0.8; }
  50% { transform: translate(-25px, -35px); opacity: 0.5; }
  85% { opacity: 0.8; }
}

@keyframes particle-drift-2 {
  0%, 100% { transform: translate(0, 0); opacity: 0; }
  15% { opacity: 0.6; }
  50% { transform: translate(30px, -20px); opacity: 0.4; }
  85% { opacity: 0.6; }
}

@keyframes particle-drift-3 {
  0%, 100% { transform: translate(0, 0); opacity: 0; }
  15% { opacity: 0.7; }
  50% { transform: translate(-15px, 30px); opacity: 0.4; }
  85% { opacity: 0.7; }
}

.hero-particle {
  position: absolute;
  border-radius: 50%;
  background: rgba(212, 175, 55, 0.5);
  pointer-events: none;
  z-index: 3;
  will-change: transform, opacity;
}

.hero-particle-1 {
  width: 3px;
  height: 3px;
  top: 15%;
  left: 5%;
  animation: particle-drift-1 7s ease-in-out infinite;
  animation-delay: 0s;
}

.hero-particle-2 {
  width: 4px;
  height: 4px;
  top: 10%;
  right: 10%;
  animation: particle-drift-2 9s ease-in-out infinite;
  animation-delay: 1.5s;
}

.hero-particle-3 {
  width: 2px;
  height: 2px;
  bottom: 20%;
  left: 8%;
  animation: particle-drift-3 8s ease-in-out infinite;
  animation-delay: 0.8s;
}

.hero-particle-4 {
  width: 3px;
  height: 3px;
  bottom: 15%;
  right: 5%;
  animation: particle-drift-1 10s ease-in-out infinite;
  animation-delay: 2.2s;
}

.hero-particle-5 {
  width: 2px;
  height: 2px;
  top: 30%;
  left: 2%;
  animation: particle-drift-2 6.5s ease-in-out infinite;
  animation-delay: 3.5s;
}

.hero-particle-6 {
  width: 3px;
  height: 3px;
  top: 5%;
  left: 50%;
  animation: particle-drift-3 8.5s ease-in-out infinite;
  animation-delay: 1s;
}

/* Stats Glass Card */
.hero-stat-card {
  border-radius: 1rem;
  border: 1px solid rgba(255, 255, 255, 0.25);
  background: rgba(255, 255, 255, 0.45);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  padding: 1.25rem;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.04);
  transition:
    transform 0.4s cubic-bezier(0.22, 1, 0.36, 1),
    box-shadow 0.4s cubic-bezier(0.22, 1, 0.36, 1),
    border-color 0.3s ease;
  will-change: transform;
}

.dark .hero-stat-card {
  background: rgba(26, 26, 26, 0.6);
  border-color: rgba(212, 175, 55, 0.15);
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.2);
}

.hero-stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
  border-color: rgba(212, 175, 55, 0.3);
}

.dark .hero-stat-card:hover {
  box-shadow: 0 12px 40px rgba(212, 175, 55, 0.1);
  border-color: rgba(212, 175, 55, 0.3);
}

.hero-stat-value {
  font-size: 1.875rem;
  font-weight: 700;
  line-height: 1.2;
  color: var(--foreground);
}

.hero-stat-label {
  margin-top: 0.25rem;
  font-size: 0.875rem;
  color: var(--muted-foreground);
  font-weight: 400;
}

/* Premium CTA Button */
.btn-premium {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 1rem 2rem;
  border-radius: 0.75rem;
  font-size: 1.125rem;
  font-weight: 600;
  background: linear-gradient(135deg, #D4AF37 0%, #C9A032 50%, #D4AF37 100%);
  background-size: 200% 200%;
  color: #0A0A0A;
  box-shadow:
    0 4px 16px rgba(212, 175, 55, 0.3),
    0 1px 3px rgba(0, 0, 0, 0.1);
  transition:
    transform 0.4s cubic-bezier(0.22, 1, 0.36, 1),
    box-shadow 0.4s cubic-bezier(0.22, 1, 0.36, 1),
    background-position 0.6s ease;
  will-change: transform;
}

.btn-premium:hover {
  transform: translateY(-2px);
  box-shadow:
    0 8px 28px rgba(212, 175, 55, 0.4),
    0 2px 6px rgba(0, 0, 0, 0.12);
  background-position: 100% 100%;
}

.btn-premium:active {
  transform: translateY(0) scale(0.98);
}

/* Glass Outline Button */
.btn-glass {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 1rem 2rem;
  border-radius: 0.75rem;
  font-size: 1.125rem;
  font-weight: 500;
  border: 1px solid rgba(0, 0, 0, 0.1);
  background: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  color: var(--foreground);
  transition:
    transform 0.4s cubic-bezier(0.22, 1, 0.36, 1),
    background 0.3s ease,
    border-color 0.3s ease,
    box-shadow 0.4s cubic-bezier(0.22, 1, 0.36, 1);
  will-change: transform;
}

.dark .btn-glass {
  background: rgba(26, 26, 26, 0.5);
  border-color: rgba(212, 175, 55, 0.15);
}

.btn-glass:hover {
  transform: translateY(-2px);
  background: rgba(255, 255, 255, 0.85);
  border-color: rgba(212, 175, 55, 0.35);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}

.dark .btn-glass:hover {
  background: rgba(42, 42, 42, 0.7);
  border-color: rgba(212, 175, 55, 0.35);
  box-shadow: 0 8px 24px rgba(212, 175, 55, 0.08);
}

.btn-glass:active {
  transform: translateY(0) scale(0.98);
}

/* Arrow icon slide animation */
.btn-arrow {
  display: inline-block;
  transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1);
}

.btn-premium:hover .btn-arrow,
.btn-glass:hover .btn-arrow {
  transform: translateX(4px);
}

/* Responsive: smaller circle on tablet */
@media (max-width: 1023px) {
  .hero-image-ring {
    max-width: 320px;
  }

  .hero-orbit-dot-1 { transform: rotate(0deg) translateX(150px); }
  .hero-orbit-dot-2 { transform: rotate(90deg) translateX(165px); }
  .hero-orbit-dot-3 { transform: rotate(180deg) translateX(140px); }
  .hero-orbit-dot-4 { transform: rotate(270deg) translateX(160px); }

  .btn-premium,
  .btn-glass {
    padding: 0.875rem 1.5rem;
    font-size: 1rem;
  }
}

@media (max-width: 639px) {
  .hero-image-ring {
    max-width: 260px;
  }

  .hero-orbit-dot-1 { transform: rotate(0deg) translateX(125px); }
  .hero-orbit-dot-2 { transform: rotate(90deg) translateX(138px); }
  .hero-orbit-dot-3 { transform: rotate(180deg) translateX(115px); }
  .hero-orbit-dot-4 { transform: rotate(270deg) translateX(132px); }
}

/* ============================================
   END PREMIUM HERO ANIMATIONS
   ============================================ */
```

### Add to the reduced-motion block (after `opacity: 1 !important;` line)

Append these entries inside the `@media (prefers-reduced-motion: reduce)` block, alongside the existing classes:

```css
  .hero-gradient-animated,
  .hero-glow-orb,
  .hero-rays,
  .hero-badge-dot-ring,
  .hero-image-wrapper,
  .hero-glow-backdrop,
  .hero-orbit-dot,
  .hero-particle,
  .hero-stat-card {
    animation: none !important;
    transition: none !important;
    transform: none !important;
  }
```

---

## File 2: `resources/js/Pages/Public/Home.vue`

### Replace lines 66–138 (the hero section) entirely

**Remove:**
```html
<section
    :class="`relative overflow-hidden py-16 sm:py-24 lg:py-32 ${actualTheme === 'dark' ? 'hero-marble-dark' : 'hero-marble-light'}`">
    ...all content...
</section>
```

**Replace with:**
```html
<section class="hero-premium-bg hero-gradient-animated relative min-h-screen flex items-center py-20 sm:py-28 lg:py-36">
    <!-- Background glow orbs -->
    <div class="hero-glow-orb w-96 h-96 bg-[#D4AF37]/15 top-[-10%] right-[-5%]" style="animation-delay: 0s" />
    <div class="hero-glow-orb w-80 h-80 bg-[#D4AF37]/10 bottom-[-8%] left-[-8%]" style="animation-delay: 2.5s" />
    <div class="hero-glow-orb w-64 h-64 bg-[#D4AF37]/8 top-[40%] left-[-4%]" style="animation-delay: 4s" />

    <!-- Light rays -->
    <div class="hero-rays" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
        <div class="grid items-center gap-16 lg:grid-cols-2 lg:gap-20">
            <!-- LEFT COLUMN -->
            <div v-stagger="{
                preset: 'fadeUp',
                stagger: 110,
                duration: 680,
            }" class="relative z-10 space-y-8 lg:space-y-10">
                <div class="space-y-6">
                    <!-- Premium Badge -->
                    <div class="hero-badge">
                        <span class="hero-badge-dot">
                            <span class="hero-badge-dot-ring" />
                            <span class="hero-badge-dot-core" />
                        </span>
                        Premium Handmade Art
                    </div>

                    <!-- Heading -->
                    <h1 class="text-4xl font-bold leading-tight text-foreground sm:text-5xl lg:text-6xl xl:text-7xl">
                        {{ heroTitleParts.lead }}
                        <span v-if="heroTitleParts.accent" class="block text-gold-gradient mt-2 pb-2">
                            {{ heroTitleParts.accent }}
                        </span>
                    </h1>

                    <!-- Description -->
                    <p class="max-w-xl text-lg leading-relaxed text-muted-foreground sm:text-xl">
                        {{ pageData.hero_subtitle }}
                    </p>
                </div>

                <!-- CTA Buttons -->
                <div class="flex flex-col gap-4 sm:flex-row">
                    <Link href="/shop" class="btn-premium group">
                        Explore Collection
                        <ArrowRight class="btn-arrow h-5 w-5" />
                    </Link>
                    <Link href="/custom-order" class="btn-glass group">
                        Custom Order
                        <ArrowRight class="btn-arrow h-5 w-5" />
                    </Link>
                </div>

                <!-- Stats Cards -->
                <div v-if="stats.length" v-stagger="{
                    preset: 'fadeUp',
                    stagger: 90,
                    delay: 100,
                }" class="grid gap-4 pt-2 sm:grid-cols-3">
                    <div v-for="stat in stats" :key="`${stat.label}-${stat.value}`" class="hero-stat-card">
                        <div v-count-up="stat.value" class="hero-stat-value">{{ stat.value }}</div>
                        <div class="hero-stat-label">{{ stat.label }}</div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN - Circular Image Showcase -->
            <div v-reveal="{ preset: 'zoom', duration: 760, delay: 120 }" class="relative flex justify-center lg:justify-end">
                <div class="hero-image-group">
                    <!-- Orbit dots -->
                    <div class="hero-orbit">
                        <div class="hero-orbit-dot hero-orbit-dot-1" />
                        <div class="hero-orbit-dot hero-orbit-dot-2" />
                        <div class="hero-orbit-dot hero-orbit-dot-3" />
                        <div class="hero-orbit-dot hero-orbit-dot-4" />
                    </div>

                    <!-- Floating particles -->
                    <div class="hero-particle hero-particle-1" />
                    <div class="hero-particle hero-particle-2" />
                    <div class="hero-particle hero-particle-3" />
                    <div class="hero-particle hero-particle-4" />
                    <div class="hero-particle hero-particle-5" />
                    <div class="hero-particle hero-particle-6" />

                    <!-- Glow backdrop -->
                    <div class="hero-glow-backdrop" />

                    <!-- Floating wrapper -->
                    <div class="hero-image-wrapper">
                        <!-- Image ring container -->
                        <div class="hero-image-ring">
                            <div class="hero-ring-border" />
                            <img :src="pageData.hero_image_url || fallbackHeroImage"
                                :alt="pageData.hero_title || 'Featured artwork'" />
                            <div class="hero-image-overlay" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
```

---

## Summary of Changes

| Change | Location | Lines |
|--------|----------|-------|
| Add ~240 lines of new CSS | `luxury.css` | Before line 508 |
| Update reduced-motion block | `luxury.css` | Inside `@media` query |
| Replace hero template | `Home.vue` | Lines 66→138 replaced |

## Verification

```bash
npm run build
# or
npx vite build
```
