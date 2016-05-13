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

class ProductToPTInvoice extends Module
{

    /**
     * @param $ref
     * @param $designation
     * @param $shortName
     * @param $tax
     * @param $obs
     * @param $stock
     * @param $active
     * @param $shortDesc
     * @param $longDesc
     * @param $price
     * @param $vendorRef
     * @param $ean
     * @param $category
     * @return array|mixed
     */
    public static function upsertProduct(
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
    ) {

        // check if exists to always upsert
        $ptinvoiceOps = new PTInvoiceOperations();
        $response = $ptinvoiceOps->login();

        if ($response[0] == "nok")
            return $response;

        // product exists ?
        $response = $ptinvoiceOps->query("StWS", array(array('column' => 'ref', 'value' => $ref)));
        $status = PTInvoiceOperations::ResponseStatus($response);
        if ($status[0] == 'nok') { return $status; }

        if (count($response['result']) != 0) {
            // update existent product
            $ststamp = $response['result'][0]['ststamp'];

            $status = PTInvoiceOperations::ResponseStatus($ptinvoiceOps->update("StWS", $ststamp, "ref", '"'. $ref .'"'));              if ($status[0] == 'nok') { return $status; }
            $status = PTInvoiceOperations::ResponseStatus($ptinvoiceOps->update("StWS", $ststamp, "design", '"'. $designation .'"'));   if ($status[0] == 'nok') { return $status; }
            $status = PTInvoiceOperations::ResponseStatus($ptinvoiceOps->update("StWS", $ststamp, "desctec", '"'. $longDesc .'"'));     if ($status[0] == 'nok') { return $status; }
            $status = PTInvoiceOperations::ResponseStatus($ptinvoiceOps->update("StWS", $ststamp, "obs", '"'. $obs .'"'));              if ($status[0] == 'nok') { return $status; }
            $status = PTInvoiceOperations::ResponseStatus($ptinvoiceOps->update("StWS", $ststamp, "codigo", $ean));                     if ($status[0] == 'nok') { return $status; }
            $status = PTInvoiceOperations::ResponseStatus($ptinvoiceOps->update("StWS", $ststamp, "epv1", $price));                     if ($status[0] == 'nok') { return $status; }
            $status = PTInvoiceOperations::ResponseStatus($ptinvoiceOps->update("StWS", $ststamp, "epv1iva", $tax));                    if ($status[0] == 'nok') { return $status; } //taxaiva
            $status = PTInvoiceOperations::ResponseStatus($result = $ptinvoiceOps->update("StWS", $ststamp, "quantity", $stock));       if ($status[0] == 'nok') { return $status; }

            $result = $ptinvoiceOps->save("StWS", $result['result'][0]);

            $ptinvoiceOps->logout();
            return PTInvoiceOperations::ResponseStatus($result);
        } else {
            // new product
            $result = $ptinvoiceOps->newInstance("StWS",$params = array ('ndos' => 0));

            $result['result'][0]['ref'] = $ref;
            $result['result'][0]['design'] = $designation;
            $result['result'][0]['desctec'] = $longDesc;
            $result['result'][0]['tipodesc'] = $obs;
            $result['result'][0]['codigo'] = $ean;
            $result['result'][0]['epv1iva'] = $tax;
            $result['result'][0]['epv1'] = $price;
            $result['result'][0]['quantity'] = $stock;

            if (self::saveProductCategory($ptinvoiceOps, $category)[0] == "ok") {
                $result['result'][0]['familia'] = $category;
                $result['result'][0]['faminome'] = $category;
            }

            $result = $ptinvoiceOps->save("StWS", $result['result'][0]);

            $ptinvoiceOps->logout();
            return PTInvoiceOperations::ResponseStatus($result);
        }
    }

    /**
     * @param $product
     * @return array|mixed
     */
    public static function saveByProductObject($product)
    {
        $ref = isset($product->reference) ? $product->reference : 'N/A';
        $designation = isset($product->name) ? utf8_encode(ProductToPTInvoice::stringOrArray($product->name)) : 'N/A';
        $shortName = 'N/A';
        $taxValue = $product->getIdTaxRulesGroup();
        $tax      = isset($taxValue) ? (string)PTInvoiceConnectorGetValueByID::getTaxByRulesGroup($taxValue) : '';

        if ($tax == "") {
            // if empty set 0 tax
            $tax = "0";
        }
        $obs        = "Produto criado via PTInvoice Connector";
        $isService  = isset($product->is_virtual) ? $product->is_virtual : '0';
        $stock      = (int)$product->getQuantity($product->id);
        $active     = isset($product->active) ? $product->active : '1';
        $shortDesc  = isset($product->description_short) ? utf8_encode(strip_tags(ProductToPTInvoice::stringOrArray($product->description_short))) : 'N/A';
        $longDesc   = isset($product->description) ? utf8_encode(strip_tags(ProductToPTInvoice::stringOrArray($product->description))) : 'N/A';
        $price      = isset($product->price) ? $product->price : '';
        $vendorRef  = isset($product->supplier_name) ? $product->supplier_name : 'N/A';
        $ean        = isset($product->ean13) ? $product->ean13 : '';
        $category   = isset($product->category) ? $product->category : '';

        return ProductToPTInvoice::upsertProduct(
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

    /**
     * @param $idProduct
     * @return array|mixed|null
     */
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
            return ProductToPTInvoice::saveByProductObject($product);
        }
        return null;
    }

    /**
     * @param $data
     * @return array|mixed
     */
    public static function stringOrArray($data)
    {
        if (is_array($data)) {
            return reset($data);
        }
        return $data;
    }

    // Save category to PTInvoice if it does not exists
    /**
     * @param $ptinvoiceOps
     * @param $category
     * @return array
     */
    private static function saveProductCategory($ptinvoiceOps, $category)
    {

        $response = $ptinvoiceOps->query("StfamiWS", array(array('column' => 'ref', 'value' => $category)));
        $status = PTInvoiceOperations::ResponseStatus($response);
        if ($status == 'nok') { return $status; }

        if (count($response['result']) == 0) {
            // new product
            $result = $ptinvoiceOps->newInstance("StfamiWS",$params = array ('ndos' => 0));
            $result['result'][0]['ref'] = $category;
            $result['result'][0]['nome'] = $category;
            $result = $ptinvoiceOps->save("StfamiWS", $result['result'][0]);
            return PTInvoiceOperations::ResponseStatus($result);
        }

        return array('ok', '');
    }

}
