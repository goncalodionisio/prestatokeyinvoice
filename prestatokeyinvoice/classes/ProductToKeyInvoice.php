<?php

class ProductToKeyInvoice extends Module
{

    public static function upsertProduct($ref, $designation, $shortName, $tax, $obs, $isService, $hasStocks, $active, $shortDesc, $longDesc, $price, $vendorRef, $ean)
    {
        $kiapi_key = Configuration::get(_DB_PREFIX_.'PTINVC_KIAPI');

        $url = "http://login.e-comercial.pt/API3_ws.php?wsdl";
        $client = new SoapClient($url);
        // see if key is valid before update config
        $kiapi_auth =  $client->authenticate("$kiapi_key");
        $session = $kiapi_auth[1];

        // check if exists to always upsert
        $productExists=$client->productExists("$session", "$ref");
        $result = $productExists[0];

        if ($result == 1) {

            $result = $client->updateProduct("$session", "$ref","$designation", "$shortName", "$tax", "$obs","$isService", "$hasStocks", "$active","$shortDesc", "$longDesc", "$price","$vendorRef", "$ean");

        } else {

            $result = $client->insertProduct("$session", "$ref","$designation", "$shortName", "$tax", "$obs","$isService", "$hasStocks", "$active","$shortDesc", "$longDesc", "$price","$vendorRef", "$ean");
        }

        return $result;
    }

    public static function save($product)
    {
        $ref = isset($product->reference) ? $product->reference : 'N/A';
        $designation = isset($product->name) ?  reset($product->name) : 'N/A';
        $shortName = 'N/A';

        $taxValue = $product->getIdTaxRulesGroup();
        $tax        = isset($taxValue) ? (string)PrestaToKeyInvoiceGetValueByID::getTaxByID($taxValue) : '';

        $obs        = "Produto inserido via PrestaToKeyinvoice";
        $isService  = isset($product->is_virtual) ? $product->is_virtual : '0';
        $hasStocks  = isset($product->is_virtual) ? ((int)$product->getQuantity($product->id) == 0 ? '0' : '1') : '0';
        $active     = isset($product->active) ? $product->active : '1';
        $shortDesc  = isset($product->description_short) ? utf8_encode(strip_tags(reset($product->description_short))) : 'N/A';
        $longDesc   = isset($product->description) ? utf8_encode(strip_tags(reset($product->description))) : 'N/A';
        $price      = isset($product->price) ? $product->price : '';
        $vendorRef  = isset($product->supplier_name) ? $product->supplier_name : 'N/A';
        $ean        = isset($product->ean13) ? $product->ean13 : '';

        return ProductToKeyInvoice::upsertProduct($ref, $designation, $shortName, $tax, $obs, $isService, $hasStocks, $active, $shortDesc, $longDesc, $price, $vendorRef, $ean);
    }

}