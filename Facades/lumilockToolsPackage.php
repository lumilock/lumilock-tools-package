<?php

namespace lumilock\lumilockToolsPackage\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Request;

class lumilockToolsPackage extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'lumilockToolsPackage';
    }
}
