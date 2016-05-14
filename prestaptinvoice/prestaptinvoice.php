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


require 'classes/PTInvoiceConfigsValidation.php';
require 'classes/GetValueByID.php';
require 'classes/ClientToPTInvoice.php'; // client operations
require 'classes/ProductToPTInvoice.php'; // product operations
require 'classes/OrderToPTInvoice.php'; // product operations
require 'classes/PTInvoiceOperations.php';

class PrestaPTInvoice extends Module
{
    /**
     * PrestaPTInvoice constructor.
     */
    public function __construct()
    {
        $this->name = 'prestaptinvoice';
        $this->tab = 'billing_invoicing';
        $this->version = '1.0.0';
        $this->author = 'Majoinfa, Lda';
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('PTInvoice Connector');
        $this->description = $this->l('Provides integration with Portuguese PTInvoice billing system');
    }

    /**
     * @return bool
     */
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

    /**
     * @return bool
     */
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
        PTInvoiceConfigsValidation::deleteByName();
        return true;
    }

    /**
     * @param $sql_file
     * @return bool
     */
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

    /**
     *
     */
    public function assignDocTypeInv()
    {

        $getDoctype = Configuration::get('PTInvoice_INV_DOC_TYPE');
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

    /**
     *
     */
    public function assignDocTypeShip()
    {

        $getDoctype = Configuration::get('PTInvoice_SHIP_DOC_TYPE');
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
    /**
     * @return bool
     */
    public function processConfiguration()
    {
        if (Tools::isSubmit('ptinvc_save_form')) {
            // enable/disable products syncronization with PTInvoice
            PTInvoiceConfigsValidation::setSyncProducts(Tools::getValue('enable_products_sync'));
            // enable/disable clients syncronization with PTInvoice
            PTInvoiceConfigsValidation::setSyncClients(Tools::getValue('enable_clients_sync'));
            // enable/disable orders syncronization with PTInvoice
            PTInvoiceConfigsValidation::setSyncOrders(Tools::getValue('enable_orders_sync'));
            // choose doctype to sync by default
            PTInvoiceConfigsValidation::setDocTypeShip(Tools::getValue('PTInvoice_SHIP_DOC_TYPE'));
            PTInvoiceConfigsValidation::setDocTypeInv(Tools::getValue('PTInvoice_INV_DOC_TYPE'));
            // configure doc reference for shipping cost
            PTInvoiceConfigsValidation::setShippingCostProduct(Tools::getValue('PTInvoice_SHIPPINGCOST'));
            
            // check key
            if (!$appID = Tools::getValue('appID')) {
                
                $this->context->smarty->assign('appID', 'na');
                PTInvoiceConfigsValidation::deleteByName();
                return false;
            }
            
            PTInvoiceConfigsValidation::setkiapi(Tools::getValue('appID'));
            $this->context->smarty->assign('confirmation_appID', 'ok');

            PTInvoiceConfigsValidation::setusername(Tools::getValue('username'));
            $this->context->smarty->assign('confirmation_username', 'ok');

             PTInvoiceConfigsValidation::setpassword(Tools::getValue('password'));
            $this->context->smarty->assign('confirmation_password', 'ok');

            PTInvoiceConfigsValidation::setconfig_url(Tools::getValue('config_url'));
            $this->context->smarty->assign('confirmation_config_url', 'ok');

            PTInvoiceConfigsValidation::setcompany(Tools::getValue('ptinvoice_company'));
            $this->context->smarty->assign('confirmation_ptinvoice_company', 'ok');

            // DEMO PARA TESTAR AUTENTICACAO COM SUCESSO
            //$ptinvoiceOps = new PTInvoiceOperations();
            //$result = $ptinvoiceOps->login();

            // new product
            //$result = $ptinvoiceOps->newInstance("StWS");

            // $result['result'][0]['ref'] = "xuxinhas";
            // $result['result'][0]['design'] = "xuxinhas";
            // $result = $ptinvoiceOps->save("StWS", $result['result'][0]);

            // get product (duas formas de fazer o select)
            // $fields = array(array('column' => 'ref', 'value' => 'xuxinhas'), array('column' => 'design', 'value' => 'xuxinhas'));
            //$fields = array(array('column' => 'ref', 'value' => 'A001'));
            //$result = $ptinvoiceOps->query("StWS", $fields);
            //var_dump($result);
            //die();

            // update
            // $result = $ptinvoiceOps->update("StWS", "0f4-453d-beec-5e603ac5a3a", "design", "\"xuxitas\"");

            // $result = $ptinvoiceOps->logout();
        }
    }

    /**
     *
     */
    public function assignConfiguration()
    {
        $config_url = Configuration::get('PTInvoice_CONFIG_URL');
        $this->context->smarty->assign('config_url', $config_url);

        $username = Configuration::get('PTInvoice_USERNAME');
        $this->context->smarty->assign('username', $username);

        $password = Configuration::get('PTInvoice_PASSWORD');
        $this->context->smarty->assign('password', $password);

        $appID = Configuration::get('PTInvoice_APPID');
        $this->context->smarty->assign('appID', $appID);

        $ptinvoice_company = Configuration::get('PTInvoice_COMPANY');
        $this->context->smarty->assign('ptinvoice_company', $ptinvoice_company);

        // enable/disable products syncronization with PTInvoice
        $enable_products_sync = Configuration::get('PTInvoice_PRODUCTS_SYNC');
        $this->context->smarty->assign('enable_products_sync', $enable_products_sync);

        // enable/disable clients syncronization with PTInvoice
        $enable_clients_sync = Configuration::get('PTInvoice_CLIENTS_SYNC');
        $this->context->smarty->assign('enable_clients_sync', $enable_clients_sync);

        // enable/disable orders syncronization with PTInvoice
        $enable_orders_sync = Configuration::get('PTInvoice_ORDERS_SYNC');
        $this->context->smarty->assign('enable_orders_sync', $enable_orders_sync);
        
        $PTInvoice_SHIPPINGCOST = Configuration::get('PTInvoice_SHIPPINGCOST');
        $this->context->smarty->assign('PTInvoice_SHIPPINGCOST', $PTInvoice_SHIPPINGCOST);
        
        // doctype drop box
        $this->assignDocTypeShip();
        $this->assignDocTypeInv();
    }

    /**
     * @return mixed
     */
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
    /**
     * @param $result
     * @return false|null|string
     */
    public function getWSResponse($result)
    {
        $message = DB::getInstance()->getValue(
            'SELECT message FROM `'._DB_PREFIX_.'PTInvoice_response` WHERE `code` = "'.(string)$result.'"'
        );
        return isset($message) ? $message : "Resposta indefinida!";
    }

    /**
     * @param $result
     */
    public function sendWSErrorResponse($result)
    {
        if (count($result) > 0 && $result[0] != '1') {
            $message = (count($result) == 1) ? $result[0] : ($result[0] . " - " . $result[1]);
            $this->context->controller->errors[] =utf8_decode($message);
        }
    }

    // on product save action
    /**
     * @return bool
     */
    public function hookActionProductSave()
    {
        // sai se não for para sincronizar com a api dos produtos
        if (!PTInvoiceConfigsValidation::syncProducts()) {
            return false;
        }

        // Se a chave não existir coloca mensagem para o ecrã e sai
        if (!PTInvoiceConfigsValidation::PTInvoiceIdExists()) {
            $this->context->controller->errors[] = 'PTInvoice autentication not defined';
            return false;
        }

        if ($id_product = (int)Tools::getValue('id_product')) {
            $result = ProductToPTInvoice::saveByIdProduct($id_product);

            if ($result[0] == "nok") {
                $this->context->controller->errors[] =utf8_decode($result[1]);
            }
        }

        return true;
    }

    /**
     * @param $params
     * @return bool
     */
    public function addAndUpdateClients($params)
    {
        // sai se não for para sincronizar com a api dos produtos
        if (!PTInvoiceConfigsValidation::syncClients()) {
            return false;
        }

        if ($params["object"] instanceof Address) {
            $result = ClientToPTInvoice::saveByIdAddress($params['object']->id);
            $location = Dispatcher::getInstance()->getController(); // page location

            if (isset($location) && $location == 'adminaddresses') {
                if (isset($result) && $result[0] != 'ok') {
                    $this->context->controller->errors[] =utf8_decode($result[1]);
                }
            }
        }

        return true;
    }

    // on address add action
    /**
     * @param $params
     */
    public function hookActionObjectAddressAddAfter($params)
    {
        self::addAndUpdateClients($params);
    }

    // on address update action
    /**
     * @param $params
     */
    public function hookActionObjectAddressUpdateAfter($params)
    {
        self::addAndUpdateClients($params);
    }

    /**
     * @return bool
     */
    public function hookDisplayAdminOrder()
    {
        // sai se não for para sincronizar com a api das encomendas
        if (!PTInvoiceConfigsValidation::syncOrders()) {
            return false;
        }
        
        // Se a chave não existir coloca mensagem para o ecrã e sai
        if (!PTInvoiceConfigsValidation::PTInvoiceIdExists()) {
            $this->context->controller->errors[] = 'PHCFX configs not defined';
            return false;
        }

        $id_order = (int)Tools::getValue('id_order');

        // doctype drop box
        $this->assignDocTypeShip();
        //$this->assignDocTypeInv();
        if (Tools::isSubmit('process_sync_order')) {

            $result = OrderToPTInvoice::sendOrderToPTInvoice($id_order, 'hookDisplayAdminOrder');
            if (isset($result) && $result[0] != '1') {
                $result[0] = utf8_encode($this->getWSResponse($result[0]));
                $this->sendWSErrorResponse($result);
                
            } elseif (isset($result) && $result[0] == '1') {
                
                $this->context->smarty->assign('confirmation_ok', $result);
            }
        }

         return $this->display(__FILE__, 'displayAdminOrder.tpl');
    }

    /**
     * @return bool
     */
    public function hookDisplayAdminCustomers()
    {
        if (!PTInvoiceConfigsValidation::PTInvoiceIdExists()) {
            return false;
        }

        if (Tools::isSubmit('ptinvoice_save_address')) {
            $result = ClientToPTInvoice::saveByIdAddress(Tools::getValue('ptinvoice_address_radio'));

            if (isset($result)) {

                if ($result[0] != 'ok') {
                    $this->context->controller->errors[] =utf8_decode($result[1]);
                } else {
                    $this->context->smarty->assign('send_to_pt_invoice_confirmation', "ok");
                }
            }
        }

        if (Validate::isLoadedObject($customer = new Customer((int)Tools::getValue('id_customer')))) {
            $address_list = $customer->getAddresses($this->context->language->id);
            $selected_address = "-1";

            foreach ($address_list as $addr) {

                try {
                    $vat_number = $addr['vat_number'];

                    $address = ClientToPTInvoice::getAddress($vat_number);

                    if (isset($address)) {
                        $addr1 = utf8_encode($addr['address1']);
                        $addr2 = utf8_encode($addr['address2']);

                        if (($address[0]['morada'].$address[0]['morada2']) == ($addr1.$addr2)) {
                            $selected_address = $addr['id_address'];

                            //var_dump($address_list);
                            //var_dump(($addr1.$addr2));
                            //die();
                        }
                    }
                } catch (Exception $e) {}
            }

            $this->context->smarty->assign('selected_address', $selected_address);
            $this->context->smarty->assign('address_list', $address_list);
        }


        return $this->display(__FILE__, 'displayAdminCustomers.tpl');
    }

    // frontend
    /**
     * @return bool
     */
    public function hookOrderConfirmation()
    {
        // sai se não for para sincronizar com a api das encomendas
        if (!PTInvoiceConfigsValidation::syncOrders()) {
            return false;
        }
        
        // Se a chave não existir coloca mensagem para o ecrã e sai
        if (!PTInvoiceConfigsValidation::PTInvoiceIdExists()) {
            return false;
        }

        $id_order = (int)Tools::getValue('id_order');
        OrderToPTInvoice::sendOrderToPTInvoice($id_order, 'hookOrderConfirmation');
        
        /*
        * TODO: notificar admin de orders nao sincronizadas via frontend
        */
    }
}
