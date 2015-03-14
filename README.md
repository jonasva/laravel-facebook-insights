# Laravel Facebook Insights

FacebookInsights provides a quick way to access insights of a facebook page with the Facebook OpenGraph API v2. It works with a permanent access token so no user interaction is required. A common usage would be to have a statistics dashboard that needs to regularly fetch insights of a facebook page.

## Installation

To get the latest version of FacebookInsights require it in your `composer.json` file.

~~~
"jonasva/laravel-facebook-insights": "dev-master"
~~~

Run `composer update jonasva/laravel-facebook-insights to install it.

Once FacebookInsights is installed you need to register its service provider with your application. Open `app/config/app.php` and find the `providers` key.

~~~php
'providers' => array(

    'Jonasva\FacebookInsights\FacebookInsightsServiceProvider',

)
~~~

A Facade for easy access is also included. You can register the facade in the `aliases` key of your `app/config/app.php` file.

~~~php
'aliases' => array(

    'FacebookInsights' => 'Jonasva\FacebookInsights\Facades\FacebookInsights',

)
~~~

### Publish the configurations

Run this on the command line from the root of your project:

~~~
$ php artisan config:publish jonasva/laravel-facebook-insights
~~~

A configuration file will be published to `app/config/packages/jonasva/laravel-facebook-insights/config.php`

## Usage

Coming soon