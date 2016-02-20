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
            !$this->registerHook('ActionProductSave') ||
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
        if (Tools::isSubmit('ptinvc_save_form')) {

            // enable/disable products syncronization with keyinvoice
            ConfigsValidation::setSyncProducts(Tools::getValue('enable_products_sync'));
            // enable/disable clients syncronization with keyinvoice
            ConfigsValidation::setSyncClients(Tools::getValue('enable_clients_sync'));
            // enable/disable orders syncronization with keyinvoice
            ConfigsValidation::setSyncOrders(Tools::getValue('enable_orders_sync'));

            // API Webservice URL
            $url = "http://login.e-comercial.pt/API3_ws.php?wsdl";
            $kiapi_key = Tools::getValue('kiapi_key');

            try {
                // try comunication with WS
                $client = new SoapClient($url);
                // see if key is valid before update config
                if ($kiapi_key) {

                    $kiapi_auth =  $client->authenticate("$kiapi_key");
                    $result = $kiapi_auth[0];

                    if ($result == '1') {
                        Configuration::updateValue(_DB_PREFIX_.'PTINVC_KIAPI', $kiapi_key);
                        $this->context->smarty->assign('confirmation_key', 'ok');
                    } else {
                        Configuration::deleteByName(_DB_PREFIX_.'PTINVC_KIAPI');
                        // disable syncronization on error
                        ConfigsValidation::disableSyncronization();
                        $this->context->smarty->assign('no_confirmation_key', 'nok');
                    }

                } else {
                    // Delete configuration values
                    Configuration::deleteByName(_DB_PREFIX_.'PTINVC_KIAPI');
                    // disable syncronization on error
                    ConfigsValidation::disableSyncronization();
                    $this->context->smarty->assign('no_configuration_key', 'na');
                }

            } catch (Exception $e){
                // disable syncronization on error
                ConfigsValidation::disableSyncronization();
                $this->context->smarty->assign('no_soap', 'nok');
            }
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

    ##################################### Module Config End ##############################################

    // vai buscar reposta do webservice que já estão na bd local.
    public function getWSResponse($result)
    {
        $message = DB::getInstance()->getValue('SELECT message FROM `'._DB_PREFIX_.'prestatokeyinvoice_response` WHERE `code` = "'.(string)$result.'"');

        return isset($message) ? $message : "Resposta indefinida!";
    }
    
    public function sendWSErrorResponse($result)
    {
        if (count($result) > 0 && $result[0] != '1')
        {
            $message = (count($result) == 1) ? $result[0] : ($result[0] . " - " . $result[1]);
            $this->context->controller->errors[] =utf8_decode($message);
        }
    }
    
    public function upsertProduct($kiapi_key,$ref,$designation, $shortName, $tax, $obs,$isService, $hasStocks, $active,$shortDesc, $longDesc, $price,$vendorRef,$ean)
    {
        $url = "http://login.e-comercial.pt/API3_ws.php?wsdl";
        $client = new SoapClient($url);
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

            $taxValue = $product->getIdTaxRulesGroup();
            $tax        = isset($taxValue) ? (string)PrestaToKeyInvoiceGetValueByID::getTaxByID($taxValue) : '';

            $obs        = "Produto inserido via PrestaToKeyinvoice";
            $isService  = isset($product->is_virtual) ? $product->is_virtual : '0';
            $hasStocks  = isset($product->is_virtual) ? ((int)$product->getQuantity($id_product) == 0 ? '0' : '1') : '0';
            $active     = isset($product->active) ? $product->active : '1';
            $shortDesc  = isset($product->description_short) ? utf8_encode(strip_tags(reset($product->description_short))) : 'N/A';
            $longDesc   = isset($product->description) ? utf8_encode(strip_tags(reset($product->description))) : 'N/A';
            $price      = isset($product->price) ? $product->price : '';
            $vendorRef  = isset($product->supplier_name) ? $product->supplier_name : 'N/A';
            $ean        = isset($product->ean13) ? $product->ean13 : '';
            
            $result=$this->upsertProduct($kiapi_key, $ref, $designation, $shortName, $tax, $obs, $isService, $hasStocks, $active, $shortDesc, $longDesc, $price, $vendorRef, $ean);

            if (isset($result) && $result[0] != '1')
            {
                $result[0] = utf8_encode($this->getWSResponse($result[0]));
                $this->sendWSErrorResponse($result);
            }
        }

        return true;
    }

    ###### primeira abordagem que nao funcionou na versao presta 1.6.0.9 ###############
    
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
           $this->processProduct();
           $this->assignProductTabContent();
           
        return $this->display(__FILE__, 'displayAdminProductsExtra.tpl');

    }
    
    ########################## fim primeira abordagem que nao funcionou no presta 1.6.0.9 ########################################

    public function assignClientTabContent()
    {
        $kiapi_key = Configuration::get(_DB_PREFIX_.'PTINVC_KIAPI');
        $customer = new CustomerCore((int)Tools::getValue('id_customer'));
        /*
         TODO 
         Falta mapear campos cliente
         Garantir que kiapi_key esta configurada ou dar erro
         */
        
        //var_dump($customer);
 
        $nif = "N/A";
        $name = "N/A";
        $address = "N/A";
        $postalCode = "N/A";
        $locality = "N/A";
        $phone = "N/A";
        $fax = "N/A";
        $email = "N/A";
        $obs = "Cliente inserido via PrestaToKeyinvoice";

        $this->context->smarty->assign('kiapi_key', $kiapi_key);
        $this->context->smarty->assign('nif', $nif);
        $this->context->smarty->assign('name', $name);
        $this->context->smarty->assign('address', $address);
        $this->context->smarty->assign('postalCode', $postalCode);
        $this->context->smarty->assign('locality', $locality);
        $this->context->smarty->assign('phone', $phone);
        $this->context->smarty->assign('fax', $fax);
        $this->context->smarty->assign('email', $email);
        $this->context->smarty->assign('obs', $obs);
        
    }
    
    public function processClient()
    {
        /*
         TODO 
         Submeter customer via upsertCustomer()
         */
        if (Tools::isSubmit('process_client_form')) {
            $url = "http://login.e-comercial.pt/API3_ws.php?wsdl";            
            $kiapi_key = Tools::getValue('kiapi_key');
            $nif = Tools::getValue('nif');
            $name = Tools::getValue('name');
            $address = Tools::getValue('address');
            $postalCode = Tools::getValue('postalCode');
            $locality = Tools::getValue('locality');
            $phone = Tools::getValue('phone');
            $fax = Tools::getValue('fax');
            $email = Tools::getValue('email');
            $obs = Tools::getValue('obs');
        
            // try comunication with WS
            try {

               $client = new SoapClient($url);
                // see if key is valid before update config
                $kiapi_auth =  $client->authenticate("$kiapi_key");
                $session = $kiapi_auth[1];
                
                $result = $client->insertClient("$sid", 
                    "$nif",
                    "$name",
                    "$address",
                    "$postalCode",
                    "$locality",
                    "$phone",
                    "$fax",
                    "$email",
                    "$obs"
                    );

                $result=reset($result);
                
                // get WS response string
                if ($message = Db::getInstance()->executeS('
                SELECT message FROM `'._DB_PREFIX_.'prestatokeyinvoice_response`
                WHERE `code` = "'.(string)$result.'"')) {
                
                    $this->context->smarty->assign('result', $message);
                } else {
                    
                    $this->context->smarty->assign('result', "Resposta indefinida!");
                }
               
            } catch (Exception $e){
                    
                $this->context->smarty->assign('no_soap', 'nok');
            }
        }
    }

    public function hookDisplayAdminCustomers()
    {
        $this->processClient();
        $this->assignClientTabContent();
        return $this->display(__FILE__, 'displayAdminCustomers.tpl');
    
    }

    /*
    TODO
    das encomendas, quase tudo, in progress
    */
    public function hookDisplayAdminOrder()
    {
        // sai se não for para sincronizar com a api das encomendas
        if (!ConfigsValidation::syncOrders())
        {
            return false;
        }
        
        // Se a chave não existir coloca mensagem para o ecrã e sai
        if (!ConfigsValidation::kiApiKeyExists())
        {
            $this->context->controller->errors[] = 'API_Key not defined';
            return false;
        }

        $id_order = (int)Tools::getValue('id_order');
        if (Validate::isLoadedObject($order = new OrderCore($id_order))) {
            
            if (Tools::isSubmit('process_sync_order'))
            {
        
                // API Webservice URL
                $url = "http://login.e-comercial.pt/API3_ws.php?wsdl";
                $kiapi_key = Configuration::get(_DB_PREFIX_.'PTINVC_KIAPI');
                $client = new SoapClient($url);
    
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
                    $this->sendWSErrorResponse($result);

                    $result = $client->insertDocumentLine("$session", "$docID", "15", $cartProduct['product_reference'], $cartProduct['product_quantity'], "", "", "", "");
                    $this->sendWSErrorResponse($result);
                }
            }
            
            return $this->display(__FILE__, 'displayAdminOrder.tpl');
        }
    }

}
