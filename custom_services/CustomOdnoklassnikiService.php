<?php
/**
 * CustomOdnoklassnikiService class file.
 *
 * @author Sergey Vardanyan <rakot.ss@gmail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

require_once dirname(dirname(__FILE__)).'/services/OdnoklassnikiOAuthService.php';

class CustomOdnoklassnikiService extends OdnoklassnikiOAuthService {

	protected $scope = 'VALUABLE ACCESS';
	
	protected function fetchAttributes() {
        parent::fetchAttributes();
        if ($this->scope == 'VALUABLE ACCESS')
            $this->getRealIdAndUrl();
	}

    /**
     * Avable only if VALUABLE ACCESS is on
     * you should ask for enable this scope for odnoklassniki administration
     */
    protected function getRealIdAndUrl() {
        $sig = strtolower(md5('application_key='.$this->client_public.'client_id='.$this->client_id.'fields=url_profileformat=JSONmethod=users.getInfouids='.$this->attributes['id'].md5($this->access_token.$this->client_secret)));

        $info = $this->makeRequest('http://api.odnoklassniki.ru/fb.do', array(
            'query' => array(
                'method' => 'users.getInfo',
                'sig' => $sig,
                'uids' => $this->attributes['id'],
                'fields' => 'url_profile',
                'format' => 'JSON',
                'application_key' => $this->client_public,
                'client_id' => $this->client_id,
                'access_token' => $this->access_token,
            ),
        ));

        preg_match('/\d+\/{0,1}$/',$info[0]->url_profile, $matches);
        $this->attributes['id'] = (int)$matches[0];
        $this->attributes['url'] = $info[0]->url_profile;
    }

}