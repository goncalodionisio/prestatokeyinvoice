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
     * @param $ptinvoiceOps
     * @param $ref
     * @param $designation
     * @param $shortName
     * @param $tax
     * @param $obs
     * @param $stock
     * @param $active
     * @param $shortDesc
     * @param $longDesc
     * @param $cost_price
     * @param $vendorRef
     * @param $ean
     * @param $category
     * @param $isService
     * @param $manufacturer
     * @param $sell_price
     * @return array|mixed
     * @internal param $price
     */
    public static function upsertProduct($ptinvoiceOps,
        $ref,
        $designation,
        $shortName,
        $tax,
        $obs,
        $stock,
        $active,
        $shortDesc,
        $longDesc,
        $cost_price,
        $vendorRef,
        $ean,
        $category,
        $isService,
        $manufacturer,
        $sell_price
    ) {

        // product exists ?
        $response = $ptinvoiceOps->query("StWS", array(array('column' => 'ref', 'value' => $ref)));
        $status = PTInvoiceOperations::ResponseStatus($response);
        if ($status[0] == 'nok') { return $status; }

        if (count($response['result']) != 0) {
            // update existent product
            $ststamp = $response['result'][0]['ststamp'];


            $status = PTInvoiceOperations::ResponseStatus($result = $ptinvoiceOps->update("StWS", $ststamp, "ref", '"'. $ref .'"'));
            if ($status[0] == 'nok') { return $status; }
            $status = PTInvoiceOperations::ResponseStatus($result = $ptinvoiceOps->update("StWS", $ststamp, "design", '"'. utf8_decode($designation) .'"'));
            if ($status[0] == 'nok') { return $status; }
            $status = PTInvoiceOperations::ResponseStatus($result = $ptinvoiceOps->update("StWS", $ststamp, "desctec", '"'. utf8_decode($shortDesc) .'\n'. utf8_decode($longDesc) .'"'));
            if ($status[0] == 'nok') { return $status; }
            $status = PTInvoiceOperations::ResponseStatus($result = $ptinvoiceOps->update("StWS", $ststamp, "obs", '"'. $obs .'"'));
            if ($status[0] == 'nok') { return $status; }
            $status = PTInvoiceOperations::ResponseStatus($result = $ptinvoiceOps->update("StWS", $ststamp, "codigo", $ean));
            if ($status[0] == 'nok') { return $status; }
            $status = PTInvoiceOperations::ResponseStatus($result = $ptinvoiceOps->update("StWS", $ststamp, "usr1", '"'. $manufacturer .'"'));
            if ($status[0] == 'nok') { return $status; }
            $status = PTInvoiceOperations::ResponseStatus($result = $ptinvoiceOps->update("StWS", $ststamp, "epcusto", $cost_price));
            if ($status[0] == 'nok') { return $status; } //preço de custo
            $status = PTInvoiceOperations::ResponseStatus($result = $ptinvoiceOps->update("StWS", $ststamp, "epv1", $sell_price));
            if ($status[0] == 'nok') { return $status; } //preço de sell

            $ivaStatus = self::getPhcFxIva($ptinvoiceOps, $tax);
            if ($ivaStatus[0] == 'nok') { return $ivaStatus; }

            $status = PTInvoiceOperations::ResponseStatus($result = $ptinvoiceOps->update("StWS", $ststamp, "tabiva", $ivaStatus[1]));
            if ($status[0] == 'nok') { return $status; }
            $status = PTInvoiceOperations::ResponseStatus($result = $ptinvoiceOps->update("StWS", $ststamp, "taxaiva", $tax));
            if ($status[0] == 'nok') { return $status; } //taxaiva
            # $status = PTInvoiceOperations::ResponseStatus($result = $ptinvoiceOps->update("StWS", $ststamp, "quantity", $stock));
            # if ($status[0] == 'nok') { return $status; }
            $status = PTInvoiceOperations::ResponseStatus($result = $ptinvoiceOps->update("StWS", $ststamp, "inactivo", ($active == '1' ? false : true)));
            if ($status[0] == 'nok') { return $status; }
            $status = PTInvoiceOperations::ResponseStatus($result = $ptinvoiceOps->update("StWS", $ststamp, "stns", (($isService == '1') ? true : false)));
            if ($status[0] == 'nok') { return $status; }

            $catResult = self::saveProductCategory($ptinvoiceOps, $category);
            if (isset($catResult) && $catResult[0] == "ok") {
                $status = PTInvoiceOperations::ResponseStatus($result = $ptinvoiceOps->update("StWS", $ststamp, "familia", '"'. $category .'"'));
                if ($status[0] == 'nok') { return $status; }
                $status = PTInvoiceOperations::ResponseStatus($result = $ptinvoiceOps->update("StWS", $ststamp, "faminome", '"'. $category .'"'));
                if ($status[0] == 'nok') { return $status; }
            }

            $result = $ptinvoiceOps->save("StWS", $result['result'][0]);

            return PTInvoiceOperations::ResponseStatus($result);
        } else {
            // new product
            $result = $ptinvoiceOps->newInstance("StWS",$params = array ('ndos' => 0));

            $result['result'][0]['ref'] = $ref;
            $result['result'][0]['design'] = $designation;
            $result['result'][0]['desctec'] = $shortDesc .'\n'. $longDesc;
            $result['result'][0]['obs'] = $obs;
            $result['result'][0]['codigo'] = $ean;
            # $result['result'][0]['quantity'] = $stock;
            $result['result'][0]['epcusto'] = $cost_price;
            $result['result'][0]['epv1'] = $sell_price;

            $ivaStatus = self::getPhcFxIva($ptinvoiceOps, $tax);
            if ($ivaStatus[0] == 'nok') { return $ivaStatus; }

            $result['result'][0]['tabiva'] = $ivaStatus[1];
            $result['result'][0]['taxaiva'] = $tax;
            $result['result'][0]['usr1'] = $manufacturer;
            $result['result'][0]['inactivo'] = ($active == '1' ? false : true);
            $result['result'][0]['stns'] = ($isService == '1' ? true : false);

			$catResult = self::saveProductCategory($ptinvoiceOps, $category);
			
            if (isset($catResult) && $catResult[0] == "ok") {
                $result['result'][0]['familia'] = $category;
                $result['result'][0]['faminome'] = $category;
            }

            $result = $ptinvoiceOps->save("StWS", $result['result'][0]);

            return PTInvoiceOperations::ResponseStatus($result);
        }
    }

    /**
     * @param $product
     * @return array|mixed
     */
    public static function saveByProductObject($ptinvoiceOps, $product)
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
        $sell_price      = isset($product->price) ? $product->price : '0'; // preco de venda sem iva
        $cost_price      = isset($product->wholesale_price) ? $product->wholesale_price : '0'; // preco de compra sem iva
        $vendorRef  = isset($product->supplier_name) ? $product->supplier_name : 'N/A';
        $ean        = isset($product->ean13) ? $product->ean13 : '';
        $category   = isset($product->category) ? $product->category : '';

        $manufacturer = $product->getWsManufacturerName();
        if (!isset($manufacturer)) {
            $manufacturer = '';
        }

        return ProductToPTInvoice::upsertProduct($ptinvoiceOps,
            $ref,
            $designation,
            $shortName,
            $tax,
            $obs,
            $stock,
            $active,
            $shortDesc,
            $longDesc,
            $cost_price,
            $vendorRef,
            $ean,
            $category,
            $isService,
            $manufacturer,
            $sell_price
        );
    }

    /**
     * @param $idProduct
     * @return array|mixed|null
     */
    public static function saveByIdProduct($ptinvoiceOps, $idProduct)
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
            return ProductToPTInvoice::saveByProductObject($ptinvoiceOps, $product);
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

    private static function getPhcFxIva($ptinvoiceOps, $value)
    {
        // product exists ?
        $response = $ptinvoiceOps->query("IVAWS", array(array('column' => 'taxa', 'value' => $value)));
        $status = PTInvoiceOperations::ResponseStatus($response);

        if ($status[0] == 'nok') { return $status; }

        if (count($response['result']) != 0) {
            $ivaCode = $response['result'][0]['codigo'];
            $ivaDataIni = strtotime($response['result'][0]['dataini']);

            $response = $ptinvoiceOps->query("IVAWS", array(array('column' => 'codigo', 'value' => $ivaCode)));

			foreach($response['result'] as $item) {
                if ($ivaDataIni < strtotime($item['dataini']) && strval($value) == strval($item['taxa'])) {
                    $ivaDataIni = strtotime($item['dataini']);
                }
            }
			
            foreach($response['result'] as $item) {
                if ($ivaDataIni < strtotime($item['dataini'])) {
                    return array('nok', 'VAT value not defined');
                }
            }

            return array('ok', $response['result'][0]['codigo']);

        } else {
            return array('nok', 'VAT does not exists in PHCFX');
        }
    }


}
