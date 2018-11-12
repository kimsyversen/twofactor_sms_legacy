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

class Puzzel implements ISmsService {

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
        $user = $this->config->getAppValue('twofactor_sms', 'puzzel_user');
        $password = $this->config->getAppValue('twofactor_sms', 'puzzel_password');
        $serviceId = $this->config->getAppValue('twofactor_sms', 'puzzel_serviceid');

        # Docs: https://github.com/Intelecom/SMS/blob/master/sections/interfaces/http-get.md
        try {
            $response = $this->client->get('https://smsgw.intele.com/gw/rs/sendMessages', [
                'query' => [
                    'serviceId'=> $serviceId,
                    'username' => $user,
                    'password' => $password,
                    "message[0].recipient" => "+".$recipient,
                    "message[0].content" => $message,
                ],
            ]);
              # Ref https://doc.owncloud.org/api/classes/OCP.Http.Client.IResponse.html
            file_put_contents('/var/log/nextcloud/twofactor_sms.log',$response->getBody()  . "\n", FILE_APPEND);

        } catch (Exception $ex) {
            $startIndex = strpos($ex->getMessage(), 'serviceId');
            $stopIndex = strpos($ex->getMessage(), 'content');
            $lengthIndex = $stopIndex - $startIndex + 51; # 51 is for removing last part of url:  content=807787%20is%20your%20authentication%20code
            $exMessage = substr_replace($streng, '*REMOVED*', $startIndex, $lengthIndex);

            file_put_contents('/var/log/nextcloud/twofactor_sms.log', $exMessage, FILE_APPEND);
            throw new SmsTransmissionException();
        }
    }

}
