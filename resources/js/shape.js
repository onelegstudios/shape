/*
| Shape's behaviour that the platform does not already supply.
|
| Almost all of it is the overlays. `<dialog>` gives modals their focus trap,
| their top layer, Escape and inertness; the `popover` attribute gives menus
| light dismiss and Escape; `::backdrop` and `:has()` give the scrim and the
| scroll lock. None of that is below.
|
| What is left is twelve small jobs, wired as delegated listeners on the document
| rather than as per-element state:
|
|   1. a fallback for `command` / `commandfor`, for browsers that predate them
|   2. a fallback for `closedby`, so a click outside a dialog closes it
|   3. holding a non-dismissible dialog open when Escape is pressed
|   4. keeping `aria-expanded` on a popover's trigger honest
|   5. arrow-key movement between menu items
|   6. arrow-key movement between tabs, and the panel swap that goes with it —
|      the one job in here that is not an overlay, and the only reason a page
|      with no overlays on it might still want this file
|   7. showing and hiding tooltips, which are the one overlay a pointer opens
|   8. placing every anchored overlay, which CSS anchor positioning was going to
|      do until it turned out to ship in halves
|   9. removing a toast or an alert someone dismissed, which is the one thing in
|      this library the platform has no primitive for
|  10. building toasts, by cloning a template the toaster already rendered
|  11. filling in the shared confirm dialog and dispatching what it was told to
|  12. replaying whatever the server flashed into the session, as the same events
|      it would have dispatched live
|
| plus a pair of window events so a Livewire component can open an overlay by
| name.
|
| There is no Alpine in here. It installs as an Alpine plugin because that is
| where consumers expect to register it, but it does not use the argument and
| works exactly as well called on its own:
|
|   import shape from '../../vendor/onelegstudios/laravel-shape/resources/js/shape.js'
|
|   Alpine.plugin(shape)   // or just: shape()
*/

const OPEN_DELAY = 120
const HIDE_DELAY = 80
const OFFSET = 6
const TOAST_DURATION = 5000
const LEAVE_DELAY = 200

let installed = false

export default function shape() {
    if (installed || typeof document === 'undefined') return

    installed = true

    invokerFallback()
    lightDismiss()
    persistentDialogs()
    expandedState()
    menuKeys()
    tabKeys()
    tooltips()
    trackAnchor()
    dismissals()
    toasts()
    confirms()
    livewireBridge()

    // Last, because it dispatches the events the two above have just started
    // listening for.
    feedback()
}

/* ------------------------------------------------------------------ helpers */

function target(name) {
    return typeof name === 'string' ? document.getElementById(name) : null
}

function isDialog(el) {
    return el instanceof HTMLElement && el.tagName === 'DIALOG'
}

function open(name) {
    const el = target(name)

    if (!el) return

    isDialog(el) ? el.showModal() : el.showPopover?.()
}

function close(name) {
    const el = target(name)

    if (!el) return

    isDialog(el) ? el.close() : el.hidePopover?.()
}

function reducedMotion() {
    return matchMedia('(prefers-reduced-motion: reduce)').matches
}

function fill(root, selector, text) {
    const el = root.querySelector(selector)

    if (el) el.textContent = text ?? ''
}

// One walker for menus and for tab strips. They ask the same question — which of
// these can the arrow keys land on — and answer it with the same two exclusions,
// so the selector is a parameter rather than a second copy of this function.
function items(root, selector) {
    return [...root.querySelectorAll(selector)].filter(
        (item) => !item.disabled && item.getAttribute('aria-disabled') !== 'true',
    )
}

// Moves focus within a list that is a single tab stop, cycling at both ends.
// Returns the element it moved to, or null if the key was not one of ours.
function roving(list, key, vertical) {
    const forward = vertical ? 'ArrowDown' : 'ArrowRight'
    const back = vertical ? 'ArrowUp' : 'ArrowLeft'
    const index = list.indexOf(document.activeElement)

    switch (key) {
        case forward:
            return list[(index + 1) % list.length]
        case back:
            return list[(index - 1 + list.length) % list.length]
        case 'Home':
            return list[0]
        case 'End':
            return list[list.length - 1]
        default:
            return null
    }
}

/* ------------------------------------------------------------- 1. invokers */

/*
| `command` and `commandfor` are the declarative way to open a dialog, and where
| they are supported this file has nothing to do with opening one.
|
| The fallback is a single delegated listener rather than a per-button handler,
| so a trigger rendered after page load — by Livewire, by a table redraw — works
| without anything having to re-scan the DOM.
*/
function invokerFallback() {
    if ('command' in HTMLButtonElement.prototype) return

    document.addEventListener('click', (event) => {
        const invoker = event.target instanceof Element ? event.target.closest('[commandfor]') : null

        if (!invoker) return

        const el = target(invoker.getAttribute('commandfor'))

        if (!el) return

        event.preventDefault()

        switch (invoker.getAttribute('command')) {
            case 'show-modal':
                el.showModal?.()
                break
            case 'close':
                el.close?.()
                break
            case 'request-close':
                el.requestClose ? el.requestClose() : el.close?.()
                break
            case 'show-popover':
                el.showPopover?.()
                break
            case 'hide-popover':
                el.hidePopover?.()
                break
            case 'toggle-popover':
                el.togglePopover?.()
                break
        }
    })
}

/* -------------------------------------------------------- 2. light dismiss */

/*
| Clicking outside a modal closes it — which a `<dialog>` does not do on its own.
|
| `closedby="any"` is the platform's answer and the components declare it, so
| where it is supported this function does nothing at all. It is recent enough
| that a fallback is still worth twenty lines.
|
| The fallback cannot simply ask whether the click landed on the dialog.
| `::backdrop` is painted by the dialog rather than being an element of its own,
| so a click there targets the dialog — and in this design the dialog *is* the
| panel, so a click on its own padding targets it too. Comparing the pointer
| against the dialog's box is what separates the two.
*/
function lightDismiss() {
    if ('closedBy' in HTMLDialogElement.prototype) return

    document.addEventListener('click', (event) => {
        const dialog = event.target

        if (!isDialog(dialog)) return
        if (!dialog.hasAttribute('data-shape-modal') && !dialog.hasAttribute('data-shape-drawer')) return
        if (dialog.hasAttribute('data-shape-persistent')) return

        // A click produced by the keyboard — Enter on a button inside the
        // dialog — carries no coordinates, and (0, 0) is outside the box for
        // every centred modal there has ever been.
        if (event.detail === 0) return

        const rect = dialog.getBoundingClientRect()

        const inside =
            event.clientX >= rect.left &&
            event.clientX <= rect.right &&
            event.clientY >= rect.top &&
            event.clientY <= rect.bottom

        if (!inside) dialog.close()
    })
}

/* ---------------------------------------------------------- 3. persistence */

/*
| `dismissible: false` is declared as `closedby="none"`, and this covers the
| browsers that do not read that attribute yet: `cancel` is a native event, and
| Escape closes a dialog unless something calls preventDefault on it.
|
| `cancel` does not bubble, so the listener is registered for the capture phase —
| which still runs for events that don't bubble, and is what lets one listener
| cover dialogs that don't exist yet.
*/
function persistentDialogs() {
    document.addEventListener(
        'cancel',
        (event) => {
            if (event.target instanceof Element && event.target.hasAttribute('data-shape-persistent')) {
                event.preventDefault()
            }
        },
        true,
    )
}

/* -------------------------------------------------------- 4. aria-expanded */

/*
| A popover's trigger has to say whether the thing it controls is open. Browsers
| do not all reflect that from `popovertarget`, so it is set from the popover's
| own `toggle` event — the state and the announcement then come from the same
| place and cannot drift.
*/
function expandedState() {
    document.addEventListener(
        'toggle',
        (event) => {
            const el = event.target

            if (!(el instanceof HTMLElement) || !el.hasAttribute('data-shape-popover')) return

            const isOpen = event.newState === 'open'

            document
                .querySelectorAll(`[popovertarget="${CSS.escape(el.id)}"]`)
                .forEach((trigger) => trigger.setAttribute('aria-expanded', String(isOpen)))

            if (isOpen && el.hasAttribute('data-shape-menu')) {
                items(el, '[data-shape-menu-item]')[0]?.focus()
            }

            position(el, isOpen)
        },
        true,
    )
}

/* ------------------------------------------------------------ 5. menu keys */

/*
| A menu is a single tab stop that arrow keys move within. Items keep their
| natural tab order inside the popover as a fallback, so a browser that fails to
| run this still leaves every item reachable.
*/
function menuKeys() {
    document.addEventListener('keydown', (event) => {
        const menu = event.target instanceof Element ? event.target.closest('[data-shape-menu]') : null

        if (!menu) return

        const list = items(menu, '[data-shape-menu-item]')

        if (list.length === 0) return

        // A menu is vertical whichever way its trigger sits, so the arrow keys
        // it answers are fixed.
        const next = roving(list, event.key, true)

        if (!next) return

        event.preventDefault()
        next.focus()
    })

    // An action that leaves its own menu standing looks like it didn't fire. The
    // popover only light-dismisses on a click *outside* itself, so closing after
    // an item is chosen is this file's job. `data-shape-keep-open` is the way out
    // for the items that toggle something and expect to be clicked again.
    document.addEventListener('click', (event) => {
        const item = event.target instanceof Element ? event.target.closest('[data-shape-menu-item]') : null

        if (!item || item.hasAttribute('data-shape-keep-open')) return

        item.closest('[data-shape-menu]')?.hidePopover?.()
    })
}

/* ----------------------------------------------------------------- 6. tabs */

/*
| A tab strip is a single tab stop that arrow keys move within, and the panel
| swap that goes with it. It is the one job in this file that is not an overlay.
|
| Only the `role="tab"` mode is touched. A strip written `as="nav"` carries no
| `data-shape-tablist`, so navigation links keep answering to Tab, which is what
| they should answer to.
|
| Activation follows focus, which is the ARIA pattern's default and the right one
| while a panel is markup the browser already has. If a panel ever costs a
| request to show, this is the line to revisit.
*/
function tabKeys() {
    document.addEventListener('keydown', (event) => {
        const strip = event.target instanceof Element ? event.target.closest('[data-shape-tablist]') : null

        if (!strip) return

        const list = items(strip, '[role="tab"]')

        if (list.length === 0) return

        const next = roving(list, event.key, strip.getAttribute('aria-orientation') === 'vertical')

        if (!next) return

        event.preventDefault()
        next.focus()
        select(next, strip)
    })

    document.addEventListener('click', (event) => {
        const tab = event.target instanceof Element ? event.target.closest('[role="tab"][aria-controls]') : null

        if (!tab) return

        const strip = tab.closest('[data-shape-tablist]')

        if (strip) select(tab, strip)
    })
}

/*
| Writes the selection across the whole strip rather than toggling the tab that
| was clicked: `aria-selected`, the roving `tabindex` — exactly one tab is in the
| tab order — and `hidden` on each panel.
|
| Panels are found by id, so they do not have to be siblings of the strip, and a
| tab pointing at a panel that isn't on the page is simply skipped.
|
| It walks every tab rather than the filtered list the arrow keys move through: a
| disabled tab is one the arrows skip, not one that gets to keep a tab stop it
| was rendered with. Leaving it out of the loop is how a strip ends up with two.
*/
function select(chosen, strip) {
    for (const tab of strip.querySelectorAll('[role="tab"]')) {
        const isChosen = tab === chosen

        tab.setAttribute('aria-selected', isChosen ? 'true' : 'false')
        tab.setAttribute('tabindex', isChosen ? '0' : '-1')

        const panel = target(tab.getAttribute('aria-controls'))

        if (panel) panel.hidden = !isChosen
    }
}

/* ------------------------------------------------------------- 7. tooltips */

/*
| The one overlay opened by a pointer rather than by a click, which is why it is
| a `popover="manual"` — an auto popover would light-dismiss the moment the
| pointer moved anywhere, including onto the thing it describes.
|
| `aria-describedby` is put on the first focusable element inside the wrapper
| rather than on the wrapper itself. A tooltip describes the control, and the
| control is what a screen reader lands on.
*/
function tooltips() {
    const timers = new WeakMap()

    const tip = (el) => target(el.getAttribute('data-shape-tooltip-for'))

    const show = (wrapper, delay) => {
        const el = tip(wrapper)

        if (!el) return

        clearTimeout(timers.get(wrapper))
        timers.set(
            wrapper,
            setTimeout(() => el.showPopover?.(), delay),
        )
    }

    const hide = (wrapper, delay = HIDE_DELAY) => {
        const el = tip(wrapper)

        if (!el) return

        clearTimeout(timers.get(wrapper))
        timers.set(
            wrapper,
            setTimeout(() => el.hidePopover?.(), delay),
        )
    }

    const wrapperFor = (node) =>
        node instanceof Element ? node.closest('[data-shape-tooltip-for]') : null

    document.addEventListener('pointerenter', (event) => {
        const wrapper = wrapperFor(event.target)

        if (wrapper) show(wrapper, OPEN_DELAY)
    }, true)

    document.addEventListener('pointerleave', (event) => {
        const wrapper = wrapperFor(event.target)

        if (wrapper) hide(wrapper)
    }, true)

    // Keyboard focus shows it immediately: someone tabbing through a toolbar has
    // already waited, and a delay there reads as the tooltip being broken.
    document.addEventListener('focusin', (event) => {
        const wrapper = wrapperFor(event.target)

        if (wrapper) show(wrapper, 0)
    })

    document.addEventListener('focusout', (event) => {
        const wrapper = wrapperFor(event.target)

        if (wrapper) hide(wrapper, 0)
    })

    // A manual popover has no light dismiss of its own, so Escape is wired here.
    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return

        document
            .querySelectorAll('[data-shape-tooltip]:popover-open')
            .forEach((el) => el.hidePopover?.())
    })

    // Describe the control, not the wrapper.
    const describe = (root) => {
        root.querySelectorAll?.('[data-shape-tooltip-for]').forEach((wrapper) => {
            const id = wrapper.getAttribute('data-shape-tooltip-for')
            const control = wrapper.querySelector('button, a[href], input, select, textarea, [tabindex]')

            if (control && !control.hasAttribute('aria-describedby')) {
                control.setAttribute('aria-describedby', id)
            }
        })
    }

    describe(document)
    new MutationObserver((records) =>
        records.forEach((record) => record.addedNodes.forEach((node) => describe(node))),
    ).observe(document.documentElement, { childList: true, subtree: true })
}

/* ------------------------------------------------- anchor position fallback */

/*
| Placement, for every anchored overlay, in every browser.
|
| This was a fallback behind `@supports (anchor-name: …)`. It is now the only
| path, because the split was the bug: anchor positioning arrives in pieces, so a
| browser could satisfy the gate, take the CSS path, and place nothing — while
| this stood down believing the stylesheet had it. One path behaves the same
| everywhere, and is the one the tests exercise.
*/
let tracking = null

function position(el, isOpen) {
    if (!isOpen) {
        if (tracking?.el === el) tracking = null

        return
    }

    const anchor = document.querySelector(
        `[popovertarget="${CSS.escape(el.id)}"], [data-shape-tooltip-for="${CSS.escape(el.id)}"]`,
    )

    if (!anchor) return

    tracking = { el, anchor }
    place(el, anchor)
}

function place(el, anchor) {
    const rect = anchor.getBoundingClientRect()
    const own = el.getBoundingClientRect()
    const placement = el.getAttribute('data-shape-placement') ?? 'bottom-start'
    const [side, align = 'center'] = placement.split('-')

    const below = rect.bottom + OFFSET
    const above = rect.top - own.height - OFFSET
    const fitsBelow = below + own.height <= innerHeight - OFFSET
    const fitsAbove = above >= OFFSET

    // Keep the side that was asked for while it fits, flip when it doesn't, and
    // when neither side fits take the one with more room rather than hanging off
    // the edge of both. This is `position-try-fallbacks`, by hand.
    const roomier = innerHeight - rect.bottom >= rect.top ? below : above
    const wanted = side === 'top'
        ? (fitsAbove ? above : fitsBelow ? below : roomier)
        : (fitsBelow ? below : fitsAbove ? above : roomier)

    let left = rect.left

    if (align === 'end') left = rect.right - own.width
    if (align === 'center') left = rect.left + rect.width / 2 - own.width / 2

    const top = Math.max(OFFSET, Math.min(wanted, innerHeight - own.height - OFFSET))

    left = Math.max(OFFSET, Math.min(left, innerWidth - own.width - OFFSET))

    el.style.position = 'fixed'
    el.style.margin = '0'
    el.style.top = `${top}px`
    el.style.left = `${left}px`
}

function trackAnchor() {
    const reposition = () => {
        if (tracking) place(tracking.el, tracking.anchor)
    }

    addEventListener('scroll', reposition, { passive: true, capture: true })
    addEventListener('resize', reposition, { passive: true })
}

/* ----------------------------------------------------------- 9. dismissal */

/*
| Removing a toast or an alert.
|
| The one job in this file with no platform primitive behind it. A dialog closes
| itself and a popover hides itself; an element someone asked to go away has to
| be taken out by something.
|
| `data-shape-leaving` is what the exit transition hangs off, and the element is
| removed a beat later. When the last toast leaves, the toaster stops being an
| open popover — an empty one is harmless, but leaving it in the top layer means
| leaving it in the accessibility tree too.
*/
function dismissals() {
    document.addEventListener('click', (event) => {
        const button = event.target instanceof Element ? event.target.closest('[data-shape-dismiss]') : null

        if (!button) return

        const el = button.closest('[data-shape-toast], [data-shape-alert]')

        if (el) dismiss(el)
    })
}

function dismiss(el) {
    el.setAttribute('data-shape-leaving', '')

    setTimeout(() => {
        const toaster = el.closest('[data-shape-toaster]')

        el.remove()

        if (toaster && !toaster.querySelector('[data-shape-toast]')) {
            toaster.removeAttribute('data-shape-open')
            toaster.hidePopover?.()
        }
    }, reducedMotion() ? 0 : LEAVE_DELAY)
}

/* -------------------------------------------------------------- 10. toasts */

/*
| A toast arrives as a browser event and leaves as DOM.
|
| The markup is not in here. The toaster renders one `<x-shape::toast>` per tone
| into a `<template>`, and this clones the one the payload asked for — so the
| toast's design lives in Blade with every other component, Tailwind scans it
| like every other component, and this function's whole job is filling in two
| pieces of text.
|
| Tone chooses the live region as well as the template. A failure is announced
| assertively; everything else waits its turn. One region cannot make that
| distinction, which is why there are two.
|
| Opening it is `promote()`, below, and there is more to that than it looks.
*/
function toasts() {
    addEventListener('shape:toast', (event) => {
        const toast = event.detail?.toast ?? event.detail ?? {}
        const toaster = document.querySelector('[data-shape-toaster]')

        if (!toaster) return

        const tone = toast.tone ?? 'neutral'

        const template =
            toaster.querySelector(`[data-shape-toast-template="${CSS.escape(tone)}"]`) ??
            toaster.querySelector('[data-shape-toast-template="neutral"]')

        const el = template?.content.querySelector('[data-shape-toast]')?.cloneNode(true)

        if (!el) return

        fill(el, '[data-shape-toast-heading]', toast.heading)
        fill(el, '[data-shape-toast-description]', toast.description)

        const region = toaster.querySelector(
            `[data-shape-toast-region="${tone === 'danger' ? 'assertive' : 'polite'}"]`,
        )

        ;(region ?? toaster).append(el)

        promote(toaster)

        const duration = typeof toast.duration === 'number' ? toast.duration : TOAST_DURATION

        if (duration > 0) countdown(el, duration)
    })
}

/*
| Putting the toaster on top, and keeping it there.
|
| The top layer has exactly one ordering rule: elements are stacked in the order
| they were promoted into it. That is normally invisible, because the library only
| ever has one thing in it at a time. It stops being invisible the moment a modal
| opens over a toast that is already showing — the dialog was promoted later, so
| the dialog paints on top, and no stylesheet can answer that. Verified by
| screenshot, because nothing about it is visible from the DOM.
|
| Re-promoting is the whole fix: hiding and showing the popover puts it back at
| the top of the stack. It is done only when a dialog is open, because taking an
| element out of the top layer and back in re-runs the entry transition on every
| toast inside it, and there is no reason to pay that on the common path.
|
| `showPopover()` throws when the popover is already open, so the open state is
| kept on an attribute of its own. `togglePopover(true)` would say the same thing
| in one line, but its `force` argument landed later than the rest of the popover
| API, and this is a file with no feature detection left in it.
|
| What this cannot fix: a modal dialog makes the rest of the document inert, and
| a toast is outside the dialog. So while a modal is open a toast is visible above
| it and cannot be clicked. That is the platform's rule about modality rather than
| a z-index problem, and no other implementation of a toast escapes it either.
*/
function promote(toaster) {
    if (!toaster.hasAttribute('data-shape-open')) {
        toaster.setAttribute('data-shape-open', '')
        toaster.showPopover?.()

        return
    }

    if (document.querySelector('dialog[open]')) {
        toaster.hidePopover?.()
        toaster.showPopover?.()
    }
}

/*
| The timer, which pauses while someone is reading.
|
| These listeners are bound to the element rather than delegated to the document,
| which is the one place in this file that happens. It is deliberate: the state
| being tracked — how much of this toast's time is left — belongs to one element
| that this function created and will remove, so it goes out with it.
*/
function countdown(el, duration) {
    let remaining = duration
    let started = Date.now()
    let id = setTimeout(() => dismiss(el), remaining)

    const pause = () => {
        clearTimeout(id)
        remaining -= Date.now() - started
    }

    const resume = () => {
        if (remaining <= 0) return

        started = Date.now()
        id = setTimeout(() => dismiss(el), remaining)
    }

    el.addEventListener('pointerenter', pause)
    el.addEventListener('pointerleave', resume)
    el.addEventListener('focusin', pause)
    el.addEventListener('focusout', resume)
}

/* ------------------------------------------------------------- 11. confirm */

/*
| One dialog in the layout, filled in per question.
|
| What the accept button does is the whole of this library's server integration,
| and it is three lines: dispatch a window event by the name the payload gave,
| then close. Nothing here knows what is listening. A Livewire component's
| `#[On('deleteProject')]` is a window-event listener, so it hears this without
| anything being wired up, and so does Alpine, and so does a plain listener.
|
| The event goes out before the dialog closes. Closing is what returns focus and
| tears the dialog down, and none of that should be able to decide whether the
| thing someone confirmed actually happened.
*/
const confirmParams = new WeakMap()

function confirms() {
    addEventListener('shape:confirm', (event) => {
        const payload = event.detail?.confirm ?? event.detail ?? {}
        const dialog = target(payload.name ?? 'shape-confirm')

        if (!dialog) return

        if (payload.heading != null) fill(dialog, `#${CSS.escape(dialog.id)}-heading`, payload.heading)

        fill(dialog, '[data-shape-confirm-message]', payload.message)

        const cancel = dialog.querySelector('[data-shape-overlay-close]')

        if (cancel && payload.cancel != null) cancel.textContent = payload.cancel

        const accept = dialog.querySelector('[data-shape-confirm-accept]')

        if (accept) {
            if (payload.accept != null) accept.textContent = payload.accept

            accept.setAttribute('data-shape-tone', payload.tone ?? 'neutral')
            accept.setAttribute('data-shape-confirm-then', payload.then ?? '')

            confirmParams.set(accept, payload.params ?? [])
        }

        dialog.showModal?.()
    })

    document.addEventListener('click', (event) => {
        const accept = event.target instanceof Element ? event.target.closest('[data-shape-confirm-accept]') : null

        if (!accept) return

        const name = accept.getAttribute('data-shape-confirm-then')

        if (name) {
            dispatchEvent(new CustomEvent(name, { detail: confirmParams.get(accept) ?? [] }))
        }

        accept.closest('dialog')?.close()
    })
}

/* ------------------------------------------------------------ 12. the flash */

/*
| Feedback that had to survive a redirect.
|
| `Shape::toast()` dispatches through Livewire when it can and flashes to the
| session when it can't. The flashed half is rendered by the toaster as a block
| of JSON, and replayed here as exactly the browser event the other half would
| have produced — so there is one code path building a toast, not two that can
| drift apart. The overlays learned that the expensive way.
|
| The payload is removed once it has been read: a Livewire round trip that
| re-renders the layout would otherwise replay it.
|
| `DOMContentLoaded` only fires once, but `wire:navigate` swaps the page's
| content without one — a link followed, or a redirect landed on, that way
| carries its own `<script data-shape-feedback>` into the swap with nothing
| left to read it. `livewire:navigated` fires after every such swap, so
| replay runs there too; the listener costs nothing in an application that
| never fires it, same as the rest of this file's stance on Livewire.
*/
function feedback() {
    const replay = () => {
        document.querySelectorAll('[data-shape-feedback]').forEach((script) => {
            let entries

            try {
                entries = JSON.parse(script.textContent ?? '[]')
            } catch {
                return
            }

            script.remove()

            if (!Array.isArray(entries)) return

            entries.forEach((entry) => {
                if (entry?.event) {
                    dispatchEvent(new CustomEvent(entry.event, { detail: entry.payload ?? {} }))
                }
            })
        })
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', replay, { once: true })
    } else {
        replay()
    }

    document.addEventListener('livewire:navigated', replay)
}

/* ------------------------------------------------------------ the bridge */

/*
| Server-driven opening, for the cases where the decision genuinely lives there:
|
|   $this->dispatch('shape:open', name: 'confirm-delete');
|
| A browser event rather than a Livewire dependency, so the package keeps working
| in an application that has no Livewire in it.
*/
function livewireBridge() {
    addEventListener('shape:open', (event) => open(event.detail?.name ?? event.detail))
    addEventListener('shape:close', (event) => close(event.detail?.name ?? event.detail))
}
