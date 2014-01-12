<?php
/**
 * DropboxOAuthService class file.
 *
 * Register application: https://www.dropbox.com/developers/apps/create
 *
 * @author Alexander Kononenko <alex.kononenko90@me.com>
 * @link http://github.com/Nodge/yii-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

require_once dirname(dirname(__FILE__)) . '/EOAuth2Service.php';

/**
 * Dropbox provider class.
 *
 * @package application.extensions.eauth.services
 */
class DropboxOAuthService extends EOAuth2Service {

	protected $name = 'dropbox';
	protected $title = 'Dropbox';
	protected $type = 'OAuth';
	protected $jsArguments = array('popup' => array('width' => 1010, 'height' => 560));

	protected $client_id = '';
	protected $client_secret = '';
	protected $scope = '';
	protected $providerOptions = array(
		'authorize' => 'https://www.dropbox.com/1/oauth2/authorize',
		'access_token' => 'https://api.dropbox.com/1/oauth2/token',
	);

	protected $uid = null;

	protected function fetchAttributes() {
		$info = (array)$this->makeSignedRequest('https://api.dropbox.com/1/account/info');
		$this->attributes['id'] = $info['uid'];
		$this->attributes['name'] = $info['display_name'];
	}

	protected function getAccessToken($code) {
		$params = array(
			'client_id' => $this->client_id,
			'client_secret' => $this->client_secret,
			'grant_type' => 'authorization_code',
			'code' => $code,
			'redirect_uri' => $this->getState('redirect_uri'),
		);
		return $this->makeRequest($this->getTokenUrl($code), array('data' => $params));
	}

	/**
	 * Save access token to the session.
	 *
	 * @param stdClass $token access token object.
	 */
	protected function saveAccessToken($token) {
		$this->setState('auth_token', $token->access_token);
		$this->setState('uid', $token->uid);
		$this->setState('expires', time() + (isset($token->expires_in) ? $token->expires_in : 365 * 86400) - 60);
		$this->uid = $token->uid;
		$this->access_token = $token->access_token;
	}

	/**
	 * Restore access token from the session.
	 *
	 * @return boolean whether the access token was successfuly restored.
	 */
	protected function restoreAccessToken() {
		if ($this->hasState('uid') && parent::restoreAccessToken()) {
			$this->uid = $this->getState('uid');
			return true;
		}
		else {
			$this->uid = null;
			return false;
		}
	}

	/**
	 * Returns the error info from json.
	 *
	 * @param stdClass $json the json response.
	 * @return array the error array with 2 keys: code and message. Should be null if no errors.
	 */
	protected function fetchJsonError($json) {
		if (isset($json->error)) {
			return array(
				'code' => is_string($json->error) ? 0 : $json->error->error_code,
				'message' => is_string($json->error) ? $json->error : $json->error->error_msg,
			);
		}
		else {
			return null;
		}
	}
}