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
 *  @author    Majoinfa - Sociedade Unipessoal Lda <info@majoinfa.pt>
 *  @copyright 2016-2021 Majoinfa - Sociedade Unipessoal Lda
 *  @license   LICENSE.txt
 */

class OrderToPTInvoice extends ModuleCore
{

    /**
     * @param $id_order
     * @param $from
     * @return array
     */
    public static function sendOrderToPTInvoice($id_order, $from)
    {


        $datetime = new DateTime();
        $datetime->modify('+ 1 Hour');
        $horacarga = $datetime->format('H:i:s');

        if (ValidateCore::isLoadedObject($order = new OrderCore($id_order))) {

            $getDiscounts = $order->getCartRules();
            if (($getDiscounts) && ($from == 'hookOrderConfirmation')) {
                return false;
            }
            if ($from == 'hookDisplayAdminOrder') {

                $getDocTypeShip = Tools::getValue('PTInvoice_SHIP_DOC_TYPE');

            } else {

                $getDocTypeShip = ConfigurationCore::get('PTInvoice_SHIP_DOC_TYPE');
            }

            // se nao estiver configurada transportadora no presta
            $shipping_reference = ConfigurationCore::get('PTInvoice_SHIPPINGCOST');
            if (empty($shipping_reference)) {
                return array("nok",
                    "Aten&ccedil;&atilde;o transportadora n&atilde;o se encontra 
                    configurada no PTInvoice Connector! Encomenda n&atilde;o 
                    sincronizada!"
                );
            }

            $ptinvoiceOps = new PTInvoiceOperations();
            $response = $ptinvoiceOps->login();
            if ($response[0] == "nok") {
                return $response;
            }

            // se transportadora do presta nao for igual no PTInvoice
            $result = $ptinvoiceOps->query(
                "StWS",
                array(array(
                    'column' => 'ref',
                    'value' => $shipping_reference))
            );
            if (count($result['result']) == 0) {
                return array("nok",
                    "Aten&ccedil;&atilde;o transportadora \"$shipping_reference\" 
                    n&atilde;o se encontra configurada no PHCX! Encomenda 
                    n&atilde;o sincronizada!"
                );
            }

            //$getDocTypeInv  = Tools::getValue('PTInvoice_INV_DOC_TYPE');
            $address_invoice = new AddressCore($order->id_address_invoice);
            //$address_delivery = new AddressCore($order->id_address_delivery);

            // upsert customer
            $response = ClientToPTInvoice::saveByIdAddress(
                $order->id_address_invoice
            );
            $status = PTInvoiceOperations::ResponseStatus($response);
            if ($status[0] == 'nok') {
                return null;
            }

            $vat_number = isset($address_invoice->vat_number)
                ? $address_invoice->vat_number : '' ;

            $response = $ptinvoiceOps->login();
            if ($response[0] == "nok") {
                return $response;
            }

            // client exists
            $client = $ptinvoiceOps->query(
                "ClWS",
                array(array('column' => 'ncont', 'value' => $vat_number))
            );
            $status = PTInvoiceOperations::ResponseStatus($client);
            if ($status[0] == 'nok') {
                return $status;
            }

            $newFt = $ptinvoiceOps->newInstance(
                "FtWS", $params = array( 'ndos' => $getDocTypeShip)
            );

            $status = PTInvoiceOperations::ResponseStatus($newFt);
            if ($status[0] == 'nok') {
                return $status;
            }

            // get stamp
            $IdFtStamp = $newFt['result'][0]['ftstamp'];

            // add products
            if (count($newFt['result']) == 1) {

                if (!$cartProducts = $order->getCartProducts()) {
                    return false;
                }

                foreach ($cartProducts as $key => $cartProduct) {

                    // upsert product
                    ProductToPTInvoice::saveByIdProduct(
                        $ptinvoiceOps, $cartProduct['product_id']
                    );

                    $product_reference = isset($cartProduct['product_reference']) ?
                        $cartProduct['product_reference'] : 'N/A';

                    $newFt = $ptinvoiceOps->sendOperation(
                        "FtWS",
                        "addNewFIsByRef",
                        $params = array(
                            'IdFtStamp' => $IdFtStamp,
                            'refsIds' => '["' . $product_reference . '"]',
                            'fiStampEditing' => "")
                    );

                    $status = PTInvoiceOperations::ResponseStatus($newFt);
                    if ($status[0] == 'nok') {
                        return $status;
                    }

                    $product_quantity = isset($cartProduct['product_quantity']) ?
                        $cartProduct['product_quantity'] : '0';
                    $product_price = isset($cartProduct['unit_price_tax_incl']) ?
                        $cartProduct['unit_price_tax_incl'] : '0';

                    //Quantity and Price of FT
                    $newFt['result'][0]['fis'][$key]['qtt'] = $product_quantity;
                    $newFt = $ptinvoiceOps->sendOperation(
                        "FtWS",
                        "actEntity",
                        $params = array(
                            'entity' => Tools::jsonEncode($newFt['result'][0]),
                            'code' => 0,
                            'newValue' => Tools::jsonEncode('[]'))
                    );
                    $status = PTInvoiceOperations::ResponseStatus($newFt);
                    if ($status[0] == 'nok') {
                        return $status;
                    }

                    $newFt['result'][0]['fis'][$key]['epv'] = $product_price;
                    $newFt = $ptinvoiceOps->sendOperation(
                        "FtWS",
                        "actEntity",
                        $params = array(
                            'entity' => Tools::jsonEncode($newFt['result'][0]),
                            'code' => 0,
                            'newValue' => Tools::jsonEncode('[]'))
                    );
                    $status = PTInvoiceOperations::ResponseStatus($newFt);
                    if ($status[0] == 'nok') {
                        return $status;
                    }

                    $newFt['result'][0]['fis'][$key]['ivaincl'] = true;
                    $newFt = $ptinvoiceOps->sendOperation(
                        "FtWS",
                        "actEntity",
                        $params = array(
                            'entity' => Tools::jsonEncode($newFt['result'][0]),
                            'code' => 0,
                            'newValue' => Tools::jsonEncode('[]'))
                    );
                    $status = PTInvoiceOperations::ResponseStatus($newFt);
                    if ($status[0] == 'nok') {
                        return $status;
                    }

                }

                // sync shipping
                $shipping = $order->getShipping();

                if ($shipping[0]['shipping_cost_tax_incl'] != "0.000000") {

                    $newFt = $ptinvoiceOps->sendOperation(
                        "FtWS",
                        "addNewFIsByRef",
                        $params = array(
                            'IdFtStamp' => $IdFtStamp,
                            'refsIds' => '["' . $shipping_reference . '"]',
                            'fiStampEditing' => "")
                    );
                    $status = PTInvoiceOperations::ResponseStatus($newFt);
                    if ($status[0] == 'nok') {
                        return $status;
                    }

                    //Quantity of FT
                    $newFt['result'][0]['fis'][count($cartProducts)]['qtt'] = 1;
                    $newFt = $ptinvoiceOps->sendOperation(
                        "FtWS",
                        "actEntity",
                        $params = array(
                            'entity' => Tools::jsonEncode($newFt['result'][0]),
                            'code' => 0,
                            'newValue' => Tools::jsonEncode('[]'))
                    );
                    $status = PTInvoiceOperations::ResponseStatus($newFt);
                    if ($status[0] == 'nok') {
                        return $status;
                    }

                    // price
                    $newFt['result'][0]['fis'][count($cartProducts)]['epv']
                        = $shipping[0]['shipping_cost_tax_incl'];
                    $newFt = $ptinvoiceOps->sendOperation(
                        "FtWS",
                        "actEntity",
                        $params = array(
                            'entity' => Tools::jsonEncode($newFt['result'][0]),
                            'code' => 0,
                            'newValue' => Tools::jsonEncode('[]'))
                    );
                    $status = PTInvoiceOperations::ResponseStatus($newFt);
                    if ($status[0] == 'nok') {
                        return $status;
                    }

                    // inclui iva
                    $newFt['result'][0]['fis'][count($cartProducts)]['ivaincl'] = true;
                    $newFt = $ptinvoiceOps->sendOperation(
                        "FtWS",
                        "actEntity",
                        $params = array(
                            'entity' => Tools::jsonEncode($newFt['result'][0]),
                            'code' => 0,
                            'newValue' => Tools::jsonEncode('[]'))
                    );
                    $status = PTInvoiceOperations::ResponseStatus($newFt);
                    if ($status[0] == 'nok') {
                        return $status;
                    }
                }

                //Associate client to FT
                $newFt['result'][0]['no'] = $client['result'][0]['no'];

                //Eliminate financial discount of client
                $newFt['result'][0]['efinv'] = 0;
                $newFt['result'][0]['fin'] = 0;
                $newFt['result'][0]['hcarga'] = $horacarga;
                $newFt['result'][0]['local'] = "pt";
                $newFt['result'][0]['ivaincl'] = true;

                $newFt = $ptinvoiceOps->sendOperation(
                    "FtWS",
                    "actEntity",
                    $params = array(
                        'entity' => Tools::jsonEncode($newFt['result'][0]),
                        'code' => 0,
                        'newValue' => Tools::jsonEncode('[]'))
                );
                $status = PTInvoiceOperations::ResponseStatus($newFt);
                if ($status[0] == 'nok') {
                    return $status;
                }

                $result = $ptinvoiceOps->save("FtWS", $newFt['result'][0]);
                $status = PTInvoiceOperations::ResponseStatus($result);
                if ($status[0] == 'nok') {
                    return $status;
                }
                $ptinvoiceOps->logout();

                $getDiscounts = $order->getCartRules();
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