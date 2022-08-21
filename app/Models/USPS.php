<?php

namespace App\Models;

use App\Helpers\HttpHelper;
use Google\Service\CloudTasks\HttpRequest;
use Google\Service\Docs\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class USPS
{
    const PRODUCTION_API = 'http://production.shippingapis.com';
    const STAGING_API    = 'https://secure.shippingapis.com';

    public static function validateAddress(string $xmlData = '') 
    {
        $url = self::STAGING_API.'/ShippingAPI.dll?API=Verify';
        $headers = self::headers();

        return HttpHelper::requestXML($url, $xmlData, $headers);
    }

    public static function cityLookUp(string $xmlData = '') 
    {
        $url = self::STAGING_API.'/ShippingAPI.dll?API=CityStateLookup';
        $headers = self::headers();
        return HttpHelper::requestXML($url, $xmlData, $headers);
    }

    public static function zipCodeLookUp(string $xmlData = '') 
    {
        $url = self::STAGING_API.'/ShippingAPI.dll?API=ZipCodeLookup';
        $headers = self::headers();
        return HttpHelper::requestXML($url, $xmlData, $headers);
    }
    

    public static function dummyAddressPayload($revision = 1, $address1 = '', $address2 = '', $city = '', $state = '', $zip5 = '', $zip4 = '') 
    {
        return '<AddressValidateRequest USERID="974FLEXM7409"><Revision>' . $revision . '</Revision><Address><Address1>'  . $address1 . '</Address1><Address2>' . $address2 . '</Address2><City>' . $city . '</City><State>' . $state . '</State><Zip5>' . $zip5 . '</Zip5><Zip4></Zip4></Address></AddressValidateRequest>';
    }

    public static function dummyCityLookUp($zip5 = '') 
    {
        return '<CityStateLookupRequest USERID="974FLEXM7409"><ZipCode><Zip5>' .$zip5. '</Zip5></ZipCode></CityStateLookupRequest>';
    }

    public static function dummyZipCodeLookUp($address1 = '', $address2 = '', $city = '', $state = '') 
    {
        return '<ZipCodeLookupRequest USERID="974FLEXM7409"><Address><Address1>'  . $address1 . '</Address1><Address2>' . $address2 . '</Address2><City>' . $city . '</City><State>' . $state . '</State></Address></ZipCodeLookupRequest>';
    }

    public static function headers($isGet = true) : array 
    {
        if(! $isGet) {
            // @TODO: implement something that changes header if the request is not get
            return [];
        }

        return [
                'Accept'          => '*/*', 
                'Accept-Encoding' => 'gzip, deflate, br', 
                'Content-Type'    =>'application/x-www-form-urlencoded'
               ];
    }
}
