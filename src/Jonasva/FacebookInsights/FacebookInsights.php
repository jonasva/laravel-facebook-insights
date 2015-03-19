<?php namespace Jonasva\FacebookInsights;

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\FacebookSDKException;

use Illuminate\Config\Repository;
use Cache;

class FacebookInsights
{

    /**
     * Facebook session.
     *
     * @var \Facebook\FacebookSession
     */
    protected $session;

    /**
     * Illuminate config repository instance.
     *
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * Maximum number of days allowed in one query to facebook
     *
     * @var int
     */
    protected $maxDaysPerQuery = 92;

    /**
     * Create a new FacebookInsights instance.
     *
     * @param  \Illuminate\Config\Repository  $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;

        FacebookSession::setDefaultApplication($this->config->get('facebook-insights::app-id'), $this->config->get('facebook-insights::app-secret'));

        $this->session = new FacebookSession($this->config->get('facebook-insights::access-token'));
    }

    /**
     * Get the page impressions per day for a given period
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return int
     */
    public function getPageImpressionsPerDay(\DateTime $startDate, \DateTime $endDate)
    {
        $params = ['period' => 'day'];

        return $this->getDataForDateRange($startDate, $endDate, '/insights/page_impressions', $params);
    }

    /**
     * Get the total number of page impressions for a given period
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return int
     */
    public function getTotalPageImpressions(\DateTime $startDate, \DateTime $endDate)
    {
        $rawData = $this->getPageImpressionsPerDay($startDate, $endDate);

        return $this->calculateTotal($rawData);
    }

    /**
     * Get the page consumptions per day for a given period
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return int
     */
    public function getPageConsumptionsPerDay(\DateTime $startDate, \DateTime $endDate)
    {
        $params = ['period' => 'day'];

        return $this->getDataForDateRange($startDate, $endDate, '/insights/page_consumptions', $params);
    }

    /**
     * Get the total number of page consumptions for a given period
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return int
     */
    public function getTotalPageConsumptions(\DateTime $startDate, \DateTime $endDate)
    {
        $rawData = $this->getPageConsumptionsPerDay($startDate, $endDate);

        return $this->calculateTotal($rawData);
    }

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
    {
        $params = ['period' => $period];

        return $this->getDataForDateRange($startDate, $endDate, '/insights/' . $insight, $params, null, false);
    }

    /**
     * Get the page's posts for a given period
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param int $limit
     *
     * @return array
     */
    public function getPagePosts(\DateTime $startDate, \DateTime $endDate, $limit = 8)
    {
        $params = ['limit' => $limit];

        return $this->getDataForDateRange($startDate, $endDate, '/posts', $params, null, false);
    }

    /**
     * Get a post's impressions
     *
     * @param string $postId
     *
     * @return int
     */
    public function getPostImpressions($postId)
    {
        return $this->getPostInsight($postId, 'post_impressions')[0]->values[0]->value;
    }

    /**
     * Get a post's consumptions
     *
     * @param string $postId
     *
     * @return int
     */
    public function getPostConsumptions($postId)
    {
        return $this->getPostInsight($postId, 'post_consumptions')[0]->values[0]->value;
    }

    /**
     * Get a specific insight for a post
     *
     * @param string $insight
     * @param string $postId
     *
     * @return array
     */
    public function getPostInsight($postId, $insight)
    {
        $queryResult = $this->performGraphCall('/insights/' . $insight, [], $postId);

        return $queryResult->getProperty('data')->asArray();
    }

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
    {
        if (count($params) > 0) {
            $i = 0;

            foreach($params as $key => $param) {
                if ($i == 0) {
                    $query .= '?' . $key . '=' . $param;
                }
                else {
                    $query .= '&' . $key . '=' . $param;
                }

                $i++;
            }
        }

        $object ? $object = '/' . $object : $object = '/' . $this->config->get('facebook-insights::page-id');

        $cacheName = $this->determineCacheName([$query, $method, $object]);

        if ($this->useCache() && Cache::has($cacheName)) {
            $response = Cache::get($cacheName);
        }
        else {
            $response = (new FacebookRequest(
                $this->session, $method, $object . $query
            ))->execute()->getGraphObject();

            if ($this->useCache()) {
                Cache::put($cacheName, $response, $this->config->get('facebook-insights::cache-lifetime'));
            }
        }

        return $response;
    }

    /**
     * get the values for an API call between a given date range
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param string $query
     * @param array $params
     * $param bool $values (return an array with values or not)
     *
     * @return array
     */
    public function getDataForDateRange(\DateTime $startDate, \DateTime $endDate, $query, $params = [], $object = null, $values = true)
    {
        $diff = $startDate->diff($endDate)->days;

        $noQueries = ceil($diff / $this->maxDaysPerQuery);

        if ($noQueries > $this->config->get('facebook-insights::api-call-max')) {
            throw new FacebookSDKException('API calls needed for this query exceed "api-calls-max" set in the config file.');
        }

        $data = [];

        if ($noQueries > 1) {
            $leftOver = $diff % $this->maxDaysPerQuery;

            for ($i = 1; $i <= $noQueries; $i++) {
                if ($i == $noQueries && $leftOver > 0) {
                    $intervalStartDate = $startDate;
                }
                else {
                    $intervalStartDate = clone $endDate;
                    $intervalStartDate->sub(new \DateInterval('P' . ($this->maxDaysPerQuery * $i) . 'D'));
                }

                $intervalEndDate = clone $endDate;
                $intervalEndDate->sub(new \DateInterval('P' . ($this->maxDaysPerQuery * ($i - 1)) . 'D'));

                $params['since'] = strtotime($intervalStartDate->format('Y-m-d'));
                $params['until'] = strtotime($intervalEndDate->format('Y-m-d'));

                $queryResult = $this->performGraphCall($query, $params, $object)->getProperty('data')->asArray();

                !$values ?: $queryResult = $queryResult[0]->values;

                $data = array_merge($data, $queryResult);
            }
        }
        else {
            $params['since'] = strtotime($startDate->format('Y-m-d'));
            $params['until'] = strtotime($endDate->format('Y-m-d'));

            $queryResult = $this->performGraphCall($query, $params, $object)->getProperty('data')->asArray();

            !$values ?: $queryResult = $queryResult[0]->values;

            $data = $queryResult;
        }

        return $data;
    }

    /**
     * Calculate totals from an array of raw data by period
     *
     * @param array $rawData
     *
     * @return int
     */
    private function calculateTotal(array $rawData)
    {
        $total = 0;

        foreach ($rawData as $data) {
            $total += $data->value;
        }

        return $total;
    }

    /**
     * Determine the cache name for the set of query properties given
     *
     * @param array $properties
     * @return string
     */
    private function determineCacheName(array $properties)
    {
        return 'jonasva.facebook-insights.' . md5(serialize($properties));
    }

    /**
     * Determine whether or not to cache API responses
     *
     * @return bool
     */
    private function useCache()
    {
        return $this->config->get('facebook-insights::cache-lifetime') > 0;
    }

} 
