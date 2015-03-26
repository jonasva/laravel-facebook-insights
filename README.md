# Laravel Facebook Insights

FacebookInsights provides a quick way to access insights of a facebook page with the Facebook OpenGraph API v2. It works with a permanent access token so no user interaction is required. A common usage would be to have a statistics dashboard that needs to regularly fetch insights of a facebook page.

## Installation

To get the latest version of FacebookInsights require it in your `composer.json` file.

~~~
"jonasva/laravel-facebook-insights": "dev-master"
~~~

Run `composer update jonasva/laravel-facebook-insights` to install it.

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

### Config

#### Facebook App and Page information

To use this package, you'll need to setup your Facebook App ID, App secret, (permanent) access token and Page ID. For more information about this check the config file.

#### Cache

Facebook GraphApi responses get cache for 1 day by default. You can change this by altering the `cache-lifetime`.

## Usage

The package contains several useful methods to fetch facebook insights with the OpenGraph API. Methods can be called by using the facade `FacebookInsights`.
For example:
```php
    $startDate = new \DateTime('2015-03-15');
    $endDate = new \DateTime('2015-03-25');
    // fetch your page's total impressions for a given period
    $totalImpressions = FacebookInsights::getPageTotalImpressions($startDate, $endDate);
```

## Methods

This package currently provides insights for Page and Post objects. That said, any other OpenGraph queries can also be done by simply using the following method:
```php
    /**
     * Construct a facebook request
     *
     * @param string $query
     * @param array $params (optional)
     * @param string $method (optional)
     * @param string $object (optional)
     *
     * @return GraphObject
     */
    public function performGraphCall($query, $params = [], $object = null, $method = 'GET')
```

### Page Insights

Get the total amount of page fans (aka followers, people who liked the page)
```php
    /**
     * Get the total amount of page fans (people who liked the page)
     *
     * @return int
     */
    public function getPageTotalFans()
```

Get new fans per day for a given period
```php
    /**
     * Get new page fans per day for a given period
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return array
     */
    public function getPageNewFansPerDay(\DateTime $startDate, \DateTime $endDate)
```

Get the total number of new page fans for a given period
```php
    /**
     * Get the total number of new page fans for a given period
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return int
     */
    public function getPageTotalNewFans(\DateTime $startDate, \DateTime $endDate)
```

Get a page's impressions (The total number of impressions seen of any content associated with your Page) per day for a given period
```php
    /**
     * Get the page impressions per day for a given period
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return array
     */
    public function getPageImpressionsPerDay(\DateTime $startDate, \DateTime $endDate)
```

Get the total number of page impressions for a given period
```php
    /**
     * Get the total number of page impressions for a given period
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return int
     */
    public function getPageTotalImpressions(\DateTime $startDate, \DateTime $endDate)
```

Get a page's consumptions (The number of times people clicked on any of your content) per day for a given period
```php
    /**
     * Get the page consumptions per day for a given period
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return array
     */
    public function getPageConsumptionsPerDay(\DateTime $startDate, \DateTime $endDate)
    {
```

Get the total number of page consumptions for a given period
```php
    /**
     * Get the total number of page consumptions for a given period
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return int
     */
    public function getPageTotalConsumptions(\DateTime $startDate, \DateTime $endDate)
```

Get like, comment, share, rsvp, claim and answer counts for a page's posts grouped per day for a given period
```php
    /**
     * Get a page's positive feedback per day for a given period
     * The following actions are categorized as positive feedback:
     * like, comment, link (share), rsvp (respond to an event), claim, answer
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return array
     */
    public function getPagePositiveFeedbackPerDay(\DateTime $startDate, \DateTime $endDate)
```

Get accumulated (total) like, comment, share, rsvp, claim and answer counts for a page's posts grouped per day for a given period
```php
    /**
     * Get a page's accumulated positive feedback for a given period
     * The following actions are categorized as positive feedback:
     * like, comment, link (share), rsvp (respond to an event), claim, answer
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return array
     */
    public function getPageTotalPositiveFeedback(\DateTime $startDate, \DateTime $endDate)
```

Get a specific insight for a page for a given period. Insights can be found here: https://developers.facebook.com/docs/graph-api/reference/v2.2/insights#page_impressions
```php
    /**
     * Get a specific insight for a page for a given period
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param string $insight
     * @param string $period (optional)
     *
     * @return int
     */
    public function getPageInsight(\DateTime $startDate, \DateTime $endDate, $insight, $period = 'day')
```

Get a page's posts for a given period. This is not really an insight, but is needed to get post ID's which can later be used to collect post insights.
```php
    /**
     * Get the page's posts for a given period
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param int $limit
     *
     * @return array
     */
    public function getPagePosts(\DateTime $startDate, \DateTime $endDate, $limit = null)
```

### Post Insights

Post specific insights can only be collected by period `lifetime`, so no date range needs to be given.

Get a post's impressions
```php
    /**
     * Get a post's impressions
     *
     * @param string $postId
     *
     * @return int
     */
    public function getPostImpressions($postId)
```

Get a post's consumptions
```php
    /**
     * Get a post's consumptions
     *
     * @param string $postId
     *
     * @return int
     */
    public function getPostConsumptions($postId)
```

Get a specific insight for a post. Post insights can be found here: https://developers.facebook.com/docs/graph-api/reference/v2.2/insights#post_impressions
```php
    /**
     * Get a specific insight for a post
     *
     * @param string $insight
     * @param string $postId
     *
     * @return array
     */
    public function getPostInsight($postId, $insight)
```

Get the page's posts with calculated insights for a given period
```php
    /**
     * Get the page's posts with calculated insights for a given period
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param int $limit
     *
     * @return array
     */
    public function getPagePostsBasicInsights(\DateTime $startDate, \DateTime $endDate, $limit = null)
```