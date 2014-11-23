<?php
/**
 * WargamingOpenIDService class file
 *
 * @author Maxim Zemskov <nodge@yandex.ru>, nek <nek@srez.org>
 * @link http://github.com/Nodge/yii-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

require_once dirname(dirname(__FILE__)) . '/EOpenIDService.php';

class WargamingOpenIDService extends EOpenIDService {

    protected $name = 'wargaming';

    protected $title = 'Wargaming';

    protected $type = 'OpenID';

    protected $jsArguments = array('popup' => array('width' => 430, 'height' => 830));

    protected $url = 'http://ru.wargaming.net/id/';

    protected $requiredAttributes = array(
        'name' => array('nickname', 'namePerson/friendly'),
    );

}