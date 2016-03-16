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
 
require('classes/ConfigsValidation.php');
require('classes/GetValueByID.php');
require('classes/ClientToKeyInvoice.php'); // client operations
require('classes/ProductToKeyInvoice.php'); // product operations
require('classes/OrderToKeyInvoice.php'); // product operations

class PrestaToKeyInvoice extends Module
{

    public function __construct() {
        $this->name = 'prestatokeyinvoice';
        $this->tab = 'billing_invoicing';
        $this->version = '1.0.0';
        $this->author = 'Majoinfa, Lda';
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

        if (!$this->registerHook('displayAdminOrder') ||
            !$this->registerHook('actionProductSave') ||
            !$this->registerHook('orderConfirmation') ||
            !$this->registerHook('actionObjectAddressUpdateAfter') ||
            !$this->registerHook('actionObjectAddressAddAfter') ||
            !$this->registerHook('displayAdminCustomers')
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
        ConfigsValidation::deleteByName();
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
    
    public function assignDocTypeInv()
    {

        $getDoctype = Configuration::get('PRESTATOKEYINVOICE_INV_DOC_TYPE');
        $defaultSelect = isset($getDoctype) ? $getDoctype : '13';
        
        $this->context->smarty->assign('InvdocOptions', array(
            4 => 'Factura',
            7 => 'Nota de Crédito',
            13 => 'Encomenda',
            32 => 'Factura Simplificada',
            34 => 'Factura-Recibo')
        );
        $this->context->smarty->assign('InvdefaultSelect', $defaultSelect);
    }
    
    public function assignDocTypeShip()
    {

        $getDoctype = Configuration::get('PRESTATOKEYINVOICE_SHIP_DOC_TYPE');
        $defaultSelect = isset($getDoctype) ? $getDoctype : '13';
        
        $this->context->smarty->assign('ShipdocOptions', array(
            4 => 'Factura',
            13 => 'Encomenda',
            15 => 'Guia de Remessa',
            32 => 'Factura Simplificada',
            34 => 'Factura-Recibo')
        );
        $this->context->smarty->assign('ShipdefaultSelect', $defaultSelect);
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
            // choose doctype to sync by default
            ConfigsValidation::setDocTypeShip(Tools::getValue('PRESTATOKEYINVOICE_SHIP_DOC_TYPE'));
            ConfigsValidation::setDocTypeInv(Tools::getValue('PRESTATOKEYINVOICE_INV_DOC_TYPE'));
            // configure doc reference for shipping cost
            ConfigsValidation::setShippingCostProduct(Tools::getValue('PRESTATOKEYINVOICE_SHIPPINGCOST'));
            
            // check key
            if (!$kiapi_key = Tools::getValue('PRESTATOKEYINVOICE_KIAPI')) {
                
                $this->context->smarty->assign('no_configuration_key', 'na');
                ConfigsValidation::deleteByName();
                return false;
            }
            
            // check soap
            if (!$client = ConfigsValidation::APIWSClient()) {
                
                $this->context->smarty->assign('no_soap', 'nok');
                ConfigsValidation::deleteByName();
                return false;
                
            }
            
            // check session
            if (!$session = ConfigsValidation::APIWSSession($client, 'getContent')) {
                
                $this->context->smarty->assign('no_confirmation_key', 'nok');
                ConfigsValidation::deleteByName();
                return false;
                
            } else {
                
                ConfigsValidation::setkiapi(Tools::getValue('PRESTATOKEYINVOICE_KIAPI'));
                $this->context->smarty->assign('confirmation_key', 'ok');
            }
        }
    }

    public function assignConfiguration()
    {
        $kiapi_key = Configuration::get('PRESTATOKEYINVOICE_KIAPI');
        $this->context->smarty->assign('PRESTATOKEYINVOICE_KIAPI', $kiapi_key);

        // enable/disable products syncronization with keyinvoice
        $enable_products_sync = Configuration::get('PRESTATOKEYINVOICE_PRODUCTS_SYNC');
        $this->context->smarty->assign('enable_products_sync', $enable_products_sync);

        // enable/disable clients syncronization with keyinvoice
        $enable_clients_sync = Configuration::get('PRESTATOKEYINVOICE_CLIENTS_SYNC');
        $this->context->smarty->assign('enable_clients_sync', $enable_clients_sync);

        // enable/disable orders syncronization with keyinvoice
        $enable_orders_sync = Configuration::get('PRESTATOKEYINVOICE_ORDERS_SYNC');
        $this->context->smarty->assign('enable_orders_sync', $enable_orders_sync);
        
        $PRESTATOKEYINVOICE_SHIPPINGCOST = Configuration::get('PRESTATOKEYINVOICE_SHIPPINGCOST');
        $this->context->smarty->assign('PRESTATOKEYINVOICE_SHIPPINGCOST', $PRESTATOKEYINVOICE_SHIPPINGCOST);
        
        // doctype drop box
        $this->assignDocTypeShip();
        $this->assignDocTypeInv();
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

        if ($id_product = (int)Tools::getValue('id_product')) {
            $product = new Product($id_product, false, $this->context->language->id, $this->context->shop->id, $this->context);

            $result = ProductToKeyInvoice::saveByProductObject($product);

            if (isset($result) && $result[0] != '1')
            {
                $result[0] = utf8_encode($this->getWSResponse($result[0]));
                $this->sendWSErrorResponse($result);
            }
        }

        return true;
    }

    public function addAndUpdateClients($params)
    {
        // sai se não for para sincronizar com a api dos produtos
        if (!ConfigsValidation::syncClients())
        {
            return false;
        }

        // Se a chave não existir coloca mensagem para o ecrã e sai
        if (!ConfigsValidation::kiApiKeyExists())
        {
            $this->context->controller->errors[] = 'API_Key not defined';
            return false;
        }

        if ($params["object"] instanceof Address) {
            $result = ClientToKeyInvoice::saveByIdAddress($params['object']->id);
            $location = Dispatcher::getInstance()->getController(); // page location

            if (isset($location) && $location == 'adminaddresses')
            {
                if (isset($result) && $result[0] != '1')
                {
                    $result[0] = utf8_encode($this->getWSResponse($result[0]));
                    $this->sendWSErrorResponse($result);
                }
            }
        }

        return true;
    }

    // on address add action
    public function hookActionObjectAddressAddAfter($params)
    {
        PrestaToKeyInvoice::addAndUpdateClients($params);
    }

    // on address update action
    public function hookActionObjectAddressUpdateAfter($params)
    {
        PrestaToKeyInvoice::addAndUpdateClients($params);
    }

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

        // doctype drop box
        $this->assignDocTypeShip();
        //$this->assignDocTypeInv();
        if (Tools::isSubmit('process_sync_order'))
        {
            $result = OrderToKeyInvoice::sendOrderToKeyInvoice($id_order, 'hookDisplayAdminOrder');
            if (isset($result) && $result[0] != '1')
            {
                $result[0] = utf8_encode($this->getWSResponse($result[0]));
                $this->sendWSErrorResponse($result);
                
            } elseif (isset($result) && $result[0] == '1') {
                
                $this->context->smarty->assign('confirmation_ok', $result);
            }
        }

         return $this->display(__FILE__, 'displayAdminOrder.tpl');
    }

    public function hookDisplayAdminCustomers()
    {
        if (!$client = ConfigsValidation::APIWSClient())
            return false;

        if (!$session = ConfigsValidation::APIWSSession($client, 'ClientToKeyInvoice'))
            return false;

        if (Tools::isSubmit('keyinvoice_save_address'))
        {
            $result = ClientToKeyInvoice::saveByIdAddress(Tools::getValue('keyinvoice_address_radio'));

            if (isset($result)) {
                if ($result[0] != '1') {
                    $result[0] = utf8_encode($this->getWSResponse($result[0]));
                    $this->sendWSErrorResponse($result);
                }  else {
                    $this->context->smarty->assign('send_to_key_invoice_confirmation', "ok");
                }
            }
        }

        if (Validate::isLoadedObject($customer = new Customer((int)Tools::getValue('id_customer'))))
        {
            $address_list = $customer->getAddresses($this->context->language->id);
            $selected_address = "-1";

            foreach($address_list as $addr) {
                $vat_number = $addr['vat_number'];

                $clientAddress = $client->getClient("$session", "$vat_number");

                if (isset($clientAddress) && isset($clientAddress->DAT) && count($clientAddress->DAT) > 0) {
                    if ($clientAddress->DAT[0]->Address == ($addr['address1'] . ", " . $addr['address2'])) {
                        $selected_address = $addr['id_address'];
                    }
                }
            }

            $this->context->smarty->assign('selected_address', $selected_address);
            $this->context->smarty->assign('address_list', $address_list);
        }

        return $this->display(__FILE__, 'displayAdminCustomers.tpl');
    }

    // frontend
    public function hookOrderConfirmation()
    {
        // sai se não for para sincronizar com a api das encomendas
        if (!ConfigsValidation::syncOrders())
        {
            return false;
        }
        
        // Se a chave não existir coloca mensagem para o ecrã e sai
        if (!ConfigsValidation::kiApiKeyExists())
        {
            return false;
        }

        $id_order = (int)Tools::getValue('id_order');
        OrderToKeyInvoice::sendOrderToKeyInvoice($id_order, 'hookOrderConfirmation');
        
        /*
        * TODO: notificar admin de orders nao sincronizadas via frontend
        */
    }

}
