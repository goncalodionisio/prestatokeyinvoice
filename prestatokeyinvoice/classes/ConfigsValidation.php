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
        $kiapi_key = (string)Configuration::get(_DB_PREFIX_.'PTINVC_KIAPI');
        return !(trim($kiapi_key) == "");
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
}