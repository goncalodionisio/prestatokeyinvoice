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

class OrderToPTInvoice extends Module
{
    public static function sendShippingAddr(
        $session,
        $client,
        $docID,
        $getDocTypeShip,
        $order_reference,
        $address_delivery
    ) {
        $opt_deliveryLocation_address    = isset($address_delivery->address1) ? $address_delivery->address1 : 'N/A' ;
        $opt_deliveryLocation_postalCode = isset($address_delivery->postcode) ? $address_delivery->postcode : 'N/A' ;
        $opt_deliveryLocation_city       = isset($address_delivery->city) ? $address_delivery->city : 'N/A' ;

        if ($getDocTypeShip == '15') {

            $result = $client->insertDocumentHeader_additionalInfo(
                "$session",
                "$docID",
                "$getDocTypeShip",
                "",
                "$order_reference",
                "",
                "",
                "",
                "$opt_deliveryLocation_address $opt_deliveryLocation_postalCode $opt_deliveryLocation_city",
                "",
                "$opt_deliveryLocation_address $opt_deliveryLocation_postalCode $opt_deliveryLocation_city",
                "$opt_deliveryLocation_postalCode",
                "$opt_deliveryLocation_city",
                ""
            );
            return $result;
        }
        return array(1, "Always ok");

    }

    public static function sendShippingCost($session, $client, $shipping, $getDocTypeShip, $docID)
    {

        $shipping_reference = Configuration::get('PTInvoice_SHIPPINGCOST');

        if ($result = $client->getProduct("$session", "$shipping_reference")) {

            $Ref       = isset($result->{"DAT"}[0]->Ref) ? $result->{"DAT"}[0]->Ref : 'N/A';
            $Name      = isset($result->{"DAT"}[0]->Name) ? $result->{"DAT"}[0]->Name : 'N/A';
            $ShortName = isset($result->{"DAT"}[0]->ShortName) ? $result->{"DAT"}[0]->ShortName : 'N/A';
            $TAX       = isset($result->{"DAT"}[0]->TAX) ? $result->{"DAT"}[0]->TAX : 'N/A';
            $Obs       = isset($result->{"DAT"}[0]->Comment) ? $result->{"DAT"}[0]->Comment : 'N/A';
            $IsService = isset($result->{"DAT"}[0]->IsService) ? $result->{"DAT"}[0]->IsService : 'N/A';
            $HasStocks = isset($result->{"DAT"}[0]->HasStocks) ? $result->{"DAT"}[0]->HasStocks : 'N/A';
            $Active    = isset($result->{"DAT"}[0]->Active) ? $result->{"DAT"}[0]->Active : 'N/A';
            $ShortDescription = isset($result->{"DAT"}[0]->ShortDescription) ?
                $result->{"DAT"}[0]->ShortDescription : 'N/A';
            $LongDescription  = isset($result->{"DAT"}[0]->LongDescription) ?
                $result->{"DAT"}[0]->LongDescription : 'N/A';
            $VendorRef  = isset($result->{"DAT"}[0]->VendorRef) ? $result->{"DAT"}[0]->VendorRef : 'N/A';
            $Price     = isset($shipping[0]['shipping_cost_tax_excl']) ?
                $shipping[0]['shipping_cost_tax_excl'] : '0.000000';
            $EAN     = isset($result->{"DAT"}[0]->EAN) ? $result->{"DAT"}[0]->EAN : '';
        }

        // trasportadoras - este produto n existe no presta so no PTInvoice nao se pode usar o metodo por ID
        $result = ProductToPTInvoice::upsertProduct(
            "$Ref",
            "$Name",
            "$ShortName",
            "$TAX",
            "$Obs",
            "$IsService",
            "$HasStocks",
            "$Active",
            "$ShortDescription",
            "$LongDescription",
            "$Price",
            "$VendorRef",
            "$EAN"
        );
        if (isset($result) && $result[0] != '1') {
            return $result;
        }
        // custo trasportadoras
        $result = $client->insertDocumentLine("$session", "$docID", "$getDocTypeShip", "$Ref", "1", "", "", "", "");
        if (isset($result) && $result[0] != '1') {
            return $result;
        }
        return $result;
    }

    public static function sendOrderToPTInvoice($id_order, $from)
    {
        if (Validate::isLoadedObject($order = new Order($id_order))) {

            $getDiscounts = $order->getDiscounts();
            if (($getDiscounts) && ($from == 'hookOrderConfirmation')) {
                return false;
            }
            if ($from == 'hookDisplayAdminOrder') {

                $getDocTypeShip = Tools::getValue('PTInvoice_SHIP_DOC_TYPE');

            } else {

                $getDocTypeShip = Configuration::get('PTInvoice_SHIP_DOC_TYPE');
            }

            // se nao estiver configurada transportadora no presta
            $shipping_reference = Configuration::get('PTInvoice_SHIPPINGCOST');
            if (empty($shipping_reference)) {
                return array(-969,
                    "Aten&ccedil;&atilde;o transportadora n&atilde;o se encontra 
                    configurada no PTInvoice Connector! Encomenda n&atilde;o sincronizada!"
                );
            }

            $ptinvoiceOps = new PTInvoiceOperations();
            $response = $ptinvoiceOps->login();

            if ($response[0] == "nok")
                return $response;


            // se transportadora do presta nao for igual no PTInvoice
            $result = $ptinvoiceOps->query("StWS", array(array('column' => 'ref', 'value' => $shipping_reference)));

            if (count($result['result']) == 0) {
                return array(-969,
                    "Aten&ccedil;&atilde;o transportadora \"$shipping_reference\" n&atilde;o se encontra 
                    configurada no PHCX! Encomenda n&atilde;o sincronizada!"
                );
            }

            //$getDocTypeInv  = Tools::getValue('PTInvoice_INV_DOC_TYPE');
            $address_invoice = new AddressCore($order->id_address_invoice);
            //$address_delivery = new AddressCore($order->id_address_delivery);

            // upsert customer
            $response = ClientToPTInvoice::saveByIdAddress($order->id_address_invoice);
            $status = PTInvoiceOperations::ResponseStatus($response);
            if ($status[0] == 'nok') {
                return null;
            }

            $vat_number = isset($address_invoice->vat_number) ? $address_invoice->vat_number : '' ;
            // $order_reference = isset($order->reference) ? $order->reference : 'N/A' ;

            $response = $ptinvoiceOps->login();
            if ($response[0] == "nok")
                return $response;

            // client exists
            $client = $ptinvoiceOps->query("ClWS", array(array('column' => 'ncont', 'value' => $vat_number)));
            $status = PTInvoiceOperations::ResponseStatus($client);
            if ($status[0] == 'nok') { return $status; }

            $newFt = $ptinvoiceOps->newInstance("FtWS",$params = array( 'ndos' => $getDocTypeShip));
            $status = PTInvoiceOperations::ResponseStatus($newFt);
            if ($status[0] == 'nok') { return $status; }

            // get stamp
            $IdFtStamp = $newFt['result'][0]['ftstamp'];

            // add products
            if (count($newFt['result']) == 1) {
                // produtos
                if (!$cartProducts = $order->getCartProducts()) {
                    return false;
                }
                foreach ($cartProducts as $cartProduct) {

                    // upsert product
                    ProductToPTInvoice::saveByIdProduct($ptinvoiceOps,$cartProduct['product_id']);

                    $product_reference = isset($cartProduct['product_reference']) ?
                        $cartProduct['product_reference'] : 'N/A';

                    $newFt = $ptinvoiceOps->sendOperation("FtWS", "addNewFIsByRef", $params = array('IdFtStamp' => $IdFtStamp, 'refsIds' => '["' . $product_reference . '"]', 'fiStampEditing' => ""));
                    $status = PTInvoiceOperations::ResponseStatus($newFt);
                    if ($status[0] == 'nok') { return $status; }
                    // $product_name = isset($cartProduct['product_name']) ? $cartProduct['product_name'] : 'N/A';
                    $product_quantity = isset($cartProduct['product_quantity']) ?
                        $cartProduct['product_quantity'] : '0';
                    $product_price = isset($cartProduct['product_price']) ? $cartProduct['product_price'] : '0';
                    $tax = PTInvoiceConnectorGetValueByID::getTaxByRulesGroup($cartProduct['id_tax_rules_group']);
                    $discount = '0';


                    //Quantity and Price of FT
                    $newFt['result'][0]['qtt'] = $product_quantity;
                    $newFt['result'][0]['epv'] = $product_price;


                }

                // sync shipping
                // retira o produto criado como transporta no lado do key
                $shipping = $order->getShipping();

                if ($shipping[0]['shipping_cost_tax_excl'] != "0.000000") {

                    $newFt = $ptinvoiceOps->sendOperation("FtWS", "addNewFIsByRef", $params = array('IdFtStamp' => $IdFtStamp, 'refsIds' => '["' . $shipping_reference . '"]', 'fiStampEditing' => ""));
                    $status = PTInvoiceOperations::ResponseStatus($newFt);
                    if ($status[0] == 'nok') { return $status; }
                    //Quantity and Price of FT
                    $newFt['result'][0]['qtt'] = 1;
                    $newFt['result'][0]['epv'] = $shipping[0]['shipping_cost_tax_excl'];
                    $newFt = $ptinvoiceOps->sendOperation("FtWS", "actEntity", $params = array('entity' => ToolsCore::jsonEncode($newFt['result'][0]), 'code' => 0, 'newValue' => ToolsCore::jsonEncode([])));
                    $status = PTInvoiceOperations::ResponseStatus($newFt);
                    if ($status[0] == 'nok') { return $status; }
                }

                //Associate client to FT
                $newFt['result'][0]['no'] = $client['result'][0]['no'];
                $newFt['result'][0]['desconto'] = 0;
                $newFt['result'][0]['desc2'] = 0;
                $newFt['result'][0]['desc3'] = 0;
                $newFt['result'][0]['desc4'] = 0;
                $newFt['result'][0]['desc5'] = 0;
                $newFt['result'][0]['desc6'] = 0;

                //Eliminate financial discount of client
                $newFt['result'][0]['efinv'] = 0;
                $newFt['result'][0]['fin'] = 0;

                $newFt = $ptinvoiceOps->sendOperation("FtWS", "actEntity", $params = array('entity' => ToolsCore::jsonEncode($newFt['result'][0]), 'code' => 0, 'newValue' => ToolsCore::jsonEncode([])));
                $status = PTInvoiceOperations::ResponseStatus($newFt);
                if ($status[0] == 'nok') { return $status; }

                $result = $ptinvoiceOps->save("FtWS", $newFt['result'][0]);
                $status = PTInvoiceOperations::ResponseStatus($result);
                if ($status[0] == 'nok') { return $status; }
                $ptinvoiceOps->logout();

                /*
                if ($address_delivery) {

                    $result = OrderToPTInvoice::sendShippingAddr(
                        $session,
                        $client,
                        $docID,
                        $getDocTypeShip,
                        $order_reference,
                        $address_delivery
                    );
                    if (isset($result) && $result[0] != '1') {
                        return $result;
                    }
                }
                */
                $getDiscounts = $order->getDiscounts();
                if ($getDiscounts) {
                    return array(-969,
                        "Aten&ccedil;&atilde;o h&aacute; descontos por sicronizar
                    nesta encomenda no PTInvoice Connector!"
                    );
                }

                return PTInvoiceOperations::ResponseStatus($result);
            }

        }
    }
}
