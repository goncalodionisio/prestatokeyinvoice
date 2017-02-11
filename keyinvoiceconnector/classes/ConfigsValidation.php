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

class ConfigsValidation extends Module
{


    /**
     * Sessions
     *
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
     * Sessions
     *
     * @param $client
     * @param $from
     * @return bool
     */
    public static function APIWSSession($client, $from)
    {

        if ($from == 'getContent') {

            $kiapi_key = Tools::getValue('KEYINVOICECONNECTOR_KIAPI');

        } else {

            $kiapi_key = Configuration::get('KEYINVOICECONNECTOR_KIAPI');

        }

        $kiapi_auth =  $client->authenticate("$kiapi_key");
        if ($kiapi_auth[0] != 1) {

            return false;
        }

        $session = $kiapi_auth[1];
        return $session;
    }


    /**
     * Validation
     *
     * @return bool
     */
    public static function kiApiKeyExists()
    {
        $kiapi_key = (string)Configuration::get('KEYINVOICECONNECTOR_KIAPI');
        return !(trim($kiapi_key) == "");
    }

    /**
     *
     * @return bool
     */
    public static function shippingCostProductExists()
    {
        $shippingCostProduct = (string)Configuration::get('KEYINVOICECONNECTOR_SHIPPINGCOST');
        return !(trim($shippingCostProduct) == "");
    }

    /**
     * @return bool
     */
    public static function syncProducts()
    {
        return ((int)ConfigsValidation::getSyncProducts() == 1);
    }


    /**
     * @return bool
     */
    public static function syncMasterProducts()
    {
        return ((int)ConfigsValidation::getKeyMasterProducts() == 1);
    }

    /**
     * @return bool
     */
    public static function syncClients()
    {
        return ((int)ConfigsValidation::getSyncClients() == 1);
    }

    /**
     * @return bool
     */
    public static function syncOrders()
    {
        return ((int)ConfigsValidation::getSyncOrders() == 1);
    }

    /**
     * @return bool
     */
    public static function isInDebug()
    {
        return ((int)ConfigsValidation::getDebug() == 1);
    }

    /**
     * Disable Module Configs
     */
    public static function disableSyncronization()
    {
        ConfigsValidation::setSyncProducts('0');
        ConfigsValidation::setKeyMasterProducts('0');
        ConfigsValidation::setSyncClients('0');
        ConfigsValidation::setSyncOrders('0');
    }


    /**
     * Delete module setting from DB
     */
    public static function deleteByName()
    {
        Configuration::deleteByName('KEYINVOICECONNECTOR_KIAPI');
        Configuration::deleteByName('KEYINVOICECONNECTOR_PRODUCTS_SYNC');
        Configuration::deleteByName('KEYINVOICECONNECTOR_MASTER_PRODUCTS');
        Configuration::deleteByName('KEYINVOICECONNECTOR_CLIENTS_SYNC');
        Configuration::deleteByName('KEYINVOICECONNECTOR_ORDERS_SYNC');
        Configuration::deleteByName('KEYINVOICECONNECTOR_SHIP_DOC_TYPE');
        Configuration::deleteByName('KEYINVOICECONNECTOR_INV_DOC_TYPE');
        Configuration::deleteByName('KEYINVOICECONNECTOR_SHIPPINGCOST');
    }

    /******************************************************
     * Get Sync Functions
     ******************************************************/

    /**
     * @return int
     */
    public static function getSyncProducts()
    {
        return (int)Configuration::get('KEYINVOICECONNECTOR_PRODUCTS_SYNC');
    }

    /**
     * @return int
     */
    public static function getKeyMasterProducts()
    {
        return (int)Configuration::get('KEYINVOICECONNECTOR_MASTER_PRODUCTS');
    }

    /**
     * @return int
     */
    public static function getSyncClients()
    {
        return (int)Configuration::get('KEYINVOICECONNECTOR_CLIENTS_SYNC');
    }

    /**
     * @return int
     */
    public static function getSyncOrders()
    {
        return (int)Configuration::get('KEYINVOICECONNECTOR_ORDERS_SYNC');
    }

    /**
     * @return int
     */
    public static function getShippingCostProduct()
    {
        return (int)Configuration::get('KEYINVOICECONNECTOR_SHIPPINGCOST');
    }

    /**
     * @return int
     */
    public static function getDebug()
    {
        return (int)Configuration::get('KEYINVOICECONNECTOR_DEBUG');
    }

    /**
     * @return int
     */
    public static function getDebugValue()
    {
        return Configuration::get('KEYINVOICECONNECTOR_DEBUG_VALUE');
    }

    /******************************************************
     * Set Sync Functions
     ******************************************************/

    /**
     * @param $value
     */
    public static function setkiapi($value)
    {
        Configuration::updateValue('KEYINVOICECONNECTOR_KIAPI', $value);
    }

    /**
     * @param $value
     */
    public static function setSyncProducts($value)
    {
        Configuration::updateValue('KEYINVOICECONNECTOR_PRODUCTS_SYNC', $value);
    }

    /**
     * @param $value
     */
    public static function setKeyMasterProducts($value)
    {
        Configuration::updateValue('KEYINVOICECONNECTOR_MASTER_PRODUCTS', $value);
    }

    /**
     * @param $value
     */
    public static function setSyncClients($value)
    {
        Configuration::updateValue('KEYINVOICECONNECTOR_CLIENTS_SYNC', $value);
    }

    /**
     * @param $value
     */
    public static function setSyncOrders($value)
    {
        Configuration::updateValue('KEYINVOICECONNECTOR_ORDERS_SYNC', $value);
    }

    /**
     * @param $value
     */
    public static function setDocTypeShip($value)
    {
        Configuration::updateValue('KEYINVOICECONNECTOR_SHIP_DOC_TYPE', $value);
    }

    /**
     * @param $value
     */
    public static function setDocTypeInv($value)
    {
        Configuration::updateValue('KEYINVOICECONNECTOR_INV_DOC_TYPE', $value);
    }

    /**
     * @param $value
     */
    public static function setShippingCostProduct($value)
    {
        Configuration::updateValue('KEYINVOICECONNECTOR_SHIPPINGCOST', $value);
    }

    /**
     * @param $value
     */
    public static function setDebug($value)
    {
        Configuration::updateValue('KEYINVOICECONNECTOR_DEBUG', $value);
    }

    /**
     * @param $value
     */
    public static function setDebugValue($value)
    {
        Configuration::updateValue('KEYINVOICECONNECTOR_DEBUG_VALUE', $value);
    }
}
