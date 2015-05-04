<?php

/**
 * EveOnlineOAuthService class file
 *
 * Register application: https://developers.eveonline.com/applications
 *
 * @author Maxim Zemskov <nodge@yandex.ru>, nek <nek@srez.org>
 * @link http://github.com/Nodge/yii-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
require_once dirname(dirname(__FILE__)) . '/EOAuth2Service.php';

class EveOnlineOAuthService extends EOAuth2Service {

    /**
     * @var string
     */
    protected $name = 'eve';

    /**
     * @var string
     */
    protected $title = 'EVE Online';

    /**
     * @var string
     */
    protected $type = 'OAuth';

    /**
     * @var string
     */
    protected $client_id = '';

    /**
     * @var string
     */
    protected $client_secret = '';

    /**
     * @var string
     */
    protected $scope = '';
    
    /**
     * @var string|null
     */
    protected $uid = null;

    /**
     * @var array
     */
    protected $jsArguments = array('popup' => array('width' => 680, 'height' => 500));

    /**
     * @var array
     */
    protected $providerOptions = array(
        'authorize'     => 'https://login.eveonline.com/oauth/authorize',
        'access_token'  => 'https://login.eveonline.com/oauth/token/'
    );

    /**
     *
     */
    protected function fetchAttributes() {
        $info = (array) $this->makeRequest('https://login.eveonline.com/oauth/verify', array(
            'headers'   => array('Authorization: Bearer '.$this->access_token),
        ));
        $this->attributes['id']         = $info['CharacterID'];
        $this->attributes['name']       = $info['CharacterName'];
        $this->attributes['char_hash']  = $info['CharacterOwnerHash'];
        
        // Set full public character info
        $data = $this->getAPIData($info['CharacterID']);
        //
        $this->attributes['race']           = (string)  $data->race;
        $this->attributes['bloodline']      = (string)  $data->bloodline;
        $this->attributes['corporationID']  = (integer) $data->corporationID;
        $this->attributes['corporation']    = (string)  $data->corporation;
        $this->attributes['securityStatus'] = (double)  $data->securityStatus;
        //
        if (isset($data->allianceID)) {
            $this->attributes['allianceID'] = (integer) $data->allianceID;
            $this->attributes['alliance']   = (string)  $data->alliance;
        }
        
    }

    /**
     * @param string $code
     * @return mixed
     */
    protected function getAccessToken($code) {
        $params = array(
            'client_id'     => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $this->getState('redirect_uri'),
        );
        return $this->makeRequest($this->getTokenUrl($code), array('data' => $params));
    }

    /**
     * @param object $token
     */
    protected function saveAccessToken($token) {
        $this->setState('uid', $token->access_token);
        $this->setState('expires', time() + (isset($token->expires_in) ? $token->expires_in : 365 * 86400) - 60);
        $this->uid = $token->access_token;
        $this->access_token = $token->access_token;
    }

    /**
     * @return boolean
     */
    protected function restoreAccessToken() {
        if ($this->hasState('uid') && parent::restoreAccessToken()) {
            $this->uid = $this->getState('uid');
            return true;
        } else {
            $this->uid = null;
            return false;
        }
    }

    /**
     * @param mixed $json
     * @return array|null
     */
    protected function fetchJsonError($json) {
        if (isset($json->error)) {
            return array(
                'code'      => is_string($json->error) ? 0 : $json->error->error_code,
                'message'   => is_string($json->error) ? $json->error : $json->error->error_msg,
            );
        } else {
            return null;
        }
    }
    
    /**
     * Get data from EVE API
     * @param integer $charId
     * @return SimpleXMLElement
     */
    private function getAPIData($charId) {
        $url = 'https://api.eveonline.com/eve/CharacterInfo.xml.aspx?characterID='.(int)$charId;
        $data = file_get_contents($url);
        $xml = simplexml_load_string($data);
        return $xml->result;
    }

}
