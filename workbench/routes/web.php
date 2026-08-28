<?php

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

    return view('preview')->with('errors', $errors);
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
