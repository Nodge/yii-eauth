Yii EAuth extension
===================


Расширение EAuth позволяет добавить на сайт авторизацию с аккаунтов на других сайтах.
Поддерживаемые протоколы: OpenID, OAuth 1.0 и OAuth 2.0.

Задачей расширения является предоставление единого (не зависящего от выбранного сервиса) метода авторизации пользователя. Таким образом, расширение самостоятельно не выполняет вход, не регистрирует пользователей и не связывает аккаунты пользователей от разных провайдеров.

### Почему собственное расширение, а не сторонний сервис?
Реализация авторизации на стороне собственного сервиса имеет ряд преимуществ:

* Полный контроль над процессом авторизации: что будет написано в окне авторизации провайдера, какие данные мы получим и т.д.
* Возможность изменять внешний вид виджета авторизации в соответствии с дизайном сайта.
* При авторизации через OAuth есть возможность вызывать методы API, если их предоставляет провайдер.
* Меньше зависимостей от сторонних сервисов – больше надежность.

### Расширение позволяет:

* Абстрагироваться от тонкостей авторизации через различные типы сервисов, использовать классы-адаптеры для каждого сервиса.
* Получить уникальный идентификатор пользователя, который можно использовать для регистрации в вашем приложении.
* Расширять стандартные классы авторизации для получения дополнительных данных о пользователе.
* Работать с API социальных сетей путем расширения класса авторизации необходимого сервиса.
* Настраивать список поддерживаемых сайтом сервисов, переопределять внешний вид виджета авторизации, использовать popup окно для авторизации без закрытия вашего приложения.
	
### Расширение содержит:

* Компонент, содержащий вспомогательные функции.
* Виджет, выводящий список сервисов в виде иконок и позволяющий проводить авторизацию в popup окне.
* Базовые классы для самостоятельного добавления новых сервисов.
* Готовые классы для авторизации через Google, Twitter, Facebook и других провайдеров.

### Поддерживаемые провайдеры "из коробки":

* OpenID: Google, Яндекс
* OAuth: Twitter, LinkedIn
* OAuth 2.0: Google, Facebook, ВКонтакте, Mail.ru, GitHub, Мой круг, Одноклассники


### Ссылки

* [Yii EAuth](https://github.com/Nodge/yii-eauth)
* [Yii Framework](http://yiiframework.com/)
* [OpenID](http://openid.net/)
* [OAuth](http://oauth.net/)
* [OAuth 2.0](http://oauth.net/2/)
* [loid extension](http://www.yiiframework.com/extension/loid)
* [EOAuth extension](http://www.yiiframework.com/extension/eoauth)


### Системные требования

* Yii 1.1 or above
* PHP curl extension
* [loid extension](http://www.yiiframework.com/extension/loid)
* [EOAuth extension](http://www.yiiframework.com/extension/eoauth)


## Установка

* Установить расширения loid и EOAuth.
* Распаковать расширение EAuth в директорию `protected/extensions`.
* Добавить следующие строки в файл конфигурации `protected/config/main.php`:

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
			'popup' => true, // Использовать всплывающее окно вместо перенаправления на сайт провайдера
			'services' => array( // Вы можете настроить список провайдеров и переопределить их классы
				'google' => array(
					'class' => 'GoogleOpenIDService',
				),
				'yandex' => array(
					'class' => 'YandexOpenIDService',
				),
				'twitter' => array(
					// регистрация приложения: https://dev.twitter.com/apps/new
					'class' => 'TwitterOAuthService',
					'key' => '...',
					'secret' => '...',
				),
				'google_oauth' => array(
					// регистрация приложения: https://code.google.com/apis/console/
					'class' => 'GoogleOAuthService',
					'client_id' => '...',
					'client_secret' => '...',
					'title' => 'Google (OAuth)',
				),
				'facebook' => array(
					// регистрация приложения: https://developers.facebook.com/apps/
					'class' => 'FacebookOAuthService',
					'client_id' => '...',
					'client_secret' => '...',
				),
				'linkedin' => array(
					// регистрация приложения: https://www.linkedin.com/secure/developer
					'class' => 'LinkedinOAuthService',
					'key' => '...',
					'secret' => '...',
				),
				'github' => array(
					// регистрация приложения: https://github.com/settings/applications
					'class' => 'GitHubOAuthService',
					'client_id' => '...',
					'client_secret' => '...',
				),
				'vkontakte' => array(
					// регистрация приложения: http://vkontakte.ru/editapp?act=create&site=1
					'class' => 'VKontakteOAuthService',
					'client_id' => '...',
					'client_secret' => '...',
				),
				'mailru' => array(
					// регистрация приложения: http://api.mail.ru/sites/my/add
					'class' => 'MailruOAuthService',
					'client_id' => '...',
					'client_secret' => '...',
				),
				'moikrug' => array(
					// регистрация приложения: https://oauth.yandex.ru/client/my
					'class' => 'MoikrugOAuthService',
					'client_id' => '...',
					'client_secret' => '...',
				),
				'odnoklassniki' => array(
					// регистрация приложения: http://www.odnoklassniki.ru/dk?st.cmd=appsInfoMyDevList&st._aid=Apps_Info_MyDev
					'class' => 'OdnoklassnikiOAuthService',
					'client_id' => '...',
					'client_public' => '...',
					'client_secret' => '...',
					'title' => 'Однокл.',
				),
			),
		),
		...
	),
...
```


## Использование

#### Действие в контроллере

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
				
				// успешная авторизация
				if ($identity->authenticate()) {
					Yii::app()->user->login($identity);
					
					// специальное перенаправления для корректного закрытия всплывающего окна
					$authIdentity->redirect();
				}
				else {
					// закрытие всплывающего окна и перенаправление на cancelUrl
					$authIdentity->cancel();
				}
			}
			
			// авторизация не удалась, перенаправляем на страницу входа
			$this->redirect(array('site/login'));
		}
		
		// далее стандартный код авторизации по логину/паролю...
	}
```

#### Представление

```php
<h2>Нажмите на иконку для входа через один из сайтов:</h2>
<?php 
	$this->widget('ext.eauth.EAuthWidget', array('action' => 'site/login'));
?>
```

#### Получение дополнительных данных (не обязательно)

Чтобы получать все необходимые Вашему приложению данные, Вы можете переопределить базовый класс любого провайдера. 
Базовые классы хранятся в `protected/extensions/eauth/services/`.
Примеры расширенных классов можно посмотреть в `protected/extensions/eauth/custom_services/`.

После переопределения базового класса, необходимо поправить Ваш файл конфигурации, указав новое имя класса.
Возможно, Вам понадобится переопределить `EAuthUserIdentity` для сохранения дополнительных данных.

#### Перевод сообщений (не обязательно)

* Для перевода EAuth на другие языки скопируйте файл `/protected/extensions/eauth/messages/[lang]/eauth.php` в `/protected/messages/[lang]/eauth.php`, где [lang] - код необходимого языка.
* Если в `/protected/extensions/eauth/messages/` нет нужного Вам языка, можно воспользоваться файлом `/protected/extensions/eauth/messages/blank/eauth.php` для перевода расширения на другие языки.

## Лицензия

Некоторое время назад я разработал данное расширение для своего проекта [LiStick.ru](http://listick.ru). На данный момент я продолжаю поддерживать расширение.

Расширение предоставляется под лицензией [New BSD License](http://www.opensource.org/licenses/bsd-license.php), так что последнюю версию можно найти на [GitHub](https://github.com/Nodge/yii-eauth).
