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
 * @author    Majoinfa - Sociedade Unipessoal Lda
 * @copyright 2016-2021 Majoinfa - Sociedade Unipessoal Lda
 * @license   LICENSE.txt
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
        $this->module_key = 'c7d055713b0edeb5943ca84cfd88ab32';
        parent::__construct();
        $this->displayName = $this->l('Presta PT Invoice');
        $this->description = $this->l('Provides integration with Portuguese Authorized Billing Solution');
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

        if (!$this->registerHook('displayAdminOrder') ||
            !$this->registerHook('actionProductSave') ||
            !$this->registerHook('orderConfirmation') ||
            !$this->registerHook('actionObjectAddressUpdateAfter') ||
            !$this->registerHook('actionObjectAddressAddAfter') ||
            !$this->registerHook('displayAdminCustomers')
        ) {
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

        // Delete configuration values
        PTInvoiceConfigsValidation::deleteByName();
        return true;
    }

    /**
     *
     */
    public function assignDocTypeInv()
    {
        $getDoctype = Configuration::get('PTInvoice_INV_DOC_TYPE');
        $defaultSelect = isset($getDoctype) ? $getDoctype : '1';

        $this->context->smarty->assign(
            'InvdocOptions',
            array(
                1 => 'Factura',
                2 => 'Guia de Remessa',
                6 => 'Factura Proforma',
                8 => 'Factura Simplificada',
                7 => 'Factura-Recibo')
        );
        $this->context->smarty->assign('InvdefaultSelect', $defaultSelect);
    }

    /**
     *
     */
    public function assignDocTypeShip()
    {

        $getDoctype = Configuration::get('PTInvoice_SHIP_DOC_TYPE');
        $defaultSelect = isset($getDoctype) ? $getDoctype : '1';

        $this->context->smarty->assign(
            'ShipdocOptions',
            array(
                1 => 'Factura',
                2 => 'Guia de Remessa',
                6 => 'Factura Proforma',
                8 => 'Factura Simplificada',
                7 => 'Factura-Recibo')
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

            if (empty(Tools::getValue('config_url'))) {

                $this->context->smarty->assign('no_config_url', 'na');
                PTInvoiceConfigsValidation::deleteByName();

            } else {

                PTInvoiceConfigsValidation::setconfigurl(Tools::getValue('config_url'));
                $this->context->smarty->assign('confirmation_config_url', 'ok');

            }

            if (empty(Tools::getValue('username'))) {

                $this->context->smarty->assign('no_username', 'na');
                PTInvoiceConfigsValidation::deleteByName();

            } else {

                PTInvoiceConfigsValidation::setusername(Tools::getValue('username'));
                $this->context->smarty->assign('confirmation_username', 'ok');

            }


            if (empty(Tools::getValue('password'))) {

                $this->context->smarty->assign('no_password', 'na');
                PTInvoiceConfigsValidation::deleteByName();

            } else {

                PTInvoiceConfigsValidation::setpassword(Tools::getValue('password'));
                $this->context->smarty->assign('confirmation_password', 'ok');

            }


            if (empty(Tools::getValue('appID'))) {

                $this->context->smarty->assign('no_configuration_key', 'na');
                PTInvoiceConfigsValidation::deleteByName();

            } else {

                PTInvoiceConfigsValidation::setkiapi(Tools::getValue('appID'));
                $this->context->smarty->assign('confirmation_appID', 'ok');

            }

            if (empty(Tools::getValue('ptinvoice_company'))) {

                $this->context->smarty->assign('no_ptinvoice_company', 'na');
                PTInvoiceConfigsValidation::deleteByName();

            } else {

                PTInvoiceConfigsValidation::setcompany(Tools::getValue('ptinvoice_company'));
                $this->context->smarty->assign('confirmation_ptinvoice_company', 'ok');

            }

            $ptinvoiceOps = new PTInvoiceOperations();
            $response = $ptinvoiceOps->login();

            if ($response[0] == "nok") {

                $this->context->smarty->assign('no_confirmation_key', 'nok');
                PTInvoiceConfigsValidation::deleteByName();

            } else {

                $this->context->smarty->assign('confirmation_key', 'ok');
            }

        }
        return true;
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
        return $this->display(__FILE__, 'getContent.tpl');
    }

    ##################################### Module Config End ##############################################

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
            $ptinvoiceOps = new PTInvoiceOperations();
            $result = $ptinvoiceOps->login();

            if ($result[0] != "nok") {
                $result = ProductToPTInvoice::saveByIdProduct($ptinvoiceOps, $id_product);
                $ptinvoiceOps->logout();
            }

            if ($result[0] == "nok") {
                $this->context->controller->errors[] = utf8_decode($result[1]);
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
                    $this->context->controller->errors[] = utf8_decode($result[1]);
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
    public function hookDisplayAdminCustomers()
    {
        if (!PTInvoiceConfigsValidation::PTInvoiceIdExists()) {
            return false;
        }

        if (Tools::isSubmit('ptinvoice_save_address')) {
            $result = ClientToPTInvoice::saveByIdAddress(Tools::getValue('ptinvoice_address_radio'));

            if (isset($result)) {

                if ($result[0] != 'ok') {
                    $this->context->controller->errors[] = utf8_decode($result[1]);
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

                        if (($address[0]['morada'] . $address[0]['morada2']) == ($addr1 . $addr2)) {
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

    /**
     * @return bool
     */
    public function hookDisplayAdminOrder()
    {

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

            $from = 'hookDisplayAdminOrder';

        } else {

            $from = 'hookOrderConfirmation';
        }

        if (Tools::isSubmit('process_sync_order')
            || PTInvoiceConfigsValidation::syncOrders()
        ) {

            $result = OrderToPTInvoice::sendOrderToPTInvoice($id_order, $from);

            if ($result[0] == "nok") {

                $this->context->controller->errors[] = utf8_decode($result[1]);
            } else {

                $this->context->smarty->assign('confirmation_ok', $result);
            }
        }

        return $this->display(__FILE__, 'displayAdminOrder.tpl');
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
        return true;
    }
}
