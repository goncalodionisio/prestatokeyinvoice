<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    Majoinfa - Sociedade Unipessoal Lda
 *  @copyright 2016-2021 Majoinfa - Sociedade Unipessoal Lda
 *  @license   LICENSE.txt
 */

class PHCXOperations extends Module
{

    public static function login()
    {
        $user_login = Configuration::get('PHCXCONNECTOR_USERNAME');
        $user_pass = Configuration::get('PHCXCONNECTOR_PASSWORD');
        $appID = Configuration::get('PHCXCONNECTOR_APPID');
        $company = Configuration::get('PHCXCONNECTOR_COMPANY');

        $params = array ('userCode' => $user_login,
            'password' => $user_pass,
            'applicationType' => $appID,
            'company' => $company
        );

        return PHCXOperations::connect("/REST/UserLoginWS/userLoginCompany", $params);
    }

    // connect to PHC
    private static function connect($url, $params)
    {
        $config_url = Configuration::get('PHCXCONNECTOR_CONFIG_URL');

        $auxUrl = $config_url . $url;

        // Build Http query using params
        $query = http_build_query ($params);

        $ch = curl_init();
        
        //URL to save cookie "ASP.NET_SessionId"
        curl_setopt($ch, CURLOPT_URL, $auxUrl);
        curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/32.0.1700.107 Chrome/32.0.1700.107 Safari/537.36');
        curl_setopt($ch, CURLOPT_POST, true);
        
        //Parameters passed to POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, '');
        curl_setopt($ch, CURLOPT_COOKIEFILE, '');
        
        return json_decode(curl_exec($ch), true);
    }

}