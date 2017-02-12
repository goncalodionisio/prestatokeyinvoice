{*
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
*}
{if isset($confirmation_key)}
    <div class="alert alert-success">{l s='Settings updated' mod='keyinvoiceconnector'}</div>
{elseif isset($no_confirmation_key)}
    <div class="alert alert-danger">{l s='Key not recognized!' mod='keyinvoiceconnector'}</div>
{elseif isset($no_configuration_key)}
    <div class="alert alert-info">{l s='No API key set yet' mod='keyinvoiceconnector'}</div>
{/if}
{if isset($no_soap)}
    <div class="alert alert-danger">{l s='There was no comunication with Webservice! Try again later!' mod='keyinvoiceconnector'}</div>
{/if}

<div class="panel">
    <div class="panel-heading">
        <legend><img src="../img/admin/cog.gif" alt="" width="16" /> 
            {l s='Configuration' mod='keyinvoiceconnector'}
        </legend>
    </div>
    <form action="" method="post">
        <div class="col-lg-12">
            <div class="col-lg-6">
                <fieldset>
                <legend>KIAPI Key:</legend>
                    <div class="form-group clearfix">
                        <div class="col-lg-10">
                            <input type="text" placeholder="kiapi_key" id="kiapi_key" name="KEYINVOICECONNECTOR_KIAPI" value="{$KEYINVOICECONNECTOR_KIAPI|escape:'htmlall':'UTF-8'}" />
                         </div>
                         <div class="col-lg-2">   
                            {if empty($KEYINVOICECONNECTOR_KIAPI) and empty($no_confirmation_key) }
                                <img src="../img/admin/status_orange.png" alt="" />
                            {elseif isset($no_confirmation_key)}
                                <img src="../img/admin/status_red.png" alt="" />
                            {else}
                                <img src="../img/admin/status_green.png" alt="" />
                            {/if}
                        </div>
                    </div>
                </fieldset>
            </div>
            <div class="col-lg-6">
                <fieldset>
                <legend class="text-center">{l s='Help' mod='keyinvoiceconnector'}:</legend>
                    <p class="text-justify">- {l s='Generate API Key in Keyinvoice and save it here.' mod='keyinvoiceconnector'}</p>
                </fieldset>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="col-lg-6">
                <fieldset>
                <legend>{l s='Price+Tax' mod='keyinvoiceconnector'}:</legend>
                    <div class="form-group clearfix">
                        <div class="col-lg-8">
                            <label for="">{l s='Enable/disable price+tax' mod='keyinvoiceconnector'}:</label>
                        </div>
                        <div class="col-lg-4">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input onclick="toggleDraftWarning(false);showOptions(true);showRedirectProductOptions(false);" id="'enable_price_plus_tax_1" name="enable_price_plus_tax" value="1" type="radio" {if $enable_price_plus_tax eq 1}checked{/if}>
                                    <label for="'enable_price_plus_tax_1" class="radioCheck">
                                        {l s='Yes' mod='keyinvoiceconnector'}
                                    </label>
                                    <input onclick="toggleDraftWarning(true);showOptions(false);showRedirectProductOptions(true);" id="'enable_price_plus_tax_0" name="enable_price_plus_tax" value="0" type="radio" {if empty($enable_price_plus_tax) || $enable_price_plus_tax eq 0}checked{/if}>
                                    <label for="'enable_price_plus_tax_0" class="radioCheck">
                                        {l s='No' mod='keyinvoiceconnector'}
                                    </label>
                                    <a class="slide-button btn"></a>
                                </span>
                        </div>
                    </div>
                </fieldset>
            </div>
            <div class="col-lg-6">
                <fieldset>
                <legend>&nbsp;</legend>
                    <p class="text-justify">- {l s='Enable upload to KeyInvoice Price+Tax for module.' mod='keyinvoiceconnector'}</p>
                </fieldset>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="col-lg-6">
                <fieldset>
                    <legend>{l s='Debug' mod='keyinvoiceconnector'}:</legend>
                    <div class="form-group clearfix">
                        <div class="col-lg-8">
                            <label for="">{l s='Enable/disable debug' mod='keyinvoiceconnector'}:</label>
                        </div>
                        <div class="col-lg-4">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input onclick="toggleDraftWarning(false);showOptions(true);showRedirectProductOptions(false);" id="'enable_keyinvoice_debug_1" name="enable_keyinvoice_debug" value="1" type="radio" {if $enable_keyinvoice_debug eq 1}checked{/if}>
                                    <label for="'enable_keyinvoice_debug_1" class="radioCheck">
                                        {l s='Yes' mod='keyinvoiceconnector'}
                                    </label>
                                    <input onclick="toggleDraftWarning(true);showOptions(false);showRedirectProductOptions(true);" id="'enable_keyinvoice_debug_0" name="enable_keyinvoice_debug" value="0" type="radio" {if empty($enable_keyinvoice_debug) || $enable_keyinvoice_debug eq 0}checked{/if}>
                                    <label for="'enable_keyinvoice_debug_0" class="radioCheck">
                                        {l s='No' mod='keyinvoiceconnector'}
                                    </label>
                                    <a class="slide-button btn"></a>
                                </span>
                        </div>
                    </div>
                </fieldset>
            </div>
            <div class="col-lg-6">
                <fieldset>
                    <legend>&nbsp;</legend>
                    <p class="text-justify">- {l s='Enable debug for module.' mod='keyinvoiceconnector'}</p>
                </fieldset>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="col-lg-6">
                <fieldset>
                    <legend>{l s='Products' mod='keyinvoiceconnector'}:</legend>
                    <div class="form-group clearfix">
                        <div class="col-lg-8">
                            <label for="">{l s='Enable/disable syncronization' mod='keyinvoiceconnector'}:</label>
                        </div>
                        <div class="col-lg-4">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input onclick="toggleDraftWarning(false);showOptions(true);showRedirectProductOptions(false);" id="'enable_products_sync_1" name="enable_products_sync" value="1" type="radio" {if $enable_products_sync eq 1}checked{/if}>
                                    <label for="'enable_products_sync_1" class="radioCheck">
                                        {l s='Yes' mod='keyinvoiceconnector'}
                                    </label>
                                    <input onclick="toggleDraftWarning(true);showOptions(false);showRedirectProductOptions(true);" id="'enable_products_sync_0" name="enable_products_sync" value="0" type="radio" {if empty($enable_products_sync) || $enable_products_sync eq 0}checked{/if}>
                                    <label for="'enable_products_sync_0" class="radioCheck">
                                        {l s='No' mod='keyinvoiceconnector'}
                                    </label>
                                    <a class="slide-button btn"></a>
                                </span>
                        </div>
                    </div>
                </fieldset>
            </div>
            <div class="col-lg-6">
                <fieldset>
                    <legend>&nbsp;</legend>
                    <p class="text-justify">- {l s='Enable product integration and everytime you save your product it will be created/updated in KeyInvoice.' mod='keyinvoiceconnector'}</p>
                </fieldset>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="col-lg-6">
                <fieldset>
                    <legend>&nbsp;</legend>
                    <div class="form-group clearfix">
                        <div class="col-lg-8">
                            <label for="">{l s='Keyinvoice Master Produtcts' mod='keyinvoiceconnector'}:</label>
                        </div>
                        <div class="col-lg-4">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input onclick="toggleDraftWarning(false);showOptions(true);showRedirectProductOptions(false);" id="'keyinvoice_master_products_1" name="keyinvoice_master_products" value="1" type="radio" {if $keyinvoice_master_products eq 1}checked{/if}>
                                    <label for="'keyinvoice_master_products_1" class="radioCheck">
                                        {l s='Yes' mod='keyinvoiceconnector'}
                                    </label>
                                    <input onclick="toggleDraftWarning(true);showOptions(false);showRedirectProductOptions(true);" id="'keyinvoice_master_products_0" name="keyinvoice_master_products" value="0" type="radio" {if empty($keyinvoice_master_products) || $keyinvoice_master_products eq 0}checked{/if}>
                                    <label for="'keyinvoice_master_products_0" class="radioCheck">
                                        {l s='No' mod='keyinvoiceconnector'}
                                    </label>
                                    <a class="slide-button btn"></a>
                                </span>
                        </div>
                    </div>
                </fieldset>
            </div>
            <div class="col-lg-6">
                <fieldset>
                    <legend>&nbsp;</legend>
                    <p class="text-justify">- {l s='If enabled products only will be created, not updated.' mod='keyinvoiceconnector'}</p>
                </fieldset>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="col-lg-6">
                <fieldset>
                    <legend>{l s='Customers' mod='keyinvoiceconnector'}:</legend>
                    <div class="form-group clearfix">
                        <div class="col-lg-8">
                            <label for="">{l s='Enable/disable syncronization' mod='keyinvoiceconnector'}:</label>
                        </div>
                        <div class="col-lg-4">
                            <span class="switch prestashop-switch fixed-width-lg">
                                <input onclick="toggleDraftWarning(false);showOptions(true);showRedirectProductOptions(false);" id="'enable_clients_sync_1" name="enable_clients_sync" value="1" type="radio" {if $enable_clients_sync eq 1}checked{/if}>
                                <label for="'enable_clients_sync_1" class="radioCheck">
                                    {l s='Yes' mod='keyinvoiceconnector'}
                                </label>
                                <input onclick="toggleDraftWarning(true);showOptions(false);showRedirectProductOptions(true);" id="'enable_clients_sync_0" name="enable_clients_sync" value="0" type="radio" {if empty($enable_clients_sync) || $enable_clients_sync eq 0}checked{/if}>
                                <label for="'enable_clients_sync_0" class="radioCheck">
                                    {l s='No' mod='keyinvoiceconnector'}
                                </label>
                                <a class="slide-button btn"></a>
                            </span>
                        </div>
                    </div>
                </fieldset>
            </div>
            <div class="col-lg-6">
                <fieldset>
                <legend>&nbsp;</legend>
                    <p class="text-justify">- {l s='Enable customer integration and everytime you save customer information in fron-office or back-office your customer information will be created / updated in KeyInvoice.' mod='keyinvoiceconnector'}</p>
                </fieldset>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="col-lg-6">
                <fieldset>

                <legend>{l s='Orders' mod='keyinvoiceconnector'}:</legend>
                    <div class="form-group clearfix">
                        <div class="col-lg-8">
                            <label for="">{l s='Enable/disable syncronization' mod='keyinvoiceconnector'}:</label>
                        </div>

                        <div class="col-lg-4">
                            <span class="switch prestashop-switch fixed-width-lg">
                                <input onclick="toggleDraftWarning(false);showOptions(true);showRedirectProductOptions(false);" id="'enable_orders_sync_1" name="enable_orders_sync" value="1" type="radio" {if $enable_orders_sync eq 1}checked{/if}>
                                <label for="'enable_orders_sync_1" class="radioCheck">
                                    {l s='Yes' mod='keyinvoiceconnector'}
                                </label>
                                <input onclick="toggleDraftWarning(true);showOptions(false);showRedirectProductOptions(true);" id="'enable_orders_sync_0" name="enable_orders_sync" value="0" type="radio" {if empty($enable_orders_sync) || $enable_orders_sync eq 0}checked{/if}>
                                <label for="'enable_orders_sync_0" class="radioCheck">
                                    {l s='No' mod='keyinvoiceconnector'}
                                </label>
                                <a class="slide-button btn"></a>
                            </span>
                        </div>
                    </div>
                    <div class="form-group clearfix">
                        <label for="">{l s='Default Document Type' mod='keyinvoiceconnector'}:</label>
                        {if $enable_orders_sync eq 1}
                            {html_options name=KEYINVOICECONNECTOR_SHIP_DOC_TYPE options=$ShipdocOptions selected=$ShipdefaultSelect}
                        {else}
                            {html_options name=KEYINVOICECONNECTOR_SHIP_DOC_TYPE disabled="disabled" options=$ShipdocOptions selected=$ShipdefaultSelect}
                            <input type="hidden" id="KEYINVOICECONNECTOR_SHIP_DOC_TYPE_hidden" name="KEYINVOICECONNECTOR_SHIP_DOC_TYPE" value="{13|escape:'htmlall':'UTF-8'}" />
                        {/if}
                    </div>
                </fieldset>
            </div>
            <div class="col-lg-6">
                <fieldset>
                <legend>&nbsp;</legend>
                    <p class="text-justify">- {l s='Enable order integration and everytime you create a order in back-office or your customer creates a order in front-office the billing document will be created in KeyInvoice.' mod='keyinvoiceconnector'}</p>
                </fieldset>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="col-lg-6">
                <fieldset>
                <legend>{l s='General' mod='keyinvoiceconnector'}:</legend>
                    <div class="form-group clearfix">
                        <label>{l s='Shipping Reference' mod='keyinvoiceconnector'}:</label>
                        <input type="text" placeholder="Shipping Cost Mapper" id="KEYINVOICECONNECTOR_SHIPPINGCOST" name="KEYINVOICECONNECTOR_SHIPPINGCOST" value="{$KEYINVOICECONNECTOR_SHIPPINGCOST|escape:'htmlall':'UTF-8'}" />
                    </div>
                </fieldset>
            </div>
            <div class="col-lg-6">
                <fieldset>
                <legend>&nbsp;</legend>
                    <p class="text-justify">- {l s='You must create a Product in Keyinvoice as a "Shipping Cost Carrier". Save here is Reference' mod='keyinvoiceconnector'}</p>
                </fieldset>
            </div>
        </div>
        <div class="form-group clearfix">
            <div class="submit col-lg-offset-10 col-lg-2">
                <button type="submit" name="ptinvc_save_form" class="button btn btn-default button-medium pull-right">
                    <i class="process-icon-save"></i> <span>{l s='Save' mod='keyinvoiceconnector'}</span>
                </button>
            </div>
        </div>
    </form>
</div>
