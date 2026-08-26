<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

Route::get('/', function () {
    // The gallery has to show a field that failed validation, and the error
    // component reads the bag the session middleware would normally share. This
    // stands in for that, so the invalid state is visible without a form to post.
    $errors = (new ViewErrorBag)->put('default', new MessageBag([
        'billing_email' => 'That address is already in use on another account.',
    ]));

    return view('preview')->with('errors', $errors);
});
