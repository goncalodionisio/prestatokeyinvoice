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


require 'classes/ConfigsValidation.php';
require 'classes/GetValueByID.php';
require 'classes/ClientToPHCX.php'; // client operations
require 'classes/ProductToPHCX.php'; // product operations
require 'classes/OrderToPHCX.php'; // product operations

class PHCxConnector extends Module
{
    public function __construct()
    {
        $this->name = 'phcxconnector';
        $this->tab = 'billing_invoicing';
        $this->version = '1.0.0';
        $this->author = 'Majoinfa, Lda';
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('PHCX Connector');
        $this->description = $this->l('Provides integration with Portuguese PHCX billing system');
    }

    public function install()
    {
        // Call install parent method
        if (!parent::install()) {
            return false;
        }

        // Execute module install SQL statements
        $sql_file = dirname(__FILE__).'/install/install.sql';
        if (!$this->loadSQLFile($sql_file)) {
            return false;
        }

        if (!$this->registerHook('displayAdminOrder') ||
        !$this->registerHook('actionProductSave') ||
        !$this->registerHook('orderConfirmation') ||
        !$this->registerHook('actionObjectAddressUpdateAfter') ||
        !$this->registerHook('actionObjectAddressAddAfter') ||
        !$this->registerHook('displayAdminCustomers')) {
            return false;
        }
        // All went well!
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        // Execute module install SQL statements
        $sql_file = dirname(__FILE__).'/install/uninstall.sql';
        if (!$this->loadSQLFile($sql_file)) {
            return false;
        }
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
        foreach ($sql_requests as $request) {
            if (!empty($request)) {
                $result &= Db::getInstance()->execute(trim($request));
            }
        }
        // Return result
        return $result;
    }

    public function assignDocTypeInv()
    {

        $getDoctype = Configuration::get('PHCXCONNECTOR_INV_DOC_TYPE');
        $defaultSelect = isset($getDoctype) ? $getDoctype : '13';
        
        $this->context->smarty->assign(
            'InvdocOptions',
            array(
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

        $getDoctype = Configuration::get('PHCXCONNECTOR_SHIP_DOC_TYPE');
        $defaultSelect = isset($getDoctype) ? $getDoctype : '13';

        $this->context->smarty->assign(
            'ShipdocOptions',
            array(
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
            // enable/disable products syncronization with PHCX
            ConfigsValidation::setSyncProducts(Tools::getValue('enable_products_sync'));
            // enable/disable clients syncronization with PHCX
            ConfigsValidation::setSyncClients(Tools::getValue('enable_clients_sync'));
            // enable/disable orders syncronization with PHCX
            ConfigsValidation::setSyncOrders(Tools::getValue('enable_orders_sync'));
            // choose doctype to sync by default
            ConfigsValidation::setDocTypeShip(Tools::getValue('PHCXCONNECTOR_SHIP_DOC_TYPE'));
            ConfigsValidation::setDocTypeInv(Tools::getValue('PHCXCONNECTOR_INV_DOC_TYPE'));
            // configure doc reference for shipping cost
            ConfigsValidation::setShippingCostProduct(Tools::getValue('PHCXCONNECTOR_SHIPPINGCOST'));
            
            // check key
            if (!$appID = Tools::getValue('appID')) {
                
                $this->context->smarty->assign('appID', 'na');
                ConfigsValidation::deleteByName();
                return false;
            }
            
            ConfigsValidation::setkiapi(Tools::getValue('appID'));
            $this->context->smarty->assign('confirmation_appID', 'ok');
            ConfigsValidation::setusername(Tools::getValue('username'));
            $this->context->smarty->assign('confirmation_username', 'ok');
             ConfigsValidation::setpassword(Tools::getValue('password'));
            $this->context->smarty->assign('confirmation_password', 'ok');
            ConfigsValidation::setconfig_url(Tools::getValue('config_url'));
            $this->context->smarty->assign('confirmation_config_url', 'ok');         
        }
    }

    public function assignConfiguration()
    {
        $config_url = Configuration::get('config_url');
        $this->context->smarty->assign('config_url', $config_url);
        $username = Configuration::get('username');
        $this->context->smarty->assign('username', $username);
        $password = Configuration::get('password');
        $this->context->smarty->assign('password', $password);
        $appID = Configuration::get('appID');
        $this->context->smarty->assign('appID', $appID);
        // enable/disable products syncronization with PHCX
        $enable_products_sync = Configuration::get('PHCXCONNECTOR_PRODUCTS_SYNC');
        $this->context->smarty->assign('enable_products_sync', $enable_products_sync);

        // enable/disable clients syncronization with PHCX
        $enable_clients_sync = Configuration::get('PHCXCONNECTOR_CLIENTS_SYNC');
        $this->context->smarty->assign('enable_clients_sync', $enable_clients_sync);

        // enable/disable orders syncronization with PHCX
        $enable_orders_sync = Configuration::get('PHCXCONNECTOR_ORDERS_SYNC');
        $this->context->smarty->assign('enable_orders_sync', $enable_orders_sync);
        
        $PHCXCONNECTOR_SHIPPINGCOST = Configuration::get('PHCXCONNECTOR_SHIPPINGCOST');
        $this->context->smarty->assign('PHCXCONNECTOR_SHIPPINGCOST', $PHCXCONNECTOR_SHIPPINGCOST);
        
        // doctype drop box
        $this->assignDocTypeShip();
        $this->assignDocTypeInv();
    }

    public function getContent()
    {
        $this->processConfiguration();
        $this->assignConfiguration();
        /*
        $userLoginServiceUser = new UserLoginServiceUser();

        if ($userLoginServiceUser->userLogin(new UserLoginStructUserLogin($user_login, $user_pass, $appID))) {
            echo "<br>38 ok_login<br><br>"; // in case of login ok
            print_r($userLoginServiceUser->getResult()); // --> you can see here also the result of the login
            echo "<br> 40 end_ok_login<br><br>"; 
        }
        */
        return $this->display(__FILE__, 'getContent.tpl');
    }

    ##################################### Module Config End ##############################################

    // vai buscar reposta do webservice que já estão na bd local.
    public function getWSResponse($result)
    {
        $message = DB::getInstance()->getValue(
            'SELECT message FROM `'._DB_PREFIX_.'PHCXconnector_response` WHERE `code` = "'.(string)$result.'"'
        );
        return isset($message) ? $message : "Resposta indefinida!";
    }
    
    public function sendWSErrorResponse($result)
    {
        if (count($result) > 0 && $result[0] != '1') {
            $message = (count($result) == 1) ? $result[0] : ($result[0] . " - " . $result[1]);
            $this->context->controller->errors[] =utf8_decode($message);
        }
    }

    // on product save action
    public function hookActionProductSave()
    {
        // sai se não for para sincronizar com a api dos produtos
        if (!ConfigsValidation::syncProducts()) {
            return false;
        }

        // Se a chave não existir coloca mensagem para o ecrã e sai
        if (!ConfigsValidation::appIDExists()) {
            $this->context->controller->errors[] = 'appID not defined';
            return false;
        }

        if ($id_product = (int)Tools::getValue('id_product')) {
            $result = ProductToPHCX::saveByIdProduct($id_product);

            if (isset($result) && $result[0] != '1') {
                $result[0] = utf8_encode($this->getWSResponse($result[0]));
                $this->sendWSErrorResponse($result);
            }
        }

        return true;
    }

    public function addAndUpdateClients($params)
    {
        // sai se não for para sincronizar com a api dos produtos
        if (!ConfigsValidation::syncClients()) {
            return false;
        }

        // Se a chave não existir coloca mensagem para o ecrã e sai
        if (!ConfigsValidation::appIDExists()) {
            $this->context->controller->errors[] = 'appID not defined';
            return false;
        }

        if ($params["object"] instanceof Address) {
            $result = ClientToPHCX::saveByIdAddress($params['object']->id);
            $location = Dispatcher::getInstance()->getController(); // page location

            if (isset($location) && $location == 'adminaddresses') {
                if (isset($result) && $result[0] != '1') {
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
        PHCXConnector::addAndUpdateClients($params);
    }

    // on address update action
    public function hookActionObjectAddressUpdateAfter($params)
    {
        PHCXConnector::addAndUpdateClients($params);
    }

    public function hookDisplayAdminOrder()
    {
        // sai se não for para sincronizar com a api das encomendas
        if (!ConfigsValidation::syncOrders()) {
            return false;
        }
        
        // Se a chave não existir coloca mensagem para o ecrã e sai
        if (!ConfigsValidation::appIDExists()) {
            $this->context->controller->errors[] = 'appID not defined';
            return false;
        }

        $id_order = (int)Tools::getValue('id_order');

        // doctype drop box
        $this->assignDocTypeShip();
        //$this->assignDocTypeInv();
        if (Tools::isSubmit('process_sync_order')) {

            $result = OrderToPHCX::sendOrderToPHCX($id_order, 'hookDisplayAdminOrder');
            if (isset($result) && $result[0] != '1') {
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

        if (!$client = ConfigsValidation::APIWSClient()) {
            return false;
        }
        if (!$session = ConfigsValidation::APIWSSession($client, 'ClientToPHCX')) {
            return false;
        }
        if (Tools::isSubmit('PHCX_save_address')) {
            $result = ClientToPHCX::saveByIdAddress(Tools::getValue('PHCX_address_radio'));

            if (isset($result)) {

                if ($result[0] != '1') {
                    $result[0] = utf8_encode($this->getWSResponse($result[0]));
                    $this->sendWSErrorResponse($result);
                } else {
                    $this->context->smarty->assign('send_to_key_invoice_confirmation', "ok");
                }
            }
        }

        if (Validate::isLoadedObject($customer = new Customer((int)Tools::getValue('id_customer')))) {
            $address_list = $customer->getAddresses($this->context->language->id);
            $selected_address = "-1";

            foreach ($address_list as $addr) {

                try {
                    $vat_number = $addr['vat_number'];
                    $clientAddress = $client->getClient("$session", "$vat_number");

                    if (isset($clientAddress) && isset($clientAddress->DAT) && isset($clientAddress->DAT[0]->Address)) {
                        $addr1 = utf8_encode($addr['address1']);
                        $addr2 = utf8_encode($addr['address2']);

                        if ($clientAddress->DAT[0]->Address == ($addr1 . ", " . $addr2)) {
                            $selected_address = $addr['id_address'];
                        }
                    }
                } catch (Exception $e) {
                    
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
        if (!ConfigsValidation::syncOrders()) {
            return false;
        }
        
        // Se a chave não existir coloca mensagem para o ecrã e sai
        if (!ConfigsValidation::appIDExists()) {
            return false;
        }

        $id_order = (int)Tools::getValue('id_order');
        OrderToPHCX::sendOrderToPHCX($id_order, 'hookOrderConfirmation');
        
        /*
        * TODO: notificar admin de orders nao sincronizadas via frontend
        */
    }
}
