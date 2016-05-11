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

/*
 * Date: 14-02-2016
 * Time: 16:23
 *
*/

class PTInvoiceConfigsValidation extends Module
{

    /******************************************************
     * Sessions
     ******************************************************/

    /**
     * @return bool|SoapClient
     */
    public static function APIWSClient()
    {

        try {

            $url = "http://login.e-comercial.pt/API3_ws.php?wsdl";
            $client = new SoapClient($url);

            return $client;

        } catch (Exception $e) {

            return false;
        }

    }

    /**
     * @param $client
     * @param $from
     * @return bool
     */
    public static function APIWSSession($client, $from)
    {

        if ($from == 'getContent') {

            $kiapi_key = Tools::getValue('PTInvoice_KIAPI');

        } else {

            $kiapi_key = Configuration::get('PTInvoice_KIAPI');

        }

        $kiapi_auth =  $client->authenticate("$kiapi_key");
        if ($kiapi_auth[0] != 1) {

            return false;
        }

        $session = $kiapi_auth[1];
        return $session;
    }

    /******************************************************
     * Validations
     ******************************************************/

    /**
     * @return bool
     */
    public static function PTInvoiceIdExists()
    {
        $appID = (string)Configuration::get('PTInvoice_APPID');
        $configUrl = (string)Configuration::get('PTInvoice_CONFIG_URL');
        $username = (string)Configuration::get('PTInvoice_USERNAME');
        $password = (string)Configuration::get('PTInvoice_PASSWORD');
        $company = (string)Configuration::get('PTInvoice_COMPANY');

        return !(trim($appID) == "" ||
            trim($configUrl) == "" ||
            trim($username) == "" ||
            trim($password) == "" ||
            trim($company) == "");
    }

    /**
     * @return bool
     */
    public static function shippingCostProductExists()
    {
        $shippingCostProduct = (string)Configuration::get('PTInvoice_SHIPPINGCOST');
        return !(trim($shippingCostProduct) == "");
    }

    /**
     * @return bool
     */
    public static function syncProducts()
    {
        return ((int)PTInvoiceConfigsValidation::getSyncProducts() == 1);
    }

    /**
     * @return bool
     */
    public static function syncClients()
    {
        return ((int)PTInvoiceConfigsValidation::getSyncClients() == 1);
    }

    /**
     * @return bool
     */
    public static function syncOrders()
    {
        return ((int)PTInvoiceConfigsValidation::getSyncOrders() == 1);
    }

    /**
     *
     */
    public static function disableSyncronization()
    {
        PTInvoiceConfigsValidation::setSyncProducts('0');
        PTInvoiceConfigsValidation::setSyncClients('0');
        PTInvoiceConfigsValidation::setSyncOrders('0');
    }


    /**
     *
     */
    public static function deleteByName()
    {
        Configuration::deleteByName('PTInvoice_CONFIG_URL');
        Configuration::deleteByName('PTInvoice_PASSWORD');
        Configuration::deleteByName('PTInvoice_USERNAME');
        Configuration::deleteByName('PTInvoice_APPID');
        Configuration::deleteByName('PTInvoice_COMPANY');

        Configuration::deleteByName('PTInvoice_PRODUCTS_SYNC');
        Configuration::deleteByName('PTInvoice_CLIENTS_SYNC');
        Configuration::deleteByName('PTInvoice_ORDERS_SYNC');
        Configuration::deleteByName('PTInvoice_SHIP_DOC_TYPE');
        Configuration::deleteByName('PTInvoice_INV_DOC_TYPE');
        Configuration::deleteByName('PTInvoice_SHIPPINGCOST');
    }

    /******************************************************
     * Get Sync Functions
     ******************************************************/

    /**
     * @return int
     */
    public static function getSyncProducts()
    {
        return (int)Configuration::get('PTInvoice_PRODUCTS_SYNC');
    }

    /**
     * @return int
     */
    public static function getSyncClients()
    {
        return (int)Configuration::get('PTInvoice_CLIENTS_SYNC');
    }

    /**
     * @return int
     */
    public static function getSyncOrders()
    {
        return (int)Configuration::get('PTInvoice_ORDERS_SYNC');
    }

    /**
     * @return int
     */
    public static function getShippingCostProduct()
    {
        return (int)Configuration::get('PTInvoice_SHIPPINGCOST');
    }

    /******************************************************
     * Set Sync Functions
     ******************************************************/

    /**
     * @param $value
     */
    public static function setkiapi($value)
    {
        Configuration::updateValue('PTInvoice_APPID', $value);
    }

    /**
     * @param $value
     */
    public static function setconfig_url($value)
    {
        Configuration::updateValue('PTInvoice_CONFIG_URL', $value);
    }

    /**
     * @param $value
     */
    public static function setusername($value)
    {
        Configuration::updateValue('PTInvoice_USERNAME', $value);
    }

    /**
     * @param $value
     */
    public static function setpassword($value)
    {
        Configuration::updateValue('PTInvoice_PASSWORD', $value);
    }

    /**
     * @param $value
     */
    public static function setcompany($value)
    {
        Configuration::updateValue('PTInvoice_COMPANY', $value);
    }

    /**
     * @param $value
     */
    public static function setSyncProducts($value)
    {
        Configuration::updateValue('PTInvoice_PRODUCTS_SYNC', $value);
    }

    /**
     * @param $value
     */
    public static function setSyncClients($value)
    {
        Configuration::updateValue('PTInvoice_CLIENTS_SYNC', $value);
    }

    /**
     * @param $value
     */
    public static function setSyncOrders($value)
    {
        Configuration::updateValue('PTInvoice_ORDERS_SYNC', $value);
    }

    /**
     * @param $value
     */
    public static function setDocTypeShip($value)
    {
        Configuration::updateValue('PTInvoice_SHIP_DOC_TYPE', $value);
    }

    /**
     * @param $value
     */
    public static function setDocTypeInv($value)
    {
        Configuration::updateValue('PTInvoice_INV_DOC_TYPE', $value);
    }

    /**
     * @param $value
     */
    public static function setShippingCostProduct($value)
    {
        Configuration::updateValue('PTInvoice_SHIPPINGCOST', $value);
    }
}
