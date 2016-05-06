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

class ProductToPHCX extends Module
{

    public static function upsertProduct($ref, $designation, $shortName, $tax, $obs, $stock, $active, $shortDesc, $longDesc, $price, $vendorRef, $ean) {


        // check if exists to always upsert
        $phcxOps = new PHCXOperations();
        $response = $phcxOps->login();

        if ($response[0] == "nok")
            return $response;

        // product exists ?
        $response = $phcxOps->query("StWS", array(array('column' => 'ref', 'value' => $ref)));

        if (count($response['result']) != 0) {

            //$result = $client->updateProduct("$session", "$ref", "$designation", "$shortName", "$tax", "$obs", "$stock", "$active", "$shortDesc", "$longDesc", "$price", "$vendorRef", "$ean");
            var_dump("helo");
            die();
        } else {
            //$result = $client->insertProduct("$session", "$ref", "$designation", "$shortName", "$tax", "$obs", "$isService", "$hasStocks", "$active", "$shortDesc", "$longDesc", "$price", "$vendorRef", "$ean");

            // new product
            $result = $phcxOps->newInstance("StWS");

            $result['result'][0]['ref'] = $ref;
            $result['result'][0]['design'] = $designation;
            $result['result'][0]['desctec'] = $longDesc;
            $result['result'][0]['tipodesc'] = $obs;
            $result['result'][0]['codigo'] = $ean;

            //$result['result'][0]['tabiva'] = $tax;
            $result['result'][0]['epv1iva'] = $tax;
            $result['result'][0]['epv1'] = $price;
            $result['result'][0]['quantity'] = $stock;


            $result = $phcxOps->save("StWS", $result['result'][0]);

            var_dump($result);
            die();

/*
ref 		=> reference
stock 		=> quantity
desctec 	=> description
tabiva		=> $tax
codigo		=> isset($product->ean13) ? $product->ean13 : '';

[familia"]         => string(13) "Membranofones"
["faminome"]         => string(62) "Instrumento cujo elemento vibratório é uma membrana retesada"

tipodesc	=> "Produto inserido via PHCX Connector"
desctec 	=> description
*/

        }

        return $result;
    }

    public static function saveByProductObject($product)
    {
        $ref = isset($product->reference) ? $product->reference : 'N/A';
        $designation = isset($product->name) ? utf8_encode(ProductToPHCX::stringOrArray($product->name)) : 'N/A';

        $shortName = 'N/A';

        $taxValue = $product->getIdTaxRulesGroup();
        $tax      = isset($taxValue) ? (string)PHCXConnectorGetValueByID::getTaxByRulesGroup($taxValue) : '';

        if ($tax == "") {
            // if empty set 0 tax
            $tax = "0";
        }
        $obs        = "Produto inserido via PHCX Connector";
        $isService  = isset($product->is_virtual) ? $product->is_virtual : '0';
        #$hasStocks  = isset($product->is_virtual) ? ((int)$product->getQuantity($product->id) == 0 ? '0' : '1') : '0';
        $stock      = (int)$product->getQuantity($product->id);
        $active     = isset($product->active) ? $product->active : '1';
        $shortDesc  = isset($product->description_short) ? utf8_encode(strip_tags(ProductToPHCX::stringOrArray($product->description_short))) : 'N/A';
        $longDesc   = isset($product->description) ? utf8_encode(strip_tags(ProductToPHCX::stringOrArray($product->description))) : 'N/A';
        $price      = isset($product->price) ? $product->price : '';
        $vendorRef  = isset($product->supplier_name) ? $product->supplier_name : 'N/A';
        $ean        = isset($product->ean13) ? $product->ean13 : '';

        return ProductToPHCX::upsertProduct(
            $ref,
            $designation,
            $shortName,
            $tax,
            $obs,
            $stock,
            $active,
            $shortDesc,
            $longDesc,
            $price,
            $vendorRef,
            $ean
        );
    }

    public static function saveByIdProduct($idProduct)
    {
        $default_language = Configuration::get('PS_LANG_DEFAULT');
        $default_shop_id = Configuration::get('PS_SHOP_DEFAULT');

        if (Validate::isLoadedObject(
            $product = new Product(
                $idProduct,
                false,
                $default_language,
                $default_shop_id,
                null
            )
        )) {
            return ProductToPHCX::saveByProductObject($product);
        }
        return null;
    }

    public static function stringOrArray($data)
    {
        if (is_array($data)) {
            return reset($data);
        }
        return $data;
    }
}
