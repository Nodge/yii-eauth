<?php
class CustomFacebookService extends FacebookOAuthService {
	/**
	 * https://developers.facebook.com/docs/authentication/permissions/
	 */
	protected $scope = 'email,user_birthday,user_hometown,user_location';

	/**
	 * http://developers.facebook.com/docs/reference/api/user/
	 *
	 * @see FacebookOAuthService::fetchAttributes()
	 */
	protected function fetchAttributes() {
		$this->attributes = (array)$this->makeSignedRequest('https://graph.facebook.com/v2.8/me', array(
			'query' => array(
				'fields' => join(',', array(
					'id',
					'name',
					'link',
					'email',
					'verified',
					'first_name',
					'last_name',
					'gender',
					'birthday',
					'hometown',
					'location',
					'locale',
					'timezone',
					'updated_time',
				))
			)
		));
	}
}
