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
        return ((int)Configuration::get('PRESTATOKEYINVOICE_PRODUCTS_SYNC') == 1);
    }

    public static function syncClients()
    {
        return ((int)Configuration::get('PRESTATOKEYINVOICE_CLIENTS_SYNC') == 1);
    }

    public static function syncOrders()
    {
        return ((int)Configuration::get('PRESTATOKEYINVOICE_ORDERS_SYNC') == 1);
    }
}