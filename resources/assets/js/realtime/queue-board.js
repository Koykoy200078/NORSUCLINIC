import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const configElement = document.getElementById('queue-board-config');
const cfg = configElement ? JSON.parse(configElement.textContent) : null;
if (cfg) {
    const status = document.getElementById('board-status');
    const sound = document.getElementById('board-sound');
    let echo = null, connected = false, revoked = false, busy = false, again = false;
    let calls = new Set(), initialized = false, audio = null, soundOn = false, midnight = null, retry = null;

    function chime() {
        if (!soundOn || !audio) return;
        [0, 0.22].forEach((delay) => {
            const oscillator = audio.createOscillator(), gain = audio.createGain();
            oscillator.frequency.value = delay ? 660 : 880;
            gain.gain.setValueAtTime(0.08, audio.currentTime + delay);
            gain.gain.exponentialRampToValueAtTime(0.001, audio.currentTime + delay + 0.16);
            oscillator.connect(gain); gain.connect(audio.destination);
            oscillator.start(audio.currentTime + delay); oscillator.stop(audio.currentTime + delay + 0.18);
        });
    }

    sound.addEventListener('click', async () => {
        try {
            const Audio = window.AudioContext || window.webkitAudioContext;
            audio = audio || new Audio();
            await audio.resume();
            soundOn = !soundOn;
            sound.textContent = soundOn ? 'Disable call sound' : 'Enable call sound';
            sound.setAttribute('aria-pressed', String(soundOn));
        } catch (_) {
            sound.textContent = 'Call sound unavailable';
            sound.disabled = true;
        }
    });

    function rows(id, entries) {
        const list = document.getElementById(id);
        list.replaceChildren();
        entries.forEach((entry) => {
            const li = document.createElement('li'), number = document.createElement('strong'), room = document.createElement('small');
            number.textContent = String(entry.number);
            room.textContent = entry.room === 'Clinic room' ? entry.room : 'Room ' + entry.room;
            li.append(number, room); list.append(li);
        });
        document.getElementById(id + '-empty').hidden = entries.length > 0;
    }

    function render(data) {
        if (revoked) return;
        const fresh = new Set(data.serving.map(entry => entry.call));
        if (initialized && data.serving.some(entry => !calls.has(entry.call))) chime();
        calls = fresh; initialized = true;
        clearTimeout(retry);
        rows('board-serving', data.serving); rows('board-next', data.next);
        document.getElementById('board-day').textContent = 'Clinic day: ' + data.day;
        clearTimeout(midnight);
        clearTimeout(retry);
        // One server-timed day-boundary refresh, even if no queue event occurs at midnight.
        midnight = setTimeout(refresh, Math.max(1, data.refresh_in) * 1000 + 1000);
    }

    function revoke() {
        revoked = true;
        document.getElementById('board-content').hidden = true;
        rows('board-serving', []); rows('board-next', []);
        status.textContent = 'This board link has changed. Ask clinic staff to open the new link.';
        sound.disabled = true; soundOn = false;
        clearTimeout(midnight);
        if (echo) echo.disconnect();
    }

    async function refresh() {
        if (revoked) return;
        if (busy) { again = true; return; }
        busy = true;
        try {
            const response = await fetch(cfg.stateUrl, { cache: 'no-store', headers: { Accept: 'application/json' }, referrerPolicy: 'no-referrer' });
            if (response.status === 404) { revoke(); return; }
            if (response.ok) render(await response.json());
            else {
                status.textContent = 'Board update unavailable. Retrying shortly.';
                clearTimeout(retry); retry = setTimeout(refresh, 30000);
            }
        } catch (_) {
            status.textContent = 'Connection unavailable. Retrying shortly.';
            clearTimeout(retry); retry = setTimeout(refresh, 30000);
        } finally {
            busy = false;
            if (again) { again = false; refresh(); }
        }
    }

    function connection(isConnected) {
        connected = isConnected;
        if (!revoked) status.textContent = connected ? 'Live queue updates' : 'Live connection unavailable. Updates retry every minute.';
        if (connected) refresh();
    }

    render(cfg.state);
    setInterval(() => { if (!connected && !revoked) refresh(); }, 60000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
    if (cfg.enabled && cfg.key) {
        try {
            window.Pusher = Pusher;
            echo = new Echo({ broadcaster: 'reverb', key: cfg.key, wsHost: window.location.hostname,
                wsPort: Number(cfg.port), wssPort: Number(cfg.port), forceTLS: cfg.scheme === 'https',
                enabledTransports: ['ws', 'wss'], disableStats: true });
            const transport = echo.connector.pusher.connection;
            transport.bind('connected', () => connection(true));
            transport.bind('state_change', change => { if (change.current !== 'connected') connection(false); });
            echo.channel(cfg.channel).listen('.QueueBoardChanged', data => { if (data.revoked) revoke(); else refresh(); })
                .subscribed(refresh);
            setTimeout(() => { if (!connected) connection(false); }, 5000);
        } catch (_) { connection(false); }
    } else { connection(false); }
}
