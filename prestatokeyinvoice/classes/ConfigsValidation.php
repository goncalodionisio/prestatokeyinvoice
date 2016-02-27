<?php

/**
 * Date: 14-02-2016
 * Time: 16:23
 *
 */
class ConfigsValidation extends Module
{
    public static function kiApiKeyExists()
    {
        $kiapi_key = (string)Configuration::get('PRESTATOKEYINVOICE_KIAPI');
        return !(trim($kiapi_key) == "");
    }
	
    public static function shippingCostProductExists()
    {
        $shippingCostProduct = (string)Configuration::get('PRESTATOKEYINVOICE_SHIPPINGCOST');
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
        Configuration::deleteByName('PRESTATOKEYINVOICE_KIAPI');
		Configuration::deleteByName('PRESTATOKEYINVOICE_PRODUCTS_SYNC');
		Configuration::deleteByName('PRESTATOKEYINVOICE_CLIENTS_SYNC');
		Configuration::deleteByName('PRESTATOKEYINVOICE_ORDERS_SYNC');
		Configuration::deleteByName('PRESTATOKEYINVOICE_SHIP_DOC_TYPE');
		Configuration::deleteByName('PRESTATOKEYINVOICE_INV_DOC_TYPE');
		Configuration::deleteByName('PRESTATOKEYINVOICE_SHIPPINGCOST');
    }

    /******************************************************
     * Get Sync Functions
     ******************************************************/

    public static function getSyncProducts()
    {
        return (int)Configuration::get('PRESTATOKEYINVOICE_PRODUCTS_SYNC');
    }

    public static function getSyncClients()
    {
        return (int)Configuration::get('PRESTATOKEYINVOICE_CLIENTS_SYNC');
    }

    public static function getSyncOrders()
    {
        return (int)Configuration::get('PRESTATOKEYINVOICE_ORDERS_SYNC');
    }
	
	public static function getShippingCostProduct()
    {
        return (int)Configuration::get('PRESTATOKEYINVOICE_SHIPPINGCOST');
    }

    /******************************************************
     * Set Sync Functions
     ******************************************************/

    public static function setSyncProducts($value)
    {
        Configuration::updateValue('PRESTATOKEYINVOICE_PRODUCTS_SYNC', $value);
    }

    public static function setSyncClients($value)
    {
        Configuration::updateValue('PRESTATOKEYINVOICE_CLIENTS_SYNC', $value);
    }

    public static function setSyncOrders($value)
    {
        Configuration::updateValue('PRESTATOKEYINVOICE_ORDERS_SYNC', $value);
    }

    public static function setDocTypeShip($value)
    {
        Configuration::updateValue('PRESTATOKEYINVOICE_SHIP_DOC_TYPE', $value);
    }

    public static function setDocTypeInv($value)
    {
        Configuration::updateValue('PRESTATOKEYINVOICE_INV_DOC_TYPE', $value);
    }
	
    public static function setShippingCostProduct($value)
    {
        Configuration::updateValue('PRESTATOKEYINVOICE_SHIPPINGCOST', $value);
    }
}