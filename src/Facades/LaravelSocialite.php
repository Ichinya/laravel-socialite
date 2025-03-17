<?php

namespace Ichinya\LaravelSocialite\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Ichinya\LaravelSocialite\LaravelSocialite
 */
class LaravelSocialite extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Ichinya\LaravelSocialite\LaravelSocialite::class;
    }
}
