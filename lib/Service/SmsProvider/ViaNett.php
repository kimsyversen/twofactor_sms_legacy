<?php

/**
 * @author Pascal Clémot <pascal.clemot@free.fr>
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

namespace OCA\TwoFactorSms\Service\SmsProvider;

use Exception;
use OCA\TwoFactorSms\Exception\SmsTransmissionException;
use OCA\TwoFactorSms\Service\ISmsService;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\IConfig;

class ViaNett implements ISmsService {

	/** @var IClient */
	private $client;

	/** @var IConfig */
	private $config;

	/**
	 * @param IClientService $clientService
	 * @param IConfig $config
	 */
	public function __construct(IClientService $clientService, IConfig $config) {
		$this->client = $clientService->newClient();
		$this->config = $config;
	}

	/**
	 * @param string $recipient
	 * @param string $message
	 * @throws SmsTransmissionException
	 */
	public function send($recipient, $message) {
	    /*
./occ config:app:set twofactor_sms sms_provider --value "vianett.no"
./occ config:app:set twofactor_sms vianett_user --value "yourusername"
./occ config:app:set twofactor_sms vianett_password --value "yourpassword"

	     */
		$user = $this->config->getAppValue('twofactor_sms', 'vianett_user');
		$password = $this->config->getAppValue('twofactor_sms', 'vianett_password');
		try {
			$this->client->get('http://smsc.vianett.no/v3/send.ashx', [
				'query' => [
                    'dst' => $recipient,
                    'msg' => $message,
                    'username' => $user,
                    'password' => $password,
				],
			]);
		} catch (Exception $ex) {
			throw new SmsTransmissionException();
		}
	}

}
