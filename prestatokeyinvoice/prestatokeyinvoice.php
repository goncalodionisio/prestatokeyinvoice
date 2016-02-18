<?php


require('classes/ConfigsValidation.php');
require('classes/GetValueByID.php');

class PrestaToKeyInvoice extends Module
{

    public function __construct() {
        $this->name = 'prestatokeyinvoice';
        $this->tab = 'billing_invoicing';
        $this->version = '1.0.0';
        $this->author = 'Majoinfa, lda';
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Presta To KeyInvoice');
        $this->description = $this->l('Provides integration with Portuguese KeyInvoice billing system');
    }

    public function install()
    {
        // Call install parent method
        if (!parent::install())
            return false;

        // Execute module install SQL statements
        $sql_file = dirname(__FILE__).'/install/install.sql';
        if (!$this->loadSQLFile($sql_file))
            return false;

        if (!$this->registerHook('displayAdminCustomers') ||
            !$this->registerHook('displayAdminOrder') ||
            !$this->registerHook('actionProductSave') ||
            !$this->registerHook('actionValidateOrder') ||
            !$this->registerHook('displayAdminProductsExtra')
            )
            return false;

        // All went well!
        return true;
    }

    public function uninstall()
    {

        if (!parent::uninstall())
            return false;

        // Execute module install SQL statements
        $sql_file = dirname(__FILE__).'/install/uninstall.sql';
        if (!$this->loadSQLFile($sql_file))
            return false;
        // Delete configuration values
        Configuration::deleteByName(_DB_PREFIX_.'PTINVC_KIAPI');
        return true;
    }

    public function loadSQLFile($sql_file)
    {
        // Get install SQL file content
        $sql_content = Tools::file_get_contents($sql_file);

        // Replace prefix and store SQL command in array
        $sql_content = str_replace('PREFIX_', _DB_PREFIX_, $sql_content);
        $sql_requests = preg_split("/;\s*[\r\n]+/", $sql_content);

        // Execute each SQL statement
        $result = true;
        foreach($sql_requests as $request)
            if (!empty($request))
                $result &= Db::getInstance()->execute(trim($request));

        // Return result
        return $result;
    }

    ################################################ Config Start ##############################
    // Module configuration options
    public function processConfiguration()
    {
    	$env = 'out';
        if (Tools::isSubmit('ptinvc_intgr_form')) {
            // API Webservice URL
            $url = "http://login.e-comercial.pt/API3_ws.php?wsdl";
            $kiapi_key = Tools::getValue('kiapi_key');
            // try comunication with WS
            try {
	    
				if ($env == 'in') {
				    $client = new SoapClient($url, array(
				        'proxy_host' => 'gateway.zscaler.net',
				        'proxy_port' => '80',
				        'authentication' => SOAP_AUTHENTICATION_BASIC,
				        'stream_context' => stream_context_create(array(
				                'ssl' => array( 'verify_peer' => false,
				                        'verify_peer_name' => false,
				                        'allow_self_signed' => false)
				        ))
				    ));
				} else {
				
			        $client = new SoapClient($url);
				}
                // see if key is valid before update config
                if ($kiapi_key) {

                    $kiapi_auth =  $client->authenticate("$kiapi_key");
                    $result = $kiapi_auth[0];
                    if ($result == '1') {
                        Configuration::updateValue(_DB_PREFIX_.'PTINVC_KIAPI', $kiapi_key);
                        $this->context->smarty->assign('confirmation_key', 'ok');
                    } else {
                        Configuration::deleteByName(_DB_PREFIX_.'PTINVC_KIAPI');
                        $this->context->smarty->assign('no_confirmation_key', 'nok');
                    }

                } else {
                    // Delete configuration values
                    Configuration::deleteByName(_DB_PREFIX_.'PTINVC_KIAPI');
                    $this->context->smarty->assign('no_configuration_key', 'na');
                }

            } catch (Exception $e) {
                $this->context->smarty->assign('no_soap', 'nok');
            }
        }

        if (Tools::isSubmit('ptinvc_sync_form')) {
            // enable/disable products syncronization with keyinvoice
            $enable_products_sync = Tools::getValue('enable_products_sync');
            Configuration::updateValue('PRESTATOKEYINVOICE_PRODUCTS_SYNC', $enable_products_sync);

            // enable/disable clients syncronization with keyinvoice
            $enable_clients_sync = Tools::getValue('enable_clients_sync');
            Configuration::updateValue('PRESTATOKEYINVOICE_CLIENTS_SYNC', $enable_clients_sync);

            // enable/disable orders syncronization with keyinvoice
            $enable_orders_sync = Tools::getValue('enable_orders_sync');
            Configuration::updateValue('PRESTATOKEYINVOICE_ORDERS_SYNC', $enable_orders_sync);


            $this->context->smarty->assign('keyinvoice_sync_update', 'ok');
        }
    }

    public function assignConfiguration()
    {
        $kiapi_key = Configuration::get(_DB_PREFIX_.'PTINVC_KIAPI');
        $this->context->smarty->assign('kiapi_key', $kiapi_key);

        // enable/disable products syncronization with keyinvoice
        $enable_products_sync = Configuration::get('PRESTATOKEYINVOICE_PRODUCTS_SYNC');
        $this->context->smarty->assign('enable_products_sync', $enable_products_sync);

        // enable/disable clients syncronization with keyinvoice
        $enable_clients_sync = Configuration::get('PRESTATOKEYINVOICE_CLIENTS_SYNC');
        $this->context->smarty->assign('enable_clients_sync', $enable_clients_sync);

        // enable/disable orders syncronization with keyinvoice
        $enable_orders_sync = Configuration::get('PRESTATOKEYINVOICE_ORDERS_SYNC');
        $this->context->smarty->assign('enable_orders_sync', $enable_orders_sync);
    }

    public function getContent()
    {
        $this->processConfiguration();
        $this->assignConfiguration();
        return $this->display(__FILE__, 'getContent.tpl');
    }

    /* 
     * TODO restantes opcoes de configuracao do modulo 
     * ser possivel ligar desligar sincronismo de: 
     * produtos (DONE)
     * clientes (DONE)
     * encomendas (DONE)
     * */
    ##################################### Module Config End ##############################################

    // vai buscar reposta do webservice
    public function getWSResponse($result)
    {
    	
	   if ($message = Db::getInstance()->executeS('SELECT message FROM `'._DB_PREFIX_.'prestatokeyinvoice_response` WHERE `code` = "'.(string)$result.'"')) {

            $this->context->smarty->assign('result', $message);

        } else {

            $this->context->smarty->assign('result', "Resposta indefinida!");

        }
    }
    
    public function upsertProduct($kiapi_key,$ref,$designation, $shortName, $tax, $obs,$isService, $hasStocks, $active,$shortDesc, $longDesc, $price,$vendorRef,$ean)
    {
    	$env = 'out';
        $url = "http://login.e-comercial.pt/API3_ws.php?wsdl";
	    
		if ($env == 'in') {
		    $client = new SoapClient($url, array(
		        'proxy_host' => 'gateway.zscaler.net',
		        'proxy_port' => '80',
		        'authentication' => SOAP_AUTHENTICATION_BASIC,
		        'stream_context' => stream_context_create(array(
		                'ssl' => array( 'verify_peer' => false,
		                        'verify_peer_name' => false,
		                        'allow_self_signed' => false)
		        ))
		    ));
		} else {
		
	        $client = new SoapClient($url);
		}
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
        
        $result=reset($result);
        
        return $result;
    }

    /*
        TODO public function upsertCustomer(){}
    */

    // on product save action
    public function hookActionProductSave()
    {
        // sai se não for para sincronizar com a api dos produtos
        if (!ConfigsValidation::syncProducts())
        {
            return false;
        }

        // Se a chave não existir coloca mensagem para o ecrã e sai
        if (!ConfigsValidation::kiApiKeyExists())
        {
            $this->context->controller->errors[] = 'API_Key not defined';
            return false;
        }

        $id_product = (int)Tools::getValue('id_product');

        if (Validate::isLoadedObject($product = new Product($id_product))) {

            $kiapi_key = Configuration::get(_DB_PREFIX_.'PTINVC_KIAPI');
            $ref = isset($product->reference) ? $product->reference : 'N/A';
            $designation = isset($product->name) ?  reset($product->name) : 'N/A';
            $shortName = 'N/A';
            $tax = isset($product->tax_rate) ? $product->tax_rate : 'N/A';
            $obs="Produto inserido via PrestaToKeyinvoice";
            $isService=isset($product->is_virtual) ? $product->is_virtual : 'N/A';
            $hasStocks="1";
            $active="1";
            $shortDesc = isset($product->description_short) ? strip_tags(reset($product->description_short)) : 'N/A';
            $longDesc = isset($product->description) ? strip_tags(reset($product->description)) : 'N/A';
            $price = isset($product->price) ? $product->price : 'N/A';
            $vendorRef = isset($product->supplier_name) ? $product->supplier_name : 'N/A';
            $ean = isset($product->ean13) ? $product->ean13 : 'N/A';
            
            $result=$this->upsertProduct($kiapi_key, $ref, $designation, $shortName, $tax, $obs, $isService, $hasStocks, $active, $shortDesc, $longDesc, $price, $vendorRef, $ean);
            $this->getWSResponse($result);

            /*
             * TODO
             *  Caso seja erro enviar o codigo do erro para o ecrã a mensagem tem de ser formatada.
             * */
            if (count($result) > 0 && $result[0] != '1')
            {
                $message = (count($result) == 1) ? $result : ($result[0] . " - " . $result[1]);
                $this->context->controller->errors[] = $message;
            }
        }
    }

	###### primeira abordagem que nao funcionou na versao presta 1.6.0.9 ###############
	/*
    public function assignProductTabContent()
    {
    	$kiapi_key = Configuration::get(_DB_PREFIX_.'PTINVC_KIAPI');
        $product = new Product((int)Tools::getValue('id_product'));
        $ref = isset($product->reference) ? $product->reference : 'N/A';
        $designation = isset($product->name) ? $product->name : 'N/A';
        $shortName = 'N/A';
        $tax = isset($product->tax_rate) ? $product->tax_rate : 'N/A';
        $obs="Produto inserido via PrestaToKeyinvoice";
        $isService=isset($product->is_virtual) ? $product->is_virtual : 'N/A';
        $hasStocks="1";
        $active="1";
        $shortDesc = isset($product->description_short) ? $product->description_short : 'N/A';
        $longDesc = isset($product->description) ? $product->description : 'N/A';
        $price = isset($product->price) ? $product->price : 'N/A';
        $vendorRef = isset($product->supplier_name) ? $product->supplier_name : 'N/A';
        $ean = isset($product->ean13) ? $product->ean13 : 'N/A';
        
        $this->context->smarty->assign('kiapi_key', $kiapi_key);
        $this->context->smarty->assign('ref', $ref);
        $this->context->smarty->assign('designation', reset($designation));
        $this->context->smarty->assign('shortName', $shortName);
        $this->context->smarty->assign('tax', $tax);
        $this->context->smarty->assign('obs', $obs);
        $this->context->smarty->assign('isService', $isService);
        $this->context->smarty->assign('hasStocks', $hasStocks);
        $this->context->smarty->assign('active', $active);
        $this->context->smarty->assign('shortDesc', strip_tags(reset($shortDesc)));
        $this->context->smarty->assign('longDesc', strip_tags(reset($longDesc)));
        $this->context->smarty->assign('price', $price);
        $this->context->smarty->assign('vendorRef', $vendorRef);
        $this->context->smarty->assign('ean', $ean);
		
    }

    public function processProduct()
    {

        if (Tools::isSubmit('process_product_form'))
        {
            $url = "http://login.e-comercial.pt/API3_ws.php?wsdl";            
            $kiapi_key = Tools::getValue('kiapi_key');
            $ref = Tools::getValue('ref');
            $designation = Tools::getValue('designation');
            $shortName = Tools::getValue('shortName');
            $tax = Tools::getValue('tax');
            $obs = Tools::getValue('obs');
            $isService = Tools::getValue('isService');
            $hasStocks = Tools::getValue('hasStocks');
            $active = Tools::getValue('active');
            $shortDesc = Tools::getValue('shortDesc');
            $longDesc = Tools::getValue('longDesc');
            $price = Tools::getValue('price');
            $vendorRef = Tools::getValue('vendorRef');
            $ean = Tools::getValue('ean');
			
			$result=$this->upsertProduct($kiapi_key, $ref, $designation, $shortName, $tax, $obs, $isService, $hasStocks, $active, $shortDesc, $longDesc, $price, $vendorRef, $ean);
            $this->getWSResponse($result);
            
        }
    }

    
    public function hookDisplayAdminProductsExtra()
    {
    	
    	$id_product = (int)Tools::getValue('id_product');
        if (Validate::isLoadedObject($product = new Product($id_product))) {
        	
	        if (Tools::isSubmit('process_product_form'))
	        {
				// Se a chave não existir coloca mensagem para o ecrã e sai
		        if (!ConfigsValidation::kiApiKeyExists())
		        {
		            $this->context->controller->errors[] = 'API_Key not defined';
		            return false;
		        }
		
		        $env = 'out';
				// API Webservice URL
			    $url = "http://login.e-comercial.pt/API3_ws.php?wsdl";
			    $kiapi_key = Configuration::get(_DB_PREFIX_.'PTINVC_KIAPI');
				    
				if ($env == 'in') {
				    $client = new SoapClient($url, array(
				        'proxy_host' => 'gateway.zscaler.net',
				        'proxy_port' => '80',
				        'authentication' => SOAP_AUTHENTICATION_BASIC,
				        'stream_context' => stream_context_create(array(
				                'ssl' => array( 'verify_peer' => false,
				                        'verify_peer_name' => false,
				                        'allow_self_signed' => false)
				        ))
				    ));
				} else {
				
			        $client = new SoapClient($url);
				}
	
		        // see if key is valid before update config
		        $kiapi_auth =  $client->authenticate("$kiapi_key");
		        $session = $kiapi_auth[1];
					
				$tax_rate = PrestaToKeyInvoiceGetValueByID::getTaxByID($product->getIdTaxRulesGroup());
				
			    $this->upsertProduct($kiapi_key, $product->reference, $product->name, "N/A", "$tax_rate", "Produto inserido via PrestaToKeyinvoice", $cartProduct['is_virtual'], "1", $cartProduct['active'], "N/A", "N/A", $cartProduct['product_price'], "N/A", $cartProduct['ean13']);
				    	$kiapi_key = Configuration::get(_DB_PREFIX_.'PTINVC_KIAPI');
        $product = new Product((int)Tools::getValue('id_product'));
        $ref = isset($product->reference) ? $product->reference : 'N/A';
        $designation = isset($product->name) ? $product->name : 'N/A';
        $shortName = 'N/A';
        $tax = isset($product->tax_rate) ? $product->tax_rate : 'N/A';
        $obs="Produto inserido via PrestaToKeyinvoice";
        $isService=isset($product->is_virtual) ? $product->is_virtual : 'N/A';
        $hasStocks="1";
        $active="1";
        $shortDesc = isset($product->description_short) ? $product->description_short : 'N/A';
        $longDesc = isset($product->description) ? $product->description : 'N/A';
        $price = isset($product->price) ? $product->price : 'N/A';
        $vendorRef = isset($product->supplier_name) ? $product->supplier_name : 'N/A';
        $ean = isset($product->ean13) ? $product->ean13 : 'N/A';
			}
			$getFields=$product->name;
            $this->context->smarty->assign('product', $getFields);
		}
	    return $this->display(__FILE__, 'displayAdminProductsExtra.tpl');
	
	}
*/
	/* hook em standby 
	public function hookActionValidateOrder($params)
	{
		if ($this->context->cookie->id_cart)
		{
		    $cart = new CartCore($this->context->cookie->id_cart);
			$id_customer=$cart->id_customer;
			$getProducts=$cart->getProducts();
	        $env = 'out';
		    // API Webservice URL
	        $url = "http://login.e-comercial.pt/API3_ws.php?wsdl";
	        $kiapi_key = Configuration::get(_DB_PREFIX_.'PTINVC_KIAPI');
		    
			if ($env == 'in') {
			    $client = new SoapClient($url, array(
			        'proxy_host' => 'gateway.zscaler.net',
			        'proxy_port' => '80',
			        'authentication' => SOAP_AUTHENTICATION_BASIC,
			        'stream_context' => stream_context_create(array(
			                'ssl' => array( 'verify_peer' => false,
			                        'verify_peer_name' => false,
			                        'allow_self_signed' => false)
			        ))
			    ));
			} else {
			
		        $client = new SoapClient($url);
			}
	        // see if key is valid before update config
	        $kiapi_auth =  $client->authenticate("$kiapi_key");
	        $session = $kiapi_auth[1];

			//$client->insertDocumentHeader("$session", "224167626", "15", "$id_customer","op_name","opt_nif", "opt_address", "opt_locality", "opt_postalCode", "docRef");
			//$client->insertDocumentLine("$session", "1", "15", "demo_1", "1", "", "", "", "");
			

        }
	
	}
    */
    public function hookDisplayAdminOrder()
    {

        $id_order = (int)Tools::getValue('id_order');
        if (Validate::isLoadedObject($order = new OrderCore($id_order))) {
            
	        if (Tools::isSubmit('process_sync_order'))
	        {
				// Se a chave não existir coloca mensagem para o ecrã e sai
		        if (!ConfigsValidation::kiApiKeyExists())
		        {
		            $this->context->controller->errors[] = 'API_Key not defined';
		            return false;
		        }
		
		        $env = 'out';
				// API Webservice URL
			    $url = "http://login.e-comercial.pt/API3_ws.php?wsdl";
			    $kiapi_key = Configuration::get(_DB_PREFIX_.'PTINVC_KIAPI');
				    
				if ($env == 'in') {
				    $client = new SoapClient($url, array(
				        'proxy_host' => 'gateway.zscaler.net',
				        'proxy_port' => '80',
				        'authentication' => SOAP_AUTHENTICATION_BASIC,
				        'stream_context' => stream_context_create(array(
				                'ssl' => array( 'verify_peer' => false,
				                        'verify_peer_name' => false,
				                        'allow_self_signed' => false)
				        ))
				    ));
				} else {
				
			        $client = new SoapClient($url);
				}
	
		        // see if key is valid before update config
		        $kiapi_auth =  $client->authenticate("$kiapi_key");
		        $session = $kiapi_auth[1];
	
				$result_header = $client->insertDocumentHeader("$session", "224167626", "15", "","","", "", "", "", "");
				$docID=$result_header[1];
				
				// produtos
				$result_line='';
	            $cartProducts = $order->getCartProducts();
				
				foreach ($cartProducts as $cartProduct) {
					
	            	$tax_rate = PrestaToKeyInvoiceGetValueByID::getTaxByID($cartProduct['id_tax_rules_group']);
	                $result=$this->upsertProduct($kiapi_key, $cartProduct['product_reference'], $cartProduct['product_name'], "N/A", "$tax_rate", "Produto inserido via PrestaToKeyinvoice", $cartProduct['is_virtual'], "1", $cartProduct['active'], "N/A", "N/A", $cartProduct['product_price'], "N/A", $cartProduct['ean13']);
					if (count($result) > 0 && $result[0] != '1')
		            {
		                $message = (count($result) == 1) ? $result : ($result[0] . " - " . $result[1]);
		                $this->context->controller->errors[] = $message;
		            }
					$result = $client->insertDocumentLine("$session", "$docID", "15", $cartProduct['product_reference'], $cartProduct['product_quantity'], "", "", "", "");
					if (count($result) > 0 && $result[0] != '1')
		            {
		                $message = (count($result) == 1) ? $result : ($result[0] . " - " . $result[1]);
		                $this->context->controller->errors[] = $message;
		            }
				}
	        }
            
            return $this->display(__FILE__, 'displayAdminOrder.tpl');
        }
    }

}
