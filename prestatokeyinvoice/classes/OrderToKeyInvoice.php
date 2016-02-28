<?php

class OrderToKeyInvoice extends Module
{
	
	public static function sendOrderToKeyInvoice($id_order)
	{
		if (Validate::isLoadedObject($order = new OrderCore($id_order))) {
			
			
			if (Tools::isSubmit('process_sync_order'))
            {
				
				// API Webservice URL
                $url = "http://login.e-comercial.pt/API3_ws.php?wsdl";
                $kiapi_key = Configuration::get('PRESTATOKEYINVOICE_KIAPI');
		        $client = new SoapClient($url);
				
                // see if key is valid before update config
                $kiapi_auth =  $client->authenticate("$kiapi_key");
                $session = $kiapi_auth[1];
				
				$getDocTypeShip = Tools::getValue('PRESTATOKEYINVOICE_SHIP_DOC_TYPE');
			    //$getDocTypeInv  = Tools::getValue('PRESTATOKEYINVOICE_INV_DOC_TYPE');
				$address_invoice = new AddressCore($order->id_address_invoice);
				
				// upsert customer
				$result = ClientToKeyInvoice::saveByIdAddress($order->id_address_invoice);
		        if (isset($result) && $result[0] != '1')
		        {
			    	return $result;
	        	}

				$vat_number = $address_invoice->vat_number;
				$order_reference = 'PTKI_'.$order->reference;
				
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
					$shipping_reference = Configuration::get('PRESTATOKEYINVOICE_SHIPPINGCOST');
					$result=$client->getProduct("$session", "$shipping_reference");

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
				}
				return $result;
            }
			
		}
	}
}