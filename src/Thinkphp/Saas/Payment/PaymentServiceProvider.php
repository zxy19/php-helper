<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Xin\Thinkphp\Saas\Payment;

use think\Service;
use Xin\Contracts\Foundation\Payment as PaymentContract;

class PaymentServiceProvider extends Service {

	/**
	 * 启动器
	 */
	public function register() {
		$this->app->bind('payment', PaymentContract::class);
		$this->app->bind(PaymentContract::class, Payment::class);
		$this->app->bind(Payment::class, function () {
			return new Payment(
				$this->app->config->get('payment')
			);
		});
	}

}
