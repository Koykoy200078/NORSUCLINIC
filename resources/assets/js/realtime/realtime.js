// Real-time updates (plan Phase 7): one WebSocket connection per browser window, to the Reverb server of the clinic PC.
//
//  * The connection goes to the page's OWN host (location.hostname), so it works from any device on the LAN; nothing leaves it.
//  * The server sends a notice ("module X changed", nothing else) on a private channel per module; the user may only
//    listen to the modules the role allows (routes/channels.php). The notice carries no data: the screens ask the
//    server again for what they show, through the usual permission checks.
//  * What happens on a notice, for the modules the current page shows (<div id="live-config" data-page-modules="...">):
//      - the window event "clinic:live-refresh" ({detail: {module}}), for pages that redraw themselves (the queue);
//      - Livewire's "refresh" event, which every data table listens to;
//      - the sidebar badges are redrawn from /live/menu.
//  * When the connection is down the header shows "Live updates off" and the page is refreshed every 60 seconds instead,
//    only while it is down.
import Echo from "laravel-echo";
import Pusher from "pusher-js";

const FALLBACK_MS = 60000;
const DEBOUNCE_MS = 300;
const SPREAD_MS = 1200;

const state = {
    echo: null,
    connected: null, // null = still connecting (no badge yet), true, false
    started: false,
    subscribed: new Set(),
    pending: new Set(),
    timer: null,
    fallback: null,
};

function config() {
    const el = document.getElementById("live-config");
    if (!el) {
        return null;
    }
    const list = (name) => (el.dataset[name] || "").split(",").filter(Boolean);

    return {
        enabled: el.dataset.enabled === "1",
        key: el.dataset.key || "",
        port: parseInt(el.dataset.port || "8080", 10),
        scheme: el.dataset.scheme || "http",
        menuUrl: el.dataset.menuUrl || "",
        modules: list("modules"),
        pageModules: list("pageModules"),
    };
}

function badge() {
    return document.getElementById("live-updates-badge");
}

function showBadge() {
    const el = badge();
    if (el) {
        el.classList.toggle("d-none", state.connected !== false);
    }
}

function refreshPage(modules) {
    const page = (config() || {}).pageModules || [];
    modules.forEach((module) => {
        if (page.includes(module)) {
            window.dispatchEvent(new CustomEvent("clinic:live-refresh", { detail: { module } }));
        }
    });

    if (modules.some((module) => page.includes(module)) && window.Livewire && typeof window.Livewire.dispatch === "function") {
        window.Livewire.dispatch("refresh");
    }
}

// The sidebar badges: ask the server for the menu and swap only the badge containers (never the active highlight).
let menuBusy = false;
function refreshMenu() {
    const cfg = config();
    if (!cfg || !cfg.menuUrl || menuBusy || !document.querySelector("[data-live-badge]")) {
        return;
    }
    menuBusy = true;
    fetch(cfg.menuUrl, { headers: { "X-Requested-With": "XMLHttpRequest" }, credentials: "same-origin" })
        .then((response) => (response.ok ? response.text() : ""))
        .then((html) => {
            if (!html) {
                return;
            }
            const doc = new DOMParser().parseFromString(html, "text/html");
            doc.querySelectorAll("[data-live-badge]").forEach((fresh) => {
                const current = document.querySelector('[data-live-badge="' + fresh.dataset.liveBadge + '"]');
                if (current) {
                    current.innerHTML = fresh.innerHTML;
                }
            });
        })
        .catch(() => {})
        .finally(() => {
            menuBusy = false;
        });
}

const BADGE_MODULES = ["queue", "consultations", "inventory", "dispensing", "notifications"];

let centerBusy = false;
function refreshNotifications() {
    if (centerBusy || !document.querySelector('[data-notification-center]')) return;
    centerBusy = true;
    fetch(window.location.href, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.ok ? r.text() : '')
        .then(html => {
            const fresh = new DOMParser().parseFromString(html, 'text/html').querySelector('[data-notification-center]');
            const current = document.querySelector('[data-notification-center]');
            if (fresh && current) current.innerHTML = fresh.innerHTML;
        }).catch(() => {}).finally(() => { centerBusy = false; });
}

window.addEventListener('clinic:live-refresh', e => {
    if (e.detail.module === 'notifications') refreshNotifications();
});

document.addEventListener('click', async e => {
    const button = e.target.closest('[data-notification-read]');
    if (!button) return;
    e.preventDefault();
    button.disabled = true;
    try {
        const response = await fetch(button.dataset.notificationRead, { method: 'POST', credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
        if (response.ok) {
            refreshMenu();
            refreshNotifications();
        } else {
            button.textContent = 'Could not mark read. Try again.';
        }
    } catch (_) {
        button.textContent = 'Connection lost. Try again.';
    } finally { button.disabled = false; }
});

function flush() {
    state.timer = null;
    const modules = Array.from(state.pending);
    state.pending.clear();
    if (modules.length === 0) {
        return;
    }
    refreshPage(modules);
    if (modules.some((module) => BADGE_MODULES.includes(module))) {
        refreshMenu();
    }
}

// Notices that arrive together (a save touches several tables) become one refresh.
function changed(module) {
    state.pending.add(module);
    if (!state.timer) {
        // a little random spread: ten open screens must not all ask the server in the same instant
        state.timer = setTimeout(flush, DEBOUNCE_MS + Math.random() * SPREAD_MS);
    }
}

function startFallback() {
    if (state.fallback) {
        return;
    }
    state.fallback = setInterval(() => {
        if (state.connected === true) {
            return;
        }
        const cfg = config();
        if (cfg) {
            refreshPage(cfg.pageModules);
            refreshMenu();
        }
    }, FALLBACK_MS);
}

function setConnected(connected) {
    const was = state.connected;
    state.connected = connected;
    showBadge();
    if (connected && was === false) {
        // the connection came back after an outage: anything that changed while we were not listening
        const cfg = config();
        if (cfg) {
            refreshPage(cfg.pageModules);
            refreshMenu();
        }
    }
}

function subscribeAll(cfg) {
    cfg.modules.forEach((module) => {
        if (state.subscribed.has(module)) {
            return;
        }
        state.subscribed.add(module);
        state.echo.private("module." + module).listen(".ModuleChanged", (event) => changed((event && event.module) || module));
    });
}

function connect(cfg) {
    window.Pusher = Pusher;
    const csrf = document.querySelector('meta[name="csrf-token"]');

    state.echo = new Echo({
        broadcaster: "reverb",
        key: cfg.key,
        wsHost: window.location.hostname,
        wsPort: cfg.port,
        wssPort: cfg.port,
        forceTLS: cfg.scheme === "https",
        enabledTransports: ["ws", "wss"],
        disableStats: true,
        authEndpoint: "/broadcasting/auth",
        auth: { headers: { "X-CSRF-TOKEN": csrf ? csrf.getAttribute("content") : "" } },
    });

    const connection = state.echo.connector.pusher.connection;
    connection.bind("connected", () => setConnected(true));
    connection.bind("state_change", (change) => {
        if (change.current === "connected") {
            setConnected(true);
        } else if (change.previous === "connected" || ["unavailable", "failed", "disconnected"].includes(change.current)) {
            // lost (the socket drops to "connecting" while Pusher retries) or never established
            setConnected(false);
        }
    });
    // a server that does not answer at all never reaches "unavailable" quickly: after a few seconds say so
    setTimeout(() => {
        if (state.connected !== true) {
            setConnected(false);
        }
    }, 5000);

    subscribeAll(cfg);
}

function start() {
    const cfg = config();
    if (!cfg) {
        return;
    }
    // every page shows the badge state; a window that cannot connect still refreshes every minute
    startFallback();
    showBadge();

    if (state.started) {
        // a new page in the same window (Turbo): the connection stays, the channels of this page are added
        if (state.echo) {
            subscribeAll(cfg);
        }
        return;
    }
    state.started = true;

    if (!cfg.enabled || !cfg.key) {
        setConnected(false);
        return;
    }
    try {
        connect(cfg);
    } catch (e) {
        setConnected(false);
    }
}

window.clinicRealtime = {
    isConnected: () => state.connected,
    start,
    // for pages with their own redraw: ask for the same refresh a notice would cause
    refreshNow: () => {
        const cfg = config();
        if (cfg) {
            refreshPage(cfg.pageModules);
            refreshMenu();
        }
    },
};

document.addEventListener("DOMContentLoaded", start);
document.addEventListener("turbo:load", start);
if (document.readyState !== "loading") {
    start();
}
