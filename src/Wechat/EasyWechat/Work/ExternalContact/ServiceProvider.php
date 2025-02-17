<?php

namespace Xin\Wechat\EasyWechat\Work\ExternalContact;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class ServiceProvider.
 *
 * @author mingyoung <mingyoungcheung@gmail.com>
 */
class ServiceProvider implements ServiceProviderInterface
{

	/**
	 * {@inheritdoc}.
	 */
	public function register(Container $app)
	{
		$app['contact_way'] = function ($app) {
			return new ContactWayClient($app);
		};
	}

}
