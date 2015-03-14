<?php namespace Jonasva\FacebookInsights;

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;

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
     * Create a new FacebookInsights instance.
     *
     * @param  \Illuminate\Config\Repository  $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;

        FacebookSession::setDefaultApplication($this->config->get('facebook-insights.app-id'), $this->config->get('facebook-insights.app-secret'));

        $this->session = new FacebookSession($this->config->get('facebook-insights.access-token'));
    }
} 