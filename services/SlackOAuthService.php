<?php
/**
 * SlackOAuthService class file.
 *
 * Register application: https://api.slack.com/applications/new
 *
 * @author Sergey Zharinov <sergio.zharinov@gmail.com>
 * @link https://github.com/Nodge/yii-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

require_once dirname(dirname(__FILE__)) . '/EOAuth2Service.php';

/**
 * Slack provider class.
 *
 * @package application.extensions.eauth.services
 */
class SlackOAuthService extends EOAuth2Service {

	protected $name = 'slack';
	protected $title = 'Slack';
	protected $type = 'OAuth';
	protected $jsArguments = array('popup' => array('width' => 900, 'height' => 450));

	protected $client_id = '';
	protected $client_secret = '';
	protected $scope = 'read';
	protected $team = '';
	protected $providerOptions = array(
		'authorize' => 'https://slack.com/oauth/authorize',
		'access_token' => 'https://slack.com/api/oauth.access',
	);
	
	/** @see https://api.slack.com/methods/oauth.access */
	private $errorMessages = array(
		'invalid_client_id' => 'Value passed for client_id was invalid',
		'bad_client_secret' => 'Value passed for client_secret was invalid',
		'invalid_code'      => 'Value passed for code was invalid',
		'bad_redirect_uri'  => 'Value passed for redirect_uri did not match the redirect_uri in the original request',
	);

	protected $errorAccessDeniedCode = 'access_denied';

	protected function fetchAttributes() {
		$testInfo = (object)$this->makeSignedRequest('https://slack.com/api/auth.test');
		$this->attributes['id'] = $testInfo->user_id;

		$fullInfo = $this->makeRequest('https://slack.com/api/users.info', array('data' => array(
			'token' => $this->access_token,
			'user'  => $testInfo->user_id,
		)), true);
		$this->attributes['name'] = $fullInfo->user->real_name;
	}

	protected function getAccessToken($code) {
		$params = array(
			'client_id' => $this->client_id,
			'client_secret' => $this->client_secret,
			'code' => $code,
			'redirect_uri' => $this->getState('redirect_uri'),
		);

		$response = $this->makeRequest($this->getTokenUrl($code), array('data' => $params), true);
		return $response->access_token;
	}

	/**
	 * Returns the error info from json.
	 *
	 * @param stdClass $json the json response.
	 * @return array the error array with 2 keys: code and message. Should be null if no errors.
	 */
	protected function fetchJsonError($json) {
		if (!$json->ok) {
			if (isset($this->errorMessages[$json->error])) {
				$errorMessage = $this->errorMessages[$json->error];
			} else {
				$errorMessage = 'Unknown error';
			}
			return array(
				'code' => $json->error,
				'message' => $errorMessage,
			);
		}
		else {
			return null;
		}
	}

	/**
	 * Add User-Agent header
	 *
	 * @param string $url
	 * @param array $options
	 * @return cURL
	 */
	protected function initRequest($url, $options = array()) {
		$ch = parent::initRequest($url, $options);
		curl_setopt($ch, CURLOPT_USERAGENT, 'yii-eauth extension');
		return $ch;
	}

	/**
	 * Returns the url to request to get OAuth2 code.
	 *
	 * @param string $redirect_uri url to redirect after user confirmation.
	 * @return string url to request.
	 */
	protected function getCodeUrl($redirect_uri) {
		$this->setState('redirect_uri', $redirect_uri);
		return $this->providerOptions['authorize'] . '?client_id=' . $this->client_id . '&redirect_uri=' . urlencode($redirect_uri) . '&scope=' . $this->scope . '&team=' . $this->team . '&state=' . md5(time());
	}

	/**
	 * Returns fields required for signed request.
	 * Used in {@link makeSignedRequest}.
	 *
	 * @return array
	 */
	protected function getSignedRequestFields()
	{
		return array(
			'token' => $this->access_token,
		);
	}
}
