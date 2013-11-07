<?php
/**
 * SteamOpenIDService class file.
 *
 * @author Dmitry Ananichev <a@qozz.ru>
 * @link http://github.com/Nodge/yii-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

require_once dirname(dirname(__FILE__)) . '/EOpenIDService.php';

/**
 * Steam provider class.
 *
 * @package application.extensions.eauth.services
 */
class SteamOpenIDService extends EOpenIDService {

    protected $name = 'steam';
    protected $title = 'Steam';
    protected $type = 'OpenID';
    protected $jsArguments = array('popup' => array('width' => 900, 'height' => 550));

    protected $url = 'http://steamcommunity.com/openid/';

}