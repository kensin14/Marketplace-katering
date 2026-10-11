<?php

namespace App\Foundation;

use Illuminate\Foundation\Application as BaseApplication;

class LaravelApplication extends BaseApplication
{
    public function bootstrapPath($path = '')
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'laravel-core'.($path !== '' ? DIRECTORY_SEPARATOR.$path : '');
    }
}
