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

    public static function upsertProduct($ref, $designation, $shortName, $tax, $obs, $stock, $active, $shortDesc, $longDesc, $price, $vendorRef, $ean, $category) {

        // check if exists to always upsert
        $phcxOps = new PHCXOperations();
        $response = $phcxOps->login();

        if ($response[0] == "nok")
            return $response;

        // product exists ?
        $response = $phcxOps->query("StWS", array(array('column' => 'ref', 'value' => $ref)));

        $status = PHCXOperations::ResponseStatus($response);
        if ($status == 'nok') { return $status; }

        if (count($response['result']) != 0) {
            // update existent product
            $ststamp = $response['result'][0]['ststamp'];

            $status = PHCXOperations::ResponseStatus($phcxOps->update("StWS", $ststamp, "ref", $ref));
            if ($status[0] == 'nok') { return $status; }
            $status = PHCXOperations::ResponseStatus($phcxOps->update("StWS", $ststamp, "design", $designation));
            if ($status[0] == 'nok') { return $status; }
            $status = PHCXOperations::ResponseStatus($phcxOps->update("StWS", $ststamp, "desctec", $longDesc));
            if ($status[0] == 'nok') { return $status; }
            $status = PHCXOperations::ResponseStatus($phcxOps->update("StWS", $ststamp, "tipodesc", $obs));
            if ($status[0] == 'nok') { return $status; }
            $status = PHCXOperations::ResponseStatus($phcxOps->update("StWS", $ststamp, "codigo", $ean));
            if ($status[0] == 'nok') { return $status; }
            $status = PHCXOperations::ResponseStatus($phcxOps->update("StWS", $ststamp, "epv1iva", $tax));
            if ($status[0] == 'nok') { return $status; }
            $status = PHCXOperations::ResponseStatus($phcxOps->update("StWS", $ststamp, "epv1", $price));
            if ($status[0] == 'nok') { return $status; }
            $status = PHCXOperations::ResponseStatus($phcxOps->update("StWS", $ststamp, "quantity", $stock));

            $phcxOps->logout();

            return $status;
        } else {
            // new product
            $result = $phcxOps->newInstance("StWS");

            $result['result'][0]['ref'] = $ref;
            $result['result'][0]['design'] = $designation;
            $result['result'][0]['desctec'] = $longDesc;
            $result['result'][0]['tipodesc'] = $obs;
            $result['result'][0]['codigo'] = $ean;
            $result['result'][0]['epv1iva'] = $tax;
            $result['result'][0]['epv1'] = $price;
            $result['result'][0]['quantity'] = $stock;

			/*
            if (self::saveProductCategory($phcxOps, $category)[0] == "ok") {
                $result['result'][0]['familia'] = $category;
                $result['result'][0]['faminome'] = $category;
            }
			*/


            $result = $phcxOps->save("StWS", $result['result'][0]);

            $phcxOps->logout();

            return PHCXOperations::ResponseStatus($result);
        }
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
        $obs        = "PHCX Connector insert";
        $isService  = isset($product->is_virtual) ? $product->is_virtual : '0';
        $stock      = (int)$product->getQuantity($product->id);
        $active     = isset($product->active) ? $product->active : '1';
        $shortDesc  = isset($product->description_short) ? utf8_encode(strip_tags(ProductToPHCX::stringOrArray($product->description_short))) : 'N/A';
        $longDesc   = isset($product->description) ? utf8_encode(strip_tags(ProductToPHCX::stringOrArray($product->description))) : 'N/A';
        $price      = isset($product->price) ? $product->price : '';
        $vendorRef  = isset($product->supplier_name) ? $product->supplier_name : 'N/A';
        $ean        = isset($product->ean13) ? $product->ean13 : '';
        $category   = isset($product->category) ? $product->category : '';

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
            $ean,
            $category
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

    // Save category to PHCX if it does not exists
    private static function saveProductCategory($phcxOps, $category) {

        $response = $phcxOps->query("StfamiWS", array(array('column' => 'ref', 'value' => $category)));
        $status = PHCXOperations::ResponseStatus($response);
        if ($status == 'nok') { return $status; }

        if (count($response['result']) == 0) {
            // new product
            $result = $phcxOps->newInstance("StfamiWS");
            $result['result'][0]['ref'] = $category;
            $result['result'][0]['nome'] = $category;
            $result = $phcxOps->save("StfamiWS", $result['result'][0]);
            return PHCXOperations::ResponseStatus($result);
        }

        return array('ok', '');
    }

}
