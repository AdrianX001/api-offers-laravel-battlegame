<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import AuthenticationForm from "./components/AuthenticationForm.vue";
import ThemeToggle from "./components/ThemeToggle.vue";

const mx = ref(50);
const my = ref(50);

function onMouseMove(e) {
    mx.value = (e.clientX / window.innerWidth)  * 100;
    my.value = (e.clientY / window.innerHeight) * 100;
}

onMounted(()  => window.addEventListener("mousemove", onMouseMove));
onUnmounted(() => window.removeEventListener("mousemove", onMouseMove));
</script>

<template>
    <div class="page">

        <div class="aurora" aria-hidden="true">
            <div class="aurora__layer aurora__layer--1"
                 :style="{ transform: `translate(${(mx-50)*0.012}%, ${(my-50)*0.008}%)` }"></div>
            <div class="aurora__layer aurora__layer--2"
                 :style="{ transform: `translate(${(mx-50)*-0.01}%, ${(my-50)*0.014}%)` }"></div>
            <div class="aurora__layer aurora__layer--3"
                 :style="{ transform: `translate(${(mx-50)*0.008}%, ${(my-50)*-0.01}%)` }"></div>
            <div class="aurora__layer aurora__layer--4"
                 :style="{ transform: `translate(${(mx-50)*-0.006}%, ${(my-50)*0.006}%)` }"></div>
            <div class="aurora__noise"></div>
            <div class="aurora__vignette"></div>
        </div>

        <header class="topbar">
            <div class="brand">
                <div class="brandLogo">
                    <svg width="16" height="16" viewBox="0 0 18 18" fill="none">
                        <path d="M9 1L16 5V13L9 17L2 13V5L9 1Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                        <path d="M9 5L13 7.5V12.5L9 15L5 12.5V7.5L9 5Z" fill="currentColor" opacity=".5"/>
                    </svg>
                </div>
                <div>
                    <div class="brandName">RCA Offers</div>
                    <div class="brandSub">Quote portal</div>
                </div>
            </div>
            <ThemeToggle />
        </header>

        <main class="content">
            <AuthenticationForm />
        </main>
    </div>
</template>

<style scoped>
.page {
    position: relative;
    min-height: 100vh;
    overflow-x: hidden;
    background: var(--bg);
}

/* ─────────────────────────────────────────
   AURORA
───────────────────────────────────────── */
.aurora {
    position: fixed;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    overflow: hidden;
}

/* Each layer is a huge soft blob that animates independently */
.aurora__layer {
    position: absolute;
    border-radius: 50%;
    mix-blend-mode: screen;
    filter: blur(80px);
    opacity: .55;
    transition: transform 1.8s cubic-bezier(.25,.46,.45,.94);
    will-change: transform;
}

/* Layer 1 — large green sweep, top-left */
.aurora__layer--1 {
    width: 140%;
    height: 60%;
    top: -20%;
    left: -20%;
    background: radial-gradient(ellipse at 40% 50%,
    rgba(34,197,94,.55)  0%,
    rgba(16,185,129,.30) 40%,
    transparent          75%
    );
    animation: aurora1 14s ease-in-out infinite alternate;
}

/* Layer 2 — deep teal, drifts right */
.aurora__layer--2 {
    width: 110%;
    height: 55%;
    top: 5%;
    right: -25%;
    background: radial-gradient(ellipse at 60% 40%,
    rgba(20,184,166,.45)  0%,
    rgba(6,182,212,.25)  45%,
    transparent           75%
    );
    animation: aurora2 18s ease-in-out infinite alternate;
}

/* Layer 3 — blue-green, centre bottom */
.aurora__layer--3 {
    width: 100%;
    height: 50%;
    bottom: -10%;
    left: 10%;
    background: radial-gradient(ellipse at 50% 60%,
    rgba(56,189,248,.30)  0%,
    rgba(34,197,94,.20)  50%,
    transparent           75%
    );
    animation: aurora3 22s ease-in-out infinite alternate;
}

/* Layer 4 — accent pulse, small and bright */
.aurora__layer--4 {
    width: 60%;
    height: 40%;
    top: 20%;
    left: 20%;
    background: radial-gradient(ellipse at 50% 50%,
    rgba(74,222,128,.18) 0%,
    transparent          70%
    );
    animation: aurora4 10s ease-in-out infinite alternate;
}

@keyframes aurora1 {
    0%   { transform: translate(0%,   0%)   scaleX(1)    scaleY(1);    opacity: .55; }
    33%  { transform: translate(4%,   6%)   scaleX(1.08) scaleY(.95);  opacity: .45; }
    66%  { transform: translate(-3%,  3%)   scaleX(.96)  scaleY(1.06); opacity: .60; }
    100% { transform: translate(6%,  -4%)   scaleX(1.05) scaleY(1.02); opacity: .50; }
}
@keyframes aurora2 {
    0%   { transform: translate(0%,   0%)   scaleX(1)    scaleY(1);    opacity: .45; }
    33%  { transform: translate(-5%,  4%)   scaleX(1.06) scaleY(.92);  opacity: .55; }
    66%  { transform: translate(3%,  -5%)   scaleX(.94)  scaleY(1.08); opacity: .40; }
    100% { transform: translate(-4%,  2%)   scaleX(1.04) scaleY(.98);  opacity: .50; }
}
@keyframes aurora3 {
    0%   { transform: translate(0%,   0%)   scaleX(1)    scaleY(1);    opacity: .30; }
    50%  { transform: translate(5%,  -3%)   scaleX(1.1)  scaleY(.9);   opacity: .40; }
    100% { transform: translate(-4%,  5%)   scaleX(.92)  scaleY(1.08); opacity: .25; }
}
@keyframes aurora4 {
    0%   { opacity: .18; transform: scale(1)    translate(0%, 0%); }
    50%  { opacity: .30; transform: scale(1.15) translate(3%, -4%); }
    100% { opacity: .14; transform: scale(.9)   translate(-4%, 3%); }
}

/* Fine grain overlay — makes it feel organic */
.aurora__noise {
    position: absolute;
    inset: -50%;
    width: 200%; height: 200%;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='1'/%3E%3C/svg%3E");
    background-size: 200px 200px;
    opacity: .032;
    animation: noiseScroll 8s steps(10) infinite;
    pointer-events: none;
}
@keyframes noiseScroll {
    0%   { transform: translate(0, 0); }
    10%  { transform: translate(-2%, -3%); }
    20%  { transform: translate(3%, 1%); }
    30%  { transform: translate(-1%, 4%); }
    40%  { transform: translate(4%, -2%); }
    50%  { transform: translate(-3%, 3%); }
    60%  { transform: translate(2%, -4%); }
    70%  { transform: translate(-4%, 1%); }
    80%  { transform: translate(1%, -2%); }
    90%  { transform: translate(3%, 4%); }
    100% { transform: translate(0, 0); }
}

/* Dark vignette around edges to keep content readable */
.aurora__vignette {
    position: absolute;
    inset: 0;
    background: radial-gradient(
        ellipse 90% 85% at 50% 50%,
        transparent 40%,
        rgba(14,17,23,.55) 100%
    );
}

/* ─────────────────────────────────────────
   TOPBAR
───────────────────────────────────────── */
.topbar {
    position: sticky;
    top: 0;
    z-index: 50;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 24px;
    height: 52px;
    border-bottom: 1px solid var(--border);
    background: color-mix(in srgb, var(--bg) 65%, transparent);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
}

.brand { display: flex; align-items: center; gap: 10px; }

.brandLogo {
    width: 30px; height: 30px;
    border-radius: 8px;
    background: var(--card2);
    border: 1px solid var(--border2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--accent);
    flex-shrink: 0;
}

.brandName { font-size: 14px; font-weight: 600; line-height: 1.2; }
.brandSub  { font-size: 11px; color: var(--muted); line-height: 1.2; }

/* ─────────────────────────────────────────
   CONTENT
───────────────────────────────────────── */
.content {
    position: relative;
    z-index: 1;
    max-width: 1080px;
    margin: 0 auto;
    padding: 24px 20px 48px;
}

/* ─────────────────────────────────────────
   LIGHT MODE ADJUSTMENTS
───────────────────────────────────────── */
:global(:root.theme-light) .aurora__layer--1 {
    background: radial-gradient(ellipse at 40% 50%,
    rgba(22,163,74,.25) 0%, rgba(16,185,129,.12) 40%, transparent 75%);
    opacity: .8;
}
:global(:root.theme-light) .aurora__layer--2 {
    background: radial-gradient(ellipse at 60% 40%,
    rgba(20,184,166,.20) 0%, rgba(6,182,212,.10) 45%, transparent 75%);
    opacity: .8;
}
:global(:root.theme-light) .aurora__layer--3 {
    background: radial-gradient(ellipse at 50% 60%,
    rgba(56,189,248,.15) 0%, rgba(34,197,94,.08) 50%, transparent 75%);
    opacity: .8;
}
:global(:root.theme-light) .aurora__layer--4 {
    opacity: .5;
}
:global(:root.theme-light) .aurora__vignette {
    background: radial-gradient(
        ellipse 90% 85% at 50% 50%,
        transparent 40%,
        rgba(245,246,250,.6) 100%
    );
}
</style>
