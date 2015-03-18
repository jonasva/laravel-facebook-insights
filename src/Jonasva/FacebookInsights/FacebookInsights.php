<?php namespace Jonasva\FacebookInsights;

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\FacebookSDKException;

use Illuminate\Config\Repository;

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
     * @var integer
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
     * Get the total number of page impressions for a given period
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return int
     */
    public function getTotalPageImpressions($startDate, $endDate)
    {
        $rawData = $this->getValuesForDateRange($startDate, $endDate, '/page_impressions');

        $totalImpressions = 0;

        foreach ($rawData as $data) {
            $totalImpressions += $data->value;
        }

        return $totalImpressions;
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
    public function performGraphCall($query, $params = [], $method = 'GET', $object = null)
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

        $object ?: $object = '/' . $this->config->get('facebook-insights::page-id') . '/insights';

        return (new FacebookRequest(
            $this->session, $method, $object . $query
        ))->execute()->getGraphObject();
    }

    /**
     * get the values for an API call between a given date range
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param string $query
     *
     * @return array
     */
    private function getValuesForDateRange($startDate, $endDate, $query)
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

                $params = [
                    'since' => strtotime($intervalStartDate->format('Y-m-d')),
                    'until' => strtotime($intervalEndDate->format('Y-m-d')),
                ];


                $queryResult = $this->performGraphCall($query, $params);

                $data = array_merge($data, $queryResult->getProperty('data')->asArray()[0]->values);
            }
        }
        else {
            $params = [
                'since' => strtotime($startDate->format('Y-m-d')),
                'until' => strtotime($endDate->format('Y-m-d')),
            ];

            $queryResult = $this->performGraphCall($query, $params);

            $data = $queryResult->getProperty('data')->asArray()[0]->values;
        }

        return $data;
    }

} 
