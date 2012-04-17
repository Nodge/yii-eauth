Yii EAuth extension
===================

EAuth extension allows to authenticate users with accounts on other websites.
Supported protocols: OpenID, OAuth 1.0 and OAuth 2.0.

EAuth is a extension for provide a unified (does not depend on the selected service) method to authenticate the user. So, the extension itself does not perform login, does not register the user and does not bind the user accounts from different providers.


### Why own extension and not a third-party service?
The implementation of the authorization on your own server has several advantages:

* Full control over the process: what will be written in the authorization window, what data we get, etc.
* Ability to change the appearance of the widget.
* When logging via OAuth is possible to invoke methods on API.
* Fewer dependencies on third-party services - more reliable application.


### The extension allows you to:

* Ignore the nuances of authorization through the different types of services, use the class based adapters for each service.
* Get a unique user ID that can be used to register user in your application.
* Extend the standard authorization classes to obtain additional data about the user.
* Work with the API of social networks by extending the authorization classes.
* Set up a list of supported services, customize the appearance of the widget, use the popup window without closing your application.
	

### Extension includes:

* The component that contains utility functions.
* A widget that displays a list of services in the form of icons and allowing authorization in the popup window.
* Base classes to create your own services.
* Ready for authenticate via Google, Twitter, Facebook and other providers.


### Supported providers "out of box":

* OpenID: Google, Yandex(ru)
* OAuth: Twitter
* OAuth 2.0: Google, Facebook, VKontake(ru), Mail.ru(ru), Moi Krug(ru), Odnoklassniki(ru)


### Resources

* [Yii EAuth](https://github.com/Nodge/yii-eauth)
* [Yii Framework](http://yiiframework.com/)
* [OpenID](http://openid.net/)
* [OAuth](http://oauth.net/)
* [OAuth 2.0](http://oauth.net/2/)
* [loid extension](http://www.yiiframework.com/extension/loid)
* [EOAuth extension](http://www.yiiframework.com/extension/eoauth)


### Requirements

* Yii 1.1 or above
* PHP curl extension
* [loid extension](http://www.yiiframework.com/extension/loid)
* [EOAuth extension](http://www.yiiframework.com/extension/eoauth)


## Installation

* Install loid and EOAuth extensions
* Extract the release file under `protected/extensions`
* In your `protected/config/main.php`, add the following:

```php
<?php
...
	'import'=>array(
		'ext.eoauth.*',
		'ext.eoauth.lib.*',
		'ext.lightopenid.*',
		'ext.eauth.*',
		'ext.eauth.services.*',
	),
...
	'components'=>array(
		'loid' => array(
			'class' => 'ext.lightopenid.loid',
		),
		'eauth' => array(
			'class' => 'ext.eauth.EAuth',
			'popup' => true, // Use the popup window instead of redirecting.
			'services' => array( // You can change the providers and their classes.
				'google' => array(
					'class' => 'GoogleOpenIDService',
				),
				'yandex' => array(
					'class' => 'YandexOpenIDService',
				),
				'twitter' => array(
					// register your app here: https://dev.twitter.com/apps/new
					'class' => 'TwitterOAuthService',
					'key' => '...',
					'secret' => '...',
				),
				'google_oauth' => array(
					// register your app here: https://code.google.com/apis/console/
					'class' => 'GoogleOAuthService',
					'client_id' => '...',
					'client_secret' => '...',
					'title' => 'Google (OAuth)',
				),
				'facebook' => array(
					// register your app here: https://developers.facebook.com/apps/
					'class' => 'FacebookOAuthService',
					'client_id' => '...',
					'client_secret' => '...',
				),
				'linkedin' => array(
					// register your app here: https://www.linkedin.com/secure/developer
					'class' => 'LinkedinOAuthService',
					'key' => '...',
					'secret' => '...',
				),
				'github' => array(
					// register your app here: https://github.com/settings/applications
					'class' => 'GitHubOAuthService',
					'client_id' => '...',
					'client_secret' => '...',
				),
				'vkontakte' => array(
					// register your app here: http://vkontakte.ru/editapp?act=create&site=1
					'class' => 'VKontakteOAuthService',
					'client_id' => '...',
					'client_secret' => '...',
				),
				'mailru' => array(
					// register your app here: http://api.mail.ru/sites/my/add
					'class' => 'MailruOAuthService',
					'client_id' => '...',
					'client_secret' => '...',
				),
				'moikrug' => array(
					// register your app here: https://oauth.yandex.ru/client/my
					'class' => 'MoikrugOAuthService',
					'client_id' => '...',
					'client_secret' => '...',
				),
				'odnoklassniki' => array(
					// register your app here: http://www.odnoklassniki.ru/dk?st.cmd=appsInfoMyDevList&st._aid=Apps_Info_MyDev
					'class' => 'OdnoklassnikiOAuthService',
					'client_id' => '...',
					'client_public' => '...',
					'client_secret' => '...',
					'title' => 'Odnokl.',
				),
			),
		),
		...
	),
...
```


## Usage

#### The action

```php
<?php
...
	public function actionLogin() {
		$service = Yii::app()->request->getQuery('service');
		if (isset($service)) {
			$authIdentity = Yii::app()->eauth->getIdentity($service);
			$authIdentity->redirectUrl = Yii::app()->user->returnUrl;
			$authIdentity->cancelUrl = $this->createAbsoluteUrl('site/login');
			
			if ($authIdentity->authenticate()) {
				$identity = new EAuthUserIdentity($authIdentity);
				
				// successful authentication
				if ($identity->authenticate()) {
					Yii::app()->user->login($identity);
					
					// special redirect with closing popup window
					$authIdentity->redirect();
				}
				else {
					// close popup window and redirect to cancelUrl
					$authIdentity->cancel();
				}
			}
			
			// Something went wrong, redirect to login page
			$this->redirect(array('site/login'));
		}
		
		// default authorization code through login/password ..
	}
```

#### The view

```php
<h2>Do you already have an account on one of these sites? Click the logo to log in with it here:</h2>
<?php 
	$this->widget('ext.eauth.EAuthWidget', array('action' => 'site/login'));
?>
```

#### Getting more user data (optional)

To receive all the necessary data to your application, you can override the base class of any provider.
Base classes are stored in `protected/extensions/eauth/services/`.
Examples of extended classes can be found in `protected/extensions/eauth/custom_services/`.

After overriding the base class, you need to modify your configuration file to set new name of the class.
Also you may need to override the `EAuthUserIdentity` class to store additional data.

#### Translations (optional)

* Copy the file `/protected/extensions/eauth/messages/[lang]/eauth.php` to `/protected/messages/[lang]/eauth.php` to translate the EAuth extension into other languages.
* To add a new language, you can use the blank file `/protected/extensions/eauth/messages/blank/eauth.php`.

## License

Some time ago I developed this extension for [LiStick.ru](http://listick.ru) and I still support the extension.

The extension was released under the [New BSD License](http://www.opensource.org/licenses/bsd-license.php), so you'll find the latest version on [GitHub](https://github.com/Nodge/yii-eauth).
