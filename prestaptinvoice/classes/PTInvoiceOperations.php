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
 * @author    Majoinfa - Sociedade Unipessoal Lda
 * @copyright 2016-2021 Majoinfa - Sociedade Unipessoal Lda
 * @license   LICENSE.txt
 */

class PTInvoiceOperations extends Module
{

    /**
     * PTInvoiceOperations constructor.
     */
    public function PTInvoiceOperations()
    {
        $this->ch = curl_init();
        $this->config_url = Configuration::get('PTInvoice_CONFIG_URL');
    }

    /**
     * @return array
     */
    public function login()
    {
        $user_login = Configuration::get('PTInvoice_USERNAME');
        $user_pass = Configuration::get('PTInvoice_PASSWORD');
        $appID = Configuration::get('PTInvoice_APPID');
        $company = Configuration::get('PTInvoice_COMPANY');

        $params = array('userCode' => $user_login,
            'password' => $user_pass,
            'applicationType' => $appID,
            'company' => $company
        );

        curl_setopt($this->ch, CURLOPT_URL, $this->config_url . "/REST/UserLoginWS/userLoginCompany");
        curl_setopt(
            $this->ch,
            CURLOPT_USERAGENT,
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) 
            Ubuntu Chromium/32.0.1700.107 Chrome/32.0.1700.107 Safari/537.36'
        );
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($this->ch, CURLOPT_COOKIEJAR, '');
        curl_setopt($this->ch, CURLOPT_COOKIEFILE, '');

        $response = Tools::jsonDecode(curl_exec($this->ch), true);

        if (empty($response)) {

            return array("nok", "Can't connect to webservice!! There's an empty response");

        } elseif (isset($response['messages'][0]['messageCodeLocale'])) {

            return array("nok", "Wrong Login! Please check your entered data!");

        } else {

            return array("ok", "");
        }
    }

    /**
     * @return mixed
     */
    public function logout()
    {
        curl_setopt($this->ch, CURLOPT_URL, $this->config_url . "/REST/UserLoginWS/userLogout");
        curl_setopt($this->ch, CURLOPT_POST, false);
        return Tools::jsonDecode(curl_exec($this->ch), true);
    }

    // builds structure for new object
    /**
     * @param $objType
     * @param $params
     * @return mixed
     */
    public function newInstance($objType, $params)
    {
        $url = "/REST/{$objType}/getNewInstance";
        return $this->runOperation($url, $params);
    }


    /**
     * @param $objType
     * @param $method
     * @param $params
     * @return mixed
     */
    public function sendOperation($objType, $method, $params)
    {
        $url = "/REST/{$objType}/{$method}";
        return $this->runOperation($url, $params);
    }

    /**
     * @param $objType
     * @param $result
     * @return mixed
     */
    public function save($objType, $result)
    {
        $url = "/REST/{$objType}/Save";
        $params = array('itemVO' => ToolsCore::jsonEncode($result), 'runWarningRules' => 'false');

        return $this->runOperation($url, $params);
    }

    /**
     * @param $objType
     * @param $fields
     * @return mixed
     */
    public function query($objType, $fields)
    {
        $data = '{ "groupByItems":[],
        "lazyLoaded":false,
        "joinEntities":[],
        "orderByItems":[],
        "SelectItems":[],
        "entityName":"",
        "filterItems":[';

        foreach ($fields as $field) {
            $data = $data . '{"comparison":0,
            "filterItem":"' . $field['column'] . '",
            "valueItem":"' . $field['value'] . '",
            "groupItem":9,
            "checkNull":false,
            "skipCheckType":false,
            "type":"Number"}';
        }

        $data = $data . ']}';

        $url = "/REST/{$objType}/Query";
        $params = array('itemQuery' => $data);

        return $this->runOperation($url, $params);
    }

    /**
     * @param $objType
     * @param $stamp
     * @param $field
     * @param $value
     * @return mixed
     */
    public function update($objType, $stamp, $field, $value)
    {
        $url = "/REST/{$objType}/updateEntity";
        $params = array('Stamp' => $stamp, 'field' => $field, 'newValue' => $value);
        return $this->runOperation($url, $params);
    }

    // run POST
    /**
     * @param $url
     * @param $params
     * @return mixed
     */
    private function runOperation($url, $params)
    {
        curl_setopt($this->ch, CURLOPT_URL, $this->config_url . $url);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($this->ch, CURLOPT_POST, true);

        return Tools::jsonDecode(curl_exec($this->ch), true);
    }

    /**
     * @param $response
     * @return array
     */
    public static function responseStatus($response)
    {
        if (empty($response)) {

            return array(
                "nok",
                utf8_encode(
                    "Can't connect to webservice!! There's an empty response"
                )
            );

        } elseif ($response == null) {

            return array("nok", "Unknown error");

        } elseif (isset($response['messages'][0])) {

            if (is_array($response['messages'][0])) {

                $responseArray = $response['messages'][0];

                if (isset($responseArray['messageCodeLocale'])) {

                    return array(
                        "nok",
                        utf8_encode(
                            $responseArray['messageCodeLocale']
                        )
                    );

                } else {

                    return array("nok", 'PHCFX error cannot be parsed.');
                }

            } else {

                return array("nok", utf8_encode($response['messages'][0]));
            }

        } else {

            return array("ok", "");
        }
    }
}
