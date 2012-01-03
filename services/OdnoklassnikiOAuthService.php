<?php
/**
 * OdnoklassnikiOAuthService class file.
 *
 * Register application: http://www.odnoklassniki.ru/dk?st.cmd=appsInfoMyDevList&st._aid=Apps_Info_MyDev
 * Note: Enabling this service a little more difficult because of the authorization policy of the service.
 * 
 * @author Sergey Vardanyan <rakot.ss@gmail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

require_once dirname(dirname(__FILE__)).'/EOAuth2Service.php';

/**
 * Odnoklassniki.Ru provider class.
 * @package application.extensions.eauth.services
 */
class OdnoklassnikiOAuthService extends EOAuth2Service {

	protected $name = 'odnoklassniki';
	protected $title = 'Одноклассники';
	protected $type = 'OAuth';
	protected $jsArguments = array('popup' => array('width' => 680, 'height' => 500));

	protected $client_id = '';
	protected $client_secret = '';
	protected $client_public = '';
	protected $scope = '';
	protected $providerOptions = array(
		'authorize' => 'http://www.odnoklassniki.ru/oauth/authorize',
		'access_token' => 'http://api.odnoklassniki.ru/oauth/token.do',
	);

	protected function fetchAttributes() {
        $sig = strtolower(md5('application_key='.$this->client_public.'client_id='.$this->client_id.'format=JSONmethod=users.getCurrentUser'.md5($this->access_token.$this->client_secret)));
		
		$info = $this->makeRequest('http://api.odnoklassniki.ru/fb.do', array(
			'query' => array(
				'method' => 'users.getCurrentUser',
				'sig' => $sig,
                'format' => 'JSON',
                'application_key' => $this->client_public,
				'client_id' => $this->client_id,
                'access_token' => $this->access_token,
			),
		));

		$this->attributes['id'] = $info->uid;
		$this->attributes['name'] = $info->first_name.' '.$info->last_name;
	}

	protected function getTokenUrl($code) {
		return $this->providerOptions['access_token'];
	}

	protected function getAccessToken($code) {
		$params = array(
			'client_id' => $this->client_id,
			'client_secret' => $this->client_secret,
			'grant_type' => 'authorization_code',
			'code' => $code,
			'redirect_uri' => $this->getState('redirect_uri'),
		);
        $url = $this->getTokenUrl($code).'?client_id='.$this->client_id.'&client_secret='.$this->client_secret.'&redirect_uri='.urlencode($this->getState('redirect_uri')).'&code='.$code.'&grant_type=authorization_code';
		$result = $this->makeRequest($url, array('data' => $params));
		return $result->access_token;
	}

	protected function getCodeUrl($redirect_uri) {
		$this->setState('redirect_uri', $redirect_uri);
		$url = parent::getCodeUrl($redirect_uri);
		if (isset($_GET['js']))
			$url .= '&display=popup';
		return $url;
	}

	/**
	 * Returns the error info from json.
	 * @param stdClass $json the json response.
	 * @return array the error array with 2 keys: code and message. Should be null if no errors.
	 */
	protected function fetchJsonError($json) {
		if (isset($json->error)) {
			return array(
				'code' => $json->error_code,
				'message' => $json->error_description,
			);
		}
		else
			return null;
	}
}