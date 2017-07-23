<?php

namespace Breaktag\LaravelUntappd;

use Breaktag\LaravelUntappd\Exceptions\LaravelUntappdException;

/**
 * PHP library for interacting with Untappd through a laravel application
 *
 * @see    https://untappd.com/api/docs
 * @author Breaktag - http://www.github.com/user/breaktag
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

	/**
	 * Constructor
	 *
	 * @throws LaravelUntappdException
	 *
	 * @param array $args - Connection parameters
	 */
	public function __construct($args = [])
	{
		if (!isset($args['client_id']) || empty($args['client_id'])) {
			throw new LaravelUntappdException('clientId not set and is required');
		}
		if (!isset($args['client_secret']) || empty($args['client_secret'])) {
			throw new LaravelUntappdException('clientSecret not set and is required');
		}

		$this->_clientId = $args['client_id'];
		$this->_clientSecret = $args['client_secret'];
		$this->_accessToken = isset($args['access_token']) ? $args['access_token'] : '';
		$this->_redirectUri = isset($args['redirect_uri']) ? $args['redirect_uri'] : '';
	}

	/**
	 * Gets the last parsed response from the service
	 *
	 * @return null|stdClass object
	 */
	public function getLastParsedResponse()
	{
		return $this->_lastParsedResponse;
	}

	/**
	 * Gets the last raw response from the service
	 *
	 * @return null|json string
	 */
	public function getLastRawResponse()
	{
		return $this->_lastRawResponse;
	}

	/**
	 * Gets the last request URI sent to the service
	 *
	 * @return null|string
	 */
	public function getLastRequestUri()
	{
		return $this->_lastRequestUri;
	}

	/**
	 * Sends a request using curl to the required URI
	 *
	 * @param string $method Untappd method to call
	 * @param array $args key value array or arguments
	 *
	 * @throws LaravelUntappdException
	 *
	 * @return stdClass object
	 */
	private function _request($method, $args, $requireAuth = false)
	{
		$this->_lastRequestUri = null;
		$this->_lastRawResponse = null;
		$this->_lastParsedResponse = null;

		if ($requireAuth) {
			if (empty($this->_accessToken)) {
				throw new LaravelUntappdException('This method requires an access token');
			}
		}

		if (!empty($this->_accessToken)) {
			$args['access_token'] = $this->_accessToken;
		} else {
			// Append the API key to the args passed in the query string
			$args['client_id'] = $this->_clientId;
			$args['client_secret'] = $this->_clientSecret;
		}

		// remove any unnecessary args from the query string
		foreach ($args as $key => $a) {
			if ($a == '') {
				unset($args[$key]);
			}
		}

		if (preg_match('/^http/i', $method)) {
			$this->_lastRequestUri = $method;
		} else {
			$this->_lastRequestUri = self::URI_BASE . '/' . $method;
		}

		$this->_lastRequestUri .= '?' . http_build_query($args);

		// Set curl options and execute the request
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->_lastRequestUri);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$this->_lastRawResponse = curl_exec($ch);

		if ($this->_lastRawResponse === false) {
			$this->_lastRawResponse = curl_error($ch);
			throw new LaravelUntappdException('CURL Error: ' . curl_error($ch));
		}

		curl_close($ch);

		// Response comes back as JSON, so we decode it into a stdClass object
		$this->_lastParsedResponse = json_decode($this->_lastRawResponse);

		// If the http_code var is not found, the response from the server was unparsable
		if (!isset($this->_lastParsedResponse->meta->code) && !isset($this->_lastParsedResponse->meta->http_code)) {
			throw new LaravelUntappdException('Error parsing response from server.');
		}

		$code = (isset($this->_lastParsedResponse->meta->http_code)) ? $this->_lastParsedResponse->meta->http_code : $this->_lastParsedResponse->meta->code;

		// Server provides error messages in http_code and error vars.  If not 200, we have an error.
		if ($code != '200') {

			$errorMessage = (isset($this->_lastParsedResponse->meta->error_detail)) ? $this->_lastParsedResponse->meta->error_detail : $this->_lastParsedResponse->meta->error;

			throw new LaravelUntappdException('Untappd Service Error ' .
				$code . ': ' .  $errorMessage);
		}

		return $this->getLastParsedResponse();
	}
}