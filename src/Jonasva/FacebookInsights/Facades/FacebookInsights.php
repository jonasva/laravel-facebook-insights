<?php namespace Jonasva\FacebookInsights\Facades;

use Illuminate\Support\Facades\Facade;

class FacebookInsights extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'facebook-insights';
    }
}
