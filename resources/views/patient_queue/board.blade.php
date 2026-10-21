<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <meta name="robots" content="noindex,nofollow">
    <title>Clinic waiting-area board</title>
    <style>
        *{box-sizing:border-box}[hidden]{display:none!important}body{margin:0;background:#10253e;color:#f5f8ff;font-family:Arial,sans-serif;padding:3vw}header{display:flex;flex-wrap:wrap;gap:1rem;justify-content:space-between;align-items:center}h1{font-size:clamp(1.6rem,4vw,3rem)}h2{font-size:clamp(1.4rem,3vw,2.4rem)}button{font:inherit;padding:.8rem;border:2px solid #9ccdf9;border-radius:.5rem;background:#163d62;color:white;cursor:pointer}button:focus-visible{outline:3px solid #ffd770}main{display:grid;grid-template-columns:2fr 1fr;gap:2rem}section{background:#18334e;padding:1.5rem;border-radius:1rem;min-width:0}ul{padding:0;list-style:none;display:flex;gap:1rem;flex-wrap:wrap}li{background:#214966;padding:1.2rem;border-radius:.7rem;min-width:7rem}strong{font-size:clamp(2rem,6vw,5rem);display:block;color:#a7f5cf}small{display:block;font-size:1.1rem;margin-top:.4rem}#board-status{color:#ffd770}#board-day{color:#cbd5e1}@media(max-width:650px){main{grid-template-columns:1fr}section{padding:1rem}li{min-width:5rem}body{padding:1rem}}
    </style>
</head>
<body>
    <header><div><h1>Clinic waiting-area board</h1><p id="board-day"></p></div><button id="board-sound" type="button" aria-pressed="false">Enable call sound</button></header>
    <p id="board-status" role="status">Connecting to live updates…</p>
    <main id="board-content" aria-live="polite">
        <section><h2>Now serving</h2><ul id="board-serving"></ul><p id="board-serving-empty">No ticket is being called.</p></section>
        <section><h2>Up next</h2><ul id="board-next"></ul><p id="board-next-empty">No waiting tickets.</p></section>
    </main>
    <p>Keep your ticket number. Priority patients may be called first.</p>
    @php
        $boardConfig = ['state' => $state, 'stateUrl' => route('queue-board.state', $token), 'channel' => $channel,
            'key' => config('broadcasting.connections.reverb.key'), 'port' => config('broadcasting.connections.reverb.options.port'),
            'scheme' => config('broadcasting.connections.reverb.options.scheme'), 'enabled' => config('broadcasting.default') === 'reverb'];
    @endphp
    <script id="queue-board-config" type="application/json">@json($boardConfig)</script>
    <script src="{{ mix('js/queue-board.js') }}" defer></script>
</body>
</html>
