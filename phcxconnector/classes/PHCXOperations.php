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
    var $ch;
    var $config_url;

    public function PHCXOperations()
    {
        $this->ch = curl_init();
        $this->config_url = Configuration::get('PHCXCONNECTOR_CONFIG_URL');
    }

    function login()
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

        curl_setopt($this->ch, CURLOPT_URL, $this->config_url . "/REST/UserLoginWS/userLoginCompany");
        curl_setopt($this->ch, CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/32.0.1700.107 Chrome/32.0.1700.107 Safari/537.36');
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query ($params));
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($this->ch, CURLOPT_COOKIEJAR, '');
        curl_setopt($this->ch, CURLOPT_COOKIEFILE, '');

        $response = json_decode(curl_exec($this->ch), true);

        if (empty($response))
            return array("nok", "Can't connect to webservice!! There's an empty response");
        else if(isset($response['messages'][0]['messageCodeLocale']))
            return array("nok", "Wrong Login! Please check your entered data!");

        return array("ok", "");
    }

    function logout()
    {
        curl_setopt($this->ch, CURLOPT_URL, $this->config_url . "/REST/UserLoginWS/userLogout");
        curl_setopt($this->ch, CURLOPT_POST, false);
        return json_decode(curl_exec($this->ch), true);
    }

    // builds structure for new object
    function newInstance($objType)
    {
        $url = "/REST/{$objType}/getNewInstance";
        $params = array ('ndos' => 0);

        return $this->runOperation($url, $params);
    }

    function save($objType, $result)
    {
        $url = "/REST/{$objType}/Save";
        $params = array ('itemVO' => json_encode($result), 'runWarningRules' => 'false');

        return $this->runOperation($url, $params);
    }

    function query($objType, $fields)
    {
        $data = '{ "groupByItems":[],"lazyLoaded":false,"joinEntities":[],"orderByItems":[],"SelectItems":[],"entityName":"","filterItems":[';

        foreach ($fields as $field) {
            $data = $data . '{"comparison":0,"filterItem":"' . $field['column'] . '","valueItem":"' . $field['value'] . '","groupItem":9,"checkNull":false,"skipCheckType":false,"type":"Number"}';
        }

	    $data = $data . ']}';

        $url = "/REST/{$objType}/Query";
        $params = array ('itemQuery' => $data);

        return $this->runOperation($url, $params);
    }

    function update($objType, $stamp, $field, $value)
    {
        $url = "/REST/{$objType}/updateEntity";
        $params = array ('Stamp' => $stamp, 'field' => $field, 'newValue' => $value);
        return $this->runOperation($url, $params);
    }

    // run POST
    private function runOperation($url, $params)
    {
        curl_setopt($this->ch, CURLOPT_URL, $this->config_url . $url);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query ($params));
        curl_setopt($this->ch, CURLOPT_POST, true);

        return json_decode(curl_exec($this->ch), true);
    }

    public static function ResponseStatus($response)
    {
        if (empty($response))
            return array("nok", "Can't connect to webservice!! There's an empty response");
        else if ($response == null)
            return array("nok", "Unknown error");
        else if(isset($response['messages'][0]))
            return array("nok", $response['messages'][0]);
        else
            return array("ok", "");
    }
}