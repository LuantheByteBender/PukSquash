<?php

use App\Repositories\CourtRepository;
use Illuminate\Support\Facades\Route;

Route::get('/debug/courts', function (CourtRepository $courts) {
    abort_unless(app()->environment('local'), 404);

    return response()->json(
        array_map(fn ($c) => $c->toArray(), $courts->listActive()),
    );
});

Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');