<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Onelegstudios\Shape\Facades\Shape;

Route::get('/', function () {
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
        ['number' => 'INV-1038', 'client' => 'Soylent', 'state' => 'Sent', 'tone' => 'accent', 'total' => '£512.00'],
    ]);

    $people = collect([
        ['name' => 'Ada Lovelace', 'initials' => 'AL', 'role' => 'Owner'],
        ['name' => 'Grace Hopper', 'initials' => 'GH', 'role' => 'Admin'],
        ['name' => 'Katherine Johnson', 'initials' => 'KJ', 'role' => 'Member'],
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
        ->with('pages', $pages);
});

// The other half of the feedback channel. There is no Livewire request here for
// a dispatch to ride on, so this flashes to the session — and the toaster on the
// redirected page renders it as JSON for shape.js to replay as the same browser
// event. One code path builds the toast either way.
Route::get('/flash', function () {
    Shape::toast()->success('Invoice sent')->description('Through the session, after a redirect.')->send();
    Shape::toast()->warning('And a second one')->description('Both survive, which flashing twice would not.')->send();

    return redirect('/');
});
