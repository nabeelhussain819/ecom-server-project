<?php

namespace App\Models;

use App\Helpers\HttpHelper;
use Google\Service\CloudTasks\HttpRequest;
use Google\Service\Docs\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Fedex
{
    const API = 'https://apis-sandbox.fedex.com';

    public static function authorize()
    {
        $url = self::API . '/oauth/token';
        $headers = ['Content-Type' => 'application/x-www-form-urlencoded'];
        $body = [
            'grant_type' => 'client_credentials',
            'client_id' => env('FEDEX_API_KEY'),
            'client_secret' => env('FEDEX_SECRET_KEY'),
        ];

        return HttpHelper::request($url, $body, 'POST', $headers);
    }


    public static function validateShipment(string $token, array $data = []) 
    {
        $url = self::API . '/ship/v1/shipments/packages/validate';
     
        $headers = array();

        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer '. $token;
        $headers[] = 'X-locale: en_US';

        return HttpHelper::request($url, $data, 'POST', $headers, true);
    }

    public static function dummyPayload() {
        return array(
            'requestedShipment' => [
              'pickupType' => 'USE_SCHEDULED_PICKUP',
              'serviceType' => 'PRIORITY_OVERNIGHT',
              'packagingType' => 'YOUR_PACKAGING',
              'shipper' => [
                'address' => [
                  'streetLines' => [
                    0 => '10 FedEx Parkway',
                    1 => 'Suite 302'
                  ],
                  'city' => 'Beverly Hills',
                  'stateOrProvinceCode' => 'CA',
                  'postalCode' => 90210,
                  'countryCode' => 'US'
                ],
                'contact' => [
                  'personName' => 'SHIPPER NAME',
                  'phoneNumber' => 1234567890,
                  'companyName' => 'Shipper Company Name'
                ],
              ],
              'recipients' => [
                'address' => [
                  'streetLines' => '-10 FedEx Parkway -Suite 302',
                  'city' => 'Beverly Hills',
                  'stateOrProvinceCode' => 'CA',
                  'postalCode' => 90210,
                  'countryCode' => 'US',
                ],
                'contact' => [
                  'personName' => 'SHIPPER NAME',
                  'phoneNumber' => 9612345671,
                  'companyName' => 'Shipper Company Name',
                ],
              ],
              'shippingChargesPayment' => [
                'paymentType' => 'SENDER',
                'payor' => [
                  'responsibleParty' => [
                    'accountNumber' => [
                      'value' => 'Your account number',
                    ],
                  ],
                ],
              ],
              'labelSpecification' => [
                'labelStockType' => 'PAPER_7X475',
                'imageType' => 'PDF',
              ],
              'requestedPackageLineItems' => [
                'weight' => [
                  'units' => 'LB',
                  'value' => 68,
                ],
              ],
            ],
            'accountNumber' => [
              'value' => 'Your account number',
            ],
        );
    }
}
