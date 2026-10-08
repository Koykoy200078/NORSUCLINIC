# Livewire 3 JavaScript Integration (this project runs Livewire 3.8)

Livewire 4's `interceptMessage()` / `interceptRequest()` do **not** exist in v3. Use the v3 hooks below
(source: Livewire 3.x docs, `docs/javascript.md`).

## Initialization events

```js
document.addEventListener('livewire:init', () => {
    // Livewire is loaded but not yet initialized on the page (register hooks here)
})

document.addEventListener('livewire:initialized', () => {
    // Livewire finished initializing on the page
})
```

## Commit hook (one component update)

```js
Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
    // before the commit payload is sent

    respond(() => { /* response received, not yet processed */ })

    succeed(({ snapshot, effects }) => {
        // processed successfully
        queueMicrotask(() => { /* after the DOM was updated */ })
    })

    fail(() => { /* request failed */ })
})
```

## Request hook (the whole HTTP request)

```js
Livewire.hook('request', ({ url, options, payload, respond, succeed, fail }) => {
    respond(({ status, response }) => { /* raw response */ })
    succeed(({ status, json }) => { /* parsed JSON */ })
    fail(({ status, content, preventDefault }) => {
        // preventDefault() disables Livewire's default error modal,
        // e.g. to show a toastr message on 403 instead
    })
})
```

## Component / element / morph hooks

```js
Livewire.hook('component.init', ({ component, cleanup }) => {})
Livewire.hook('element.init', ({ el, component }) => {})
Livewire.hook('morph.updating', ({ el, toEl, component }) => {})
Livewire.hook('morph.updated', ({ el, component }) => {})
Livewire.hook('morph.removed', ({ el, component }) => {})
```

## In this project

- Existing v3-style code: `Livewire.hook("element.init", …)` in `resources/assets/js/category/category.js`,
  `doctors/doctors.js`, `patients/patients.js`, `patients/detail.js`.
- **Livewire 2 leftovers that never run on v3:**
  - `document.addEventListener('livewire:load', …)` + `window.livewire.hook('message.processed', …)` in
    `resources/assets/js/custom/custom.js` (re-initialises Select2 after updates) and `doctors/detail.js`;
  - `beforePushState` / `beforeReplaceState` in `livewire-turbolinks.js`.
  Port them to `livewire:init` + `Livewire.hook('commit', …)` (or remove if dead), with a browser check. Tracked in
  `docs/audit-plan-2026-10-08.md`.
- Toast messages: `displaySuccessMessage()` / `displayErrorMessage()` from `resources/assets/js/custom/custom.js`.
- Compiled by Laravel Mix (`npm run dev` / `npm run prod`). Never load a script from a CDN: the clinic devices must work
  with no internet.
