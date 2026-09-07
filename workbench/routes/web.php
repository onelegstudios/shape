<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Onelegstudios\Shape\Facades\Shape;

// The gallery is served twice, because the seed layer is an optional import
// rather than a setting: /  is shape.css on its own, /seed adds shape-seed.css.
// Same view, same fixtures, different stylesheet — which is exactly the choice
// an application makes in its own CSS.
$gallery = function (string $stylesheet, bool $seeded) {
    // The gallery has to show a field that failed validation, and the error
    // component reads the bag the session middleware would normally share. This
    // stands in for that, so the invalid state is visible without a form to post.
    $errors = (new ViewErrorBag)->put('default', new MessageBag([
        'billing_email' => 'That address is already in use on another account.',
    ]));

    // Rows for the data-display sections. A route closure may read the request
    // and build a paginator from it — which is the whole point of the pager
    // taking the paginator rather than looking the current page up itself.
    $invoices = collect([
        ['number' => 'INV-1042', 'client' => 'Acme Corp', 'state' => 'Paid', 'tone' => 'success', 'total' => '£240.00'],
        ['number' => 'INV-1041', 'client' => 'Globex', 'state' => 'Overdue', 'tone' => 'danger', 'total' => '£1,180.00'],
        ['number' => 'INV-1040', 'client' => 'Initech', 'state' => 'Draft', 'tone' => 'neutral', 'total' => '£96.50'],
        ['number' => 'INV-1039', 'client' => 'Umbrella', 'state' => 'Paid', 'tone' => 'success', 'total' => '£3,400.00'],
        ['number' => 'INV-1038', 'client' => 'Soylent', 'state' => 'Sent', 'tone' => 'info', 'total' => '£512.00'],
    ]);

    $people = collect([
        ['name' => 'Alex Lindqvist', 'initials' => 'AL', 'role' => 'Owner'],
        ['name' => 'Gabriel Haas', 'initials' => 'GH', 'role' => 'Admin'],
        ['name' => 'Kim Jansen', 'initials' => 'KJ', 'role' => 'Member'],
    ]);

    $pages = new LengthAwarePaginator(
        $invoices,
        120,
        10,
        LengthAwarePaginator::resolveCurrentPage(),
        ['path' => '/'],
    );

    return view('preview')
        ->with('errors', $errors)
        ->with('invoices', $invoices)
        ->with('people', $people)
        ->with('pages', $pages)
        ->with('stylesheet', $stylesheet)
        ->with('seeded', $seeded);
};

Route::get('/', fn () => $gallery('workbench/resources/css/preview.css', false));
Route::get('/seed', fn () => $gallery('workbench/resources/css/preview-seed.css', true));

// The other half of the feedback channel. There is no Livewire request here for
// a dispatch to ride on, so this flashes to the session — and the toaster on the
// redirected page renders it as JSON for shape.js to replay as the same browser
// event. One code path builds the toast either way.
Route::get('/flash', function () {
    Shape::toast()->success('Invoice sent')->description('Through the session, after a redirect.')->send();
    Shape::toast()->warning('And a second one')->description('Both survive, which flashing twice would not.')->send();

    return redirect('/');
});

// A face for the avatar preview.
//
// The avatar's `src` is a prop with a picture attached to it, so the page about
// it has to show one, and a drawn placeholder shows the shape of the prop rather
// than the thing it is for — a photograph is cropped, lit and off-centre in ways
// an icon never is, which is the whole reason the component paints a fill under
// it and clips it to a circle.
//
// These three are stock portraits from Unsplash, cropped square and small enough
// to sit in the repository without ceremony. `workbench/` is export-ignored, so
// they stay here and never reach an application's vendor directory. Their
// photographers and licence are in `workbench/resources/images/README.md`.
//
// The people in them are strangers, which is why the docs cast is invented: an
// avatar's `alt` is the person's accessible name, and a stock face labelled with
// a real person's name announces that person to a screen reader.
//
// Only those three exist. A name with no file 404s rather than falling back to
// something drawn: the previews name people deliberately, and a missing face
// should fail where it is introduced rather than quietly become someone else.
Route::get('/avatars/{name}.webp', function (string $name) {
    $file = \Orchestra\Testbench\package_path('workbench/resources/images/'.basename($name).'.webp');

    abort_unless(is_file($file), 404);

    return response()->file($file, ['Content-Type' => 'image/webp']);
})->name('shape.docs.avatar');

// The overlays in the docs previews are meant to open.
//
// A modal, a drawer, a dropdown, a popover and a tooltip are all components whose
// props you can only see the effect of once they are on screen, so the docs site
// serves the same `shape.js` a consuming application imports.
Route::get('/shape-docs.js', function () {
    return response()
        ->file(\Orchestra\Testbench\package_path('resources/js/shape.js'), [
            'Content-Type' => 'text/javascript',
        ]);
})->name('shape.docs.js');

// The previews inside the docs site need Shape's stylesheet, and laradocs'
// layout is not this package's file to edit. It exposes a `head` stack, so the
// workbench provider pushes a link to this route into it.
Route::get('/shape-docs.css', function () {
    return response()
        ->file(\Orchestra\Testbench\package_path('workbench/resources/css/docs.css'), [
            'Content-Type' => 'text/css',
        ]);
})->name('shape.docs.css');
