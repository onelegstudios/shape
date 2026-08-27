/*
| Shape's overlay behaviour.
|
| Everything here is what the platform does not supply on its own. `<dialog>`
| gives modals their focus trap, their top layer, Escape and inertness; the
| `popover` attribute gives menus light dismiss and Escape; `::backdrop` and
| `:has()` give the scrim and the scroll lock. None of that is below.
|
| What is left is five small jobs, wired as delegated listeners on the document
| rather than as per-element state:
|
|   1. a fallback for `command` / `commandfor`, for browsers that predate them
|   2. a fallback for `closedby`, so a click outside a dialog closes it
|   3. holding a non-dismissible dialog open when Escape is pressed
|   4. keeping `aria-expanded` on a popover's trigger honest
|   5. arrow-key movement between menu items
|   6. showing and hiding tooltips, which are the one overlay a pointer opens
|   7. placing every anchored overlay, which CSS anchor positioning was going to
|      do until it turned out to ship in halves
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

let installed = false

export default function shape() {
    if (installed || typeof document === 'undefined') return

    installed = true

    invokerFallback()
    lightDismiss()
    persistentDialogs()
    expandedState()
    menuKeys()
    tooltips()
    trackAnchor()
    livewireBridge()
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

function items(menu) {
    return [...menu.querySelectorAll('[data-shape-menu-item]')].filter(
        (item) => !item.disabled && item.getAttribute('aria-disabled') !== 'true',
    )
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
                items(el)[0]?.focus()
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

        const list = items(menu)

        if (list.length === 0) return

        const index = list.indexOf(document.activeElement)

        let next = null

        switch (event.key) {
            case 'ArrowDown':
                next = list[(index + 1) % list.length]
                break
            case 'ArrowUp':
                next = list[(index - 1 + list.length) % list.length]
                break
            case 'Home':
                next = list[0]
                break
            case 'End':
                next = list[list.length - 1]
                break
            default:
                return
        }

        event.preventDefault()
        next?.focus()
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

/* ------------------------------------------------------------- 6. tooltips */

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
