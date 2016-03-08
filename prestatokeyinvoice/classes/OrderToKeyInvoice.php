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

class OrderToKeyInvoice extends Module
{
    
    public static function sendShippingCost($session, $client, $shipping, $getDocTypeShip, $docID) {
        
        $shipping_reference = Configuration::get('PRESTATOKEYINVOICE_SHIPPINGCOST');
        if ($result = $client->getProduct("$session", "$shipping_reference")) {
            
            $Ref       = isset($result->{"DAT"}[0]->Ref) ? $result->{"DAT"}[0]->Ref : 'N/A';
            $Name      = isset($result->{"DAT"}[0]->Name) ? $result->{"DAT"}[0]->Name : 'N/A';
            $ShortName = isset($result->{"DAT"}[0]->ShortName) ? $result->{"DAT"}[0]->ShortName : 'N/A';
            $TAX       = isset($result->{"DAT"}[0]->TAX) ? $result->{"DAT"}[0]->TAX : 'N/A';
            $Obs       = isset($result->{"DAT"}[0]->Comment) ? $result->{"DAT"}[0]->Comment : 'N/A';
            $IsService = isset($result->{"DAT"}[0]->IsService) ? $result->{"DAT"}[0]->IsService : 'N/A';
            $HasStocks = isset($result->{"DAT"}[0]->HasStocks) ? $result->{"DAT"}[0]->HasStocks : 'N/A';
            $Active    = isset($result->{"DAT"}[0]->Active) ? $result->{"DAT"}[0]->Active : 'N/A';
            $ShortDescription = isset($result->{"DAT"}[0]->ShortDescription) ? $result->{"DAT"}[0]->ShortDescription : 'N/A';
            $LongDescription  = isset($result->{"DAT"}[0]->LongDescription) ? $result->{"DAT"}[0]->LongDescription : 'N/A';
            $VendorRef  = isset($result->{"DAT"}[0]->VendorRef) ? $result->{"DAT"}[0]->VendorRef : 'N/A';
            $Price     = isset($shipping[0]['shipping_cost_tax_excl']) ? $shipping[0]['shipping_cost_tax_excl'] : '0.000000';
            $EAN     = isset($result->{"DAT"}[0]->EAN) ? $result->{"DAT"}[0]->EAN : '';
        }
        // trasportadoras - este produto n existe no presta so no keyinvoice nao se pode usar o metodo por ID
        $result = ProductToKeyInvoice::upsertProduct("$Ref", "$Name", "$ShortName", "$TAX", "$Obs", "$IsService", "$HasStocks", "$Active", "$ShortDescription", "$LongDescription", "$Price", "$VendorRef", "$EAN");
        if (isset($result) && $result[0] != '1')
        {
            return $result;
        }
        // custo trasportadoras
        $result = $client->insertDocumentLine("$session", "$docID", "$getDocTypeShip", "$Ref", "1", "", "", "", "");
        if (isset($result) && $result[0] != '1')
        {
            return $result;
        }
        return $result;
    }
    
    public static function sendOrderToKeyInvoice($id_order, $from)
    {
        if (Validate::isLoadedObject($order = new Order($id_order))) {

                if (!$client = ConfigsValidation::APIWSClient())
                    return false;

                if (!$session = ConfigsValidation::APIWSSession($client, 'OrderToKeyInvoice'))
                    return false;
                
                if ($from == 'hookDisplayAdminOrder') {
                    
                    $getDocTypeShip = Tools::getValue('PRESTATOKEYINVOICE_SHIP_DOC_TYPE');
                    
                } else {
      
                    $getDocTypeShip = Configuration::get('PRESTATOKEYINVOICE_SHIP_DOC_TYPE');
                }

                //$getDocTypeInv  = Tools::getValue('PRESTATOKEYINVOICE_INV_DOC_TYPE');
                $address_invoice = new AddressCore($order->id_address_invoice);

                // upsert customer
                $result = ClientToKeyInvoice::saveByIdAddress($order->id_address_invoice);
                if (isset($result) && $result[0] != '1')
                {
                    return $result;
                }

                $vat_number = $address_invoice->vat_number;

                $order_reference = $order->reference;
                
                // create document
                $result = $client->insertDocumentHeader("$session", "$vat_number", "$getDocTypeShip", "","","", "", "", "", "$order_reference"); 
                $docID = $result[1];
                if (isset($result) && $result[0] != '1')
                {
                    return $result;
                }

                // add products
                if ($result[0] == '1') {

                    // produtos
                    $cartProducts = $order->getCartProducts();
                    foreach ($cartProducts as $cartProduct) {
                        
                        $OrderPrice = $cartProduct['product_price'];
                        $result = ProductToKeyInvoice::saveByIdProduct($cartProduct['product_id']);
                        //$result = ProductToKeyInvoice::saveByIdProduct($cartProduct['product_id'],$OrderPrice);
                        //$tax_rate = PrestaToKeyInvoiceGetValueByID::getTaxByID($cartProduct['id_tax_rules_group']);
                        //$result = ProductToKeyInvoice::upsertProduct($cartProduct['product_reference'], $cartProduct['product_name'], "N/A", "$tax_rate", "Produto inserido via PrestaToKeyinvoice", $cartProduct['is_virtual'], "1", $cartProduct['active'], "N/A", "N/A", $cartProduct['product_price'], "N/A", $cartProduct['ean13']);
                        if (isset($result) && $result[0] != '1')
                        {
                            return $result;
                        }
                        $result = $client->insertDocumentLine("$session", "$docID", "$getDocTypeShip", $cartProduct['product_reference'], $cartProduct['product_quantity'], "", "", "", "");
                        if (isset($result) && $result[0] != '1')
                        {
                            return $result;
                        }
                    }

                    // sync shipping
                    // retira o produto criado como transporta no lado do key
                    $shipping = $order->getShipping();
                    $result = OrderToKeyInvoice::sendShippingCost($session, $client, $shipping, $getDocTypeShip, $docID);
                    if (isset($result) && $result[0] != '1')
                    {
                        return $result;
                    }
                     
                }
                return $result;

        }
    }
}