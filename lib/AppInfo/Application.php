<?php

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * Nextcloud - Two-factor SMS
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\TwoFactorSms\AppInfo;

use OCA\TwoFactorSms\Service\ISmsService;
use OCA\TwoFactorSms\Service\SmsProvider\Puzzel;
use OCA\TwoFactorSms\Service\SmsProvider\ViaNett;
use OCA\TwoFactorSms\Service\SmsProvider\WebSmsDe;
use OCA\TwoFactorSms\Service\SmsProvider\PlaySMS;
use OCP\AppFramework\App;
use OCP\IConfig;

class Application extends App {

	/**
	 * @param array $urlParams
	 */
	public function __construct($urlParams = []) {
		parent::__construct('twofactor_sms', $urlParams);

		$container = $this->getContainer();

		/* @var $config IConfig */
		$config = $container->query(IConfig::class);

		$provider = $config->getAppValue('twofactor_sms', 'sms_provider', 'puzzel.no');

		$container->registerAlias(ISmsService::class, $this->getSmsProviderClass($provider));
	}

	/**
	 * @param string $name
	 * @return string fully qualified class name
	 */
	private function getSmsProviderClass($name) {
		switch ($name) {
			case 'websms.de':
				return WebSmsDe::class;
			case 'playsms':
				return PlaySMS::class;
            case 'vianett.no':
                return ViaNett::class;
            case 'puzzel.no':
                return Puzzel::class;
		}
	}

}
