<?php namespace Jonasva\FacebookInsights;

use Illuminate\Support\ServiceProvider;

class FacebookInsightsServiceProvider extends ServiceProvider
{

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('jonasva/laravel-facebook-insights', 'facebook-insights');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['facebook-insights'] = $this->app->share(function($app)
		{
			return new FacebookInsights($app['config']);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('facebook-insights');
	}

}
