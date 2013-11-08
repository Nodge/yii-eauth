<?php
/**
 * EOAuthService class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

require_once 'EAuthServiceBase.php';

/**
 * EOAuthService is a base class for all OAuth providers.
 *
 * @package application.extensions.eauth
 */
abstract class EOAuthService extends EAuthServiceBase implements IAuthService {

	/**
	 * @var EOAuthUserIdentity the OAuth library instance.
	 */
	private $auth;


	/**
	 * @var string OAuth2 client id.
	 */
	protected $key;

	/**
	 * @var string OAuth2 client secret key.
	 */
	protected $secret;

	/**
	 * @var string OAuth scopes.
	 */
	protected $scope = '';

	/**
	 * @var array Provider options. Must contain the keys: request, authorize, access.
	 */
	protected $providerOptions = array(
		'request' => '',
		'authorize' => '',
		'access' => '',
	);


	/**
	 * Initialize the component.
	 *
	 * @param EAuth $component the component instance.
	 * @param array $options properties initialization.
	 */
	public function init($component, $options = array()) {
		parent::init($component, $options);

		$this->auth = new EOAuthUserIdentity(array(
			'scope' => $this->scope,
			'key' => $this->key,
			'secret' => $this->secret,
			'provider' => $this->providerOptions,
		));

		// Try to restore access token and customer from session.
		$this->restoreCredentials();
	}

	/**
	 * Authenticate the user.
	 *
	 * @return boolean whether user was successfuly authenticated.
	 * @throws EAuthException
	 */
	public function authenticate() {
		$this->authenticated = $this->auth->authenticate();
		$error = $this->auth->getError();
		if (isset($error)) {
			throw new EAuthException($error);
		}

		// In case of successful authentication save access token and
		// customer to session.
		if ($this->authenticated) {
			$this->saveCredentials();
		}

		return $this->getIsAuthenticated();
	}

	/**
	 * Returns the OAuth consumer.
	 *
	 * @return object the consumer.
	 */
	protected function getConsumer() {
		return $this->auth->getProvider()->consumer;
	}

	/**
	 * Returns the OAuth access token.
	 *
	 * @return string the token.
	 */
	protected function getAccessToken() {
		return $this->auth->getProvider()->token;
	}

	/**
	 * Save access credentials to the session.
	 */
	protected function saveCredentials() {

		$this->setState('auth_token', $this->getAccessToken());
		$this->setState('auth_consumer', $this->getConsumer());
	}

	/**
	 * Restore access credentials from the session.
	 *
	 * @return boolean whether the access credentials were successfully restored.
	 */
	protected function restoreCredentials() {
		if (!$this->authenticated) {
			if ($this->hasState('auth_consumer') && $this->hasState('auth_token')) {
				$this->auth->getProvider()->consumer = $this->getState('auth_consumer');
				$this->auth->getProvider()->token = $this->getState('auth_token');
				$this->authenticated = true;
			}
			else {
				$this->authenticated = false;
			}
		}

		return $this->authenticated;
	}

	/**
	 * Initializes a new session and return a cURL handle.
	 *
	 * @param string $url url to request.
	 * @param array $options HTTP request options. Keys: query, data, referer.
	 * @param boolean $parseJson Whether to parse response in json format.
	 * @return cURL handle.
	 */
	protected function initRequest($url, $options = array()) {
		$ch = parent::initRequest($url, $options);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
		return $ch;
	}

	/**
	 * Returns the protected resource.
	 *
	 * @param string $url url to request.
	 * @param array $options HTTP request options. Keys: query, data, referer.
	 * @param boolean $parseJson Whether to parse response in json format.
	 * @return string the response.
	 * @see makeRequest
	 */
	public function makeSignedRequest($url, $options = array(), $parseJson = true) {
		if (!$this->getIsAuthenticated()) {
			throw new CHttpException(401, Yii::t('eauth', 'Unable to complete the request because the user was not authenticated.'));
		}

		$consumer = $this->getConsumer();
		$signatureMethod = new OAuthSignatureMethod_HMAC_SHA1();
		$token = $this->getAccessToken();

		$query = null;
		if (isset($options['query'])) {
			$query = $options['query'];
			unset($options['query']);
		}

		$request = OAuthRequest::from_consumer_and_token($consumer, $token, isset($options['data']) ? 'POST' : 'GET', $url, $query);
		$request->sign_request($signatureMethod, $consumer, $token);

		return $this->makeRequest($request->to_url(), $options, $parseJson);
	}
}