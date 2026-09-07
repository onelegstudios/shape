# Preview portraits

Three photographs, used as the `src` in the [avatar](../../../docs/components/avatar.md)
previews and in the workbench gallery. They come from Unsplash, cropped square
around the face by the Unsplash CDN and re-encoded at 160×160 WebP — twice the
largest size the component renders (`lg` is 48px), a couple of kilobytes each.

## The cast

The people in the photographs are strangers, so the previews name nobody real.
An avatar's `alt` is the person's accessible name, and a stock face under a real
name announces that person to a screen reader. The cast is invented, the way the
invoices are Acme and Globex — and it is written down here because it is spread
across previews that are each read on their own.

| Name | Initials | Photograph |
| --- | --- | --- |
| Alex Lindqvist | `AL` | `alex.webp` |
| Gabriel Haas | `GH` | `gabriel.webp` |
| Kim Jansen | `KJ` | `kim.webp` |
| Maya Holm | `MH` | — |
| Ben Larsen | `BL` | — |

Alex carries the single-avatar previews — the hero, sizes and variants. All
three with photographs fill the picture, tone and group previews and the
workbench gallery; the list preview names Alex and Gabriel. Maya and Ben appear
once each, in the tone preview, and have no photograph because nothing renders
one for them — a face no preview uses is a file that rots.

Not every appearance passes a name. The group preview and the gallery's stack
pass initials alone, because an avatar sitting next to a name already on the page
should announce nothing; that is the page's own lesson, applied to itself.

Add a face by putting it here under the person's first name and pointing an `src`
at `/avatars/<name>.webp`. The route serves what is on disk and 404s on what is
not, and `tests/Feature/WorkbenchGalleryTest.php` names the three that have to
resolve.

## Where the photographs came from

| File | Docs name | Photograph | Photographer |
| --- | --- | --- | --- |
| `alex.webp` | Alex Lindqvist | [bqe0J0b26RQ](https://unsplash.com/photos/woman-staring-directly-at-camera-near-pink-wall-bqe0J0b26RQ) | [Jimmy Fermin](https://unsplash.com/@jimmyferminphotography) |
| `gabriel.webp` | Gabriel Haas | [7YVZYZeITc8](https://unsplash.com/photos/man-wearing-henley-top-portrait-7YVZYZeITc8) | [Jurica Koletić](https://unsplash.com/@juricakoletic) |
| `kim.webp` | Kim Jansen | [3TLl_97HNJo](https://unsplash.com/photos/woman-wearing-black-crew-neck-shirt-3TLl_97HNJo) | [Aiony Haust](https://unsplash.com/@aiony) |

All three are under the [Unsplash licence](https://unsplash.com/license): free to
use, commercially or not, with no permission needed. Attribution is not required
and is given here anyway, because a repository should be able to say where the
files in it came from.

`workbench/` is `export-ignore` in `.gitattributes`, so these live in the
repository and never reach an application's vendor directory.
