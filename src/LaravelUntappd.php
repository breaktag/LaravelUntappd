<?php

namespace Breaktag\LaravelUntappd;

/**
 * PHP library for interacting with Untappd through a laravel application
 *
 * @see    https://untappd.com/api/docs
 * @author Breaktag - http://www.github.com/user/breaktag
 *
 */
class LaravelUntappd
{
/**
     * Base URI for the Untappd service
     *
     * @var string
     */
    const BASE_URL = 'https://api.untappd.com/v4';

    /**
     * Client ID
     *
     * @var string
     */
    protected $_clientId = '';

    /**
     * Client Secret
     *
     * @var string
     */
    protected $_clientSecret = '';

    /**
     * Access token
     *
     * @var string
     */
    protected $_accessToken = '';

    /**
     * Redirect URI
     *
     * @var string
     */
    protected $_redirectUri = '';

    /**
     * Last response from the server
     *
     * @var stdClass
     */
    protected $_lastParsedResponse = null;

    /**
     * Last raw response from the server
     *
     * @var string
     */
    protected $_lastRawResponse = null;

    /**
     * Last requested URI
     *
     * @var string
     */
    protected $_lastRequestUri = null;

    public function __construct()
    {

    }

	private function _request()
	{
		return true;
	}
}