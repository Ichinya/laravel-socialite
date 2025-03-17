<?php

use Ichinya\LaravelSocialite\LaravelSocialite;

Route::middleware(['web'])->group(function () {
    Route::controller(LaravelSocialite::class)
        ->prefix('socialite')
        ->as('socialite.')
        ->group(static function (): void {
            Route::get('/{driver}/redirect', 'redirect')->name('redirect');
            Route::get('/{driver}/callback', 'callback')->name('callback');
        });
});
