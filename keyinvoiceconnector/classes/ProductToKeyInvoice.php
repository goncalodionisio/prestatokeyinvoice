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
 
class ProductToKeyInvoice extends Module
{


    /**
     * @param $ref
     * @param $designation
     * @param $shortName
     * @param $tax
     * @param $obs
     * @param $isService
     * @param $hasStocks
     * @param $active
     * @param $shortDesc
     * @param $longDesc
     * @param $price
     * @param $vendorRef
     * @param $ean
     * @return array|bool
     */
    public static function upsertProduct(
        $ref,
        $designation,
        $shortName,
        $tax,
        $obs,
        $isService,
        $hasStocks,
        $active,
        $shortDesc,
        $longDesc,
        $price,
        $vendorRef,
        $ean
    ) {

        /************************************************************************
         * calculate price with tax
         ************************************************************************/
        if (ConfigsValidation::isPricePlusTax()) {
            $price = ($tax/100)*$price + $price;
        }
        /*************************************************************************/

        /************************************************************************
        * set debug
         ************************************************************************/
        $debug = "";
        //$debug .= "session:" . "$session" . " | ";
        $debug .= "ref:" . "$ref" . ' | ';
        //$debug .= "designation" . "$designation" . " | ";
        //$debug .= "shortName" . "$shortName" . " | ";
        $debug .= "tax:" . "$tax" . " | ";
        //$debug .= "obs" . "$obs" . " | ";
        $debug .= "serviço:" . "$isService" . " | ";
        $debug .= "stock:" . "$hasStocks" . " | ";
        $debug .= "active:" . "$active" . " | ";
        //$debug .= "shortDesc" . "$shortDesc" . " | ";
        //$debug .= "longDesc" . "$longDesc" . " | ";
        $debug .= "preço" . "$price" . " | ";
        $debug .= "vendor ref:" . "$vendorRef" . " | ";
        $debug .= "ean:" . "$ean";

        ConfigsValidation::setDebugValue($debug);
        /*************************************************************************/

        if (!$client = ConfigsValidation::APIWSClient()) {
            return false;
        }

        if (!$session = ConfigsValidation::APIWSSession($client, 'ProductToKeyInvoice')) {
            return false;
        }

        // check if keyinvoice is master
        $keyinvoice_master = ConfigsValidation::getKeyMasterProducts();

        // check if exists to always upsert
        $productExists = $client->productExists("$session", "$ref");
        $result = $productExists[0];


        if ((int)$result === 1 && (int)$keyinvoice_master === 1) {
            $result = array("1");
        } elseif ((int)$result === 1 && (int)$keyinvoice_master === 0) {
            $result = $client->updateProduct(
                "$session",
                "$ref",
                "$designation",
                "$shortName",
                "$tax",
                "$obs",
                "$isService",
                "$hasStocks",
                "$active",
                "$shortDesc",
                "$longDesc",
                "$price",
                "$vendorRef",
                "$ean"
            );
        } else {
            $result = $client->insertProduct(
                "$session",
                "$ref",
                "$designation",
                "$shortName",
                "$tax",
                "$obs",
                "$isService",
                "$hasStocks",
                "$active",
                "$shortDesc",
                "$longDesc",
                "$price",
                "$vendorRef",
                "$ean"
            );
        }
        return $result;
    }

    public static function saveByProductObject($product)
    {

        $ref = isset($product->reference) ? $product->reference : 'N/A';
        $designation = isset($product->name) ? utf8_encode(ProductToKeyInvoice::stringOrArray($product->name)) : 'N/A';

        $shortName = 'N/A';

        $taxValue = $product->getIdTaxRulesGroup();
        $tax = isset($taxValue) ? (string)KeyInvoiceConnectorGetValueByID::getTaxByRulesGroup($taxValue) : '';

        if ($tax == "") {
            // if empty set 0 tax
            $tax = "0";
        }
        $obs = "Produto inserido via KeyInvoice Connector";
        $isService = isset($product->is_virtual) ? $product->is_virtual : '0';
        $hasStocks = isset($product->is_virtual) ? ((int)$product->getQuantity($product->id) == 0 ? '0' : '1') : '0';
        $active = isset($product->active) ? $product->active : '1';
        $shortDesc = isset($product->description_short) ? utf8_encode(strip_tags(ProductToKeyInvoice::stringOrArray($product->description_short))) : 'N/A';
        $longDesc = isset($product->description) ? utf8_encode(strip_tags(ProductToKeyInvoice::stringOrArray($product->description))) : 'N/A';
        $price = isset($product->price) ? $product->price : '';
        $vendorRef = isset($product->supplier_name) ? $product->supplier_name : 'N/A';
        $ean = isset($product->ean13) ? $product->ean13 : '';

        return ProductToKeyInvoice::upsertProduct(
            $ref,
            $designation,
            $shortName,
            $tax,
            $obs,
            $isService,
            $hasStocks,
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
        )
        ) {
            return ProductToKeyInvoice::saveByProductObject($product);
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
