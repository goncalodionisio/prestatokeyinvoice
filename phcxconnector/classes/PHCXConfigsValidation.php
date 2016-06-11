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

class PHCXConfigsValidation extends Module
{

    /******************************************************
     * Sessions
     ******************************************************/
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
    
    public static function APIWSSession($client, $from)
    {
        
        if ($from == 'getContent') {

            $kiapi_key = Tools::getValue('PHCXCONNECTOR_KIAPI');

        } else {

            $kiapi_key = Configuration::get('PHCXCONNECTOR_KIAPI');

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
    // change this stuff
    public static function PHCXIdExists()
    {
        $appID = (string)Configuration::get('PHCXCONNECTOR_APPID');
        $configUrl = (string)Configuration::get('PHCXCONNECTOR_CONFIG_URL');
        $username = (string)Configuration::get('PHCXCONNECTOR_USERNAME');
        $password = (string)Configuration::get('PHCXCONNECTOR_PASSWORD');
        $company = (string)Configuration::get('PHCXCONNECTOR_COMPANY');

        return !(trim($appID) == "" ||
            trim($configUrl) == "" ||
            trim($username) == "" ||
            trim($password) == "" ||
            trim($company) == "");
    }
    
    public static function shippingCostProductExists()
    {
        $shippingCostProduct = (string)Configuration::get('PHCXCONNECTOR_SHIPPINGCOST');
        return !(trim($shippingCostProduct) == "");
    }

    public static function syncProducts()
    {
        return ((int)ConfigsValidation::getSyncProducts() == 1);
    }

    public static function syncClients()
    {
        return ((int)ConfigsValidation::getSyncClients() == 1);
    }

    public static function syncOrders()
    {
        return ((int)ConfigsValidation::getSyncOrders() == 1);
    }

    public static function disableSyncronization()
    {
        ConfigsValidation::setSyncProducts('0');
        ConfigsValidation::setSyncClients('0');
        ConfigsValidation::setSyncOrders('0');
    }
    
    
    public static function deleteByName()
    {
        Configuration::deleteByName('PHCXCONNECTOR_CONFIG_URL');
        Configuration::deleteByName('PHCXCONNECTOR_PASSWORD');
        Configuration::deleteByName('PHCXCONNECTOR_USERNAME');
        Configuration::deleteByName('PHCXCONNECTOR_APPID');
        Configuration::deleteByName('PHCXCONNECTOR_COMPANY');

        Configuration::deleteByName('PHCXCONNECTOR_PRODUCTS_SYNC');
        Configuration::deleteByName('PHCXCONNECTOR_CLIENTS_SYNC');
        Configuration::deleteByName('PHCXCONNECTOR_ORDERS_SYNC');
        Configuration::deleteByName('PHCXCONNECTOR_SHIP_DOC_TYPE');
        Configuration::deleteByName('PHCXCONNECTOR_INV_DOC_TYPE');
        Configuration::deleteByName('PHCXCONNECTOR_SHIPPINGCOST');
    }

    /******************************************************
     * Get Sync Functions
     ******************************************************/

    public static function getSyncProducts()
    {
        return (int)Configuration::get('PHCXCONNECTOR_PRODUCTS_SYNC');
    }

    public static function getSyncClients()
    {
        return (int)Configuration::get('PHCXCONNECTOR_CLIENTS_SYNC');
    }

    public static function getSyncOrders()
    {
        return (int)Configuration::get('PHCXCONNECTOR_ORDERS_SYNC');
    }
    
    public static function getShippingCostProduct()
    {
        return (int)Configuration::get('PHCXCONNECTOR_SHIPPINGCOST');
    }

    /******************************************************
     * Set Sync Functions
     ******************************************************/

    public static function setkiapi($value)
    {
        Configuration::updateValue('PHCXCONNECTOR_APPID', $value);
    }

    public static function setconfig_url($value)
    {
        Configuration::updateValue('PHCXCONNECTOR_CONFIG_URL', $value);
    }

    public static function setusername($value)
    {
        Configuration::updateValue('PHCXCONNECTOR_USERNAME', $value);
    }

    public static function setpassword($value)
    {
        Configuration::updateValue('PHCXCONNECTOR_PASSWORD', $value);
    }

    public static function setcompany($value)
    {
        Configuration::updateValue('PHCXCONNECTOR_COMPANY', $value);
    }

    public static function setSyncProducts($value)
    {
        Configuration::updateValue('PHCXCONNECTOR_PRODUCTS_SYNC', $value);
    }

    public static function setSyncClients($value)
    {
        Configuration::updateValue('PHCXCONNECTOR_CLIENTS_SYNC', $value);
    }

    public static function setSyncOrders($value)
    {
        Configuration::updateValue('PHCXCONNECTOR_ORDERS_SYNC', $value);
    }

    public static function setDocTypeShip($value)
    {
        Configuration::updateValue('PHCXCONNECTOR_SHIP_DOC_TYPE', $value);
    }

    public static function setDocTypeInv($value)
    {
        Configuration::updateValue('PHCXCONNECTOR_INV_DOC_TYPE', $value);
    }
    
    public static function setShippingCostProduct($value)
    {
        Configuration::updateValue('PHCXCONNECTOR_SHIPPINGCOST', $value);
    }
}