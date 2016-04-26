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
    <div class="alert alert-success">{l s='Settings updated' mod='phcxconnector'}</div>
{elseif isset($no_confirmation_key)}
    <div class="alert alert-danger">{l s='Key not recognized!' mod='phcxconnector'}</div>
{elseif isset($no_configuration_key)}
    <div class="alert alert-info">{l s='No API key set yet' mod='phcxconnector'}</div>
{/if}
{if isset($no_soap)}
    <div class="alert alert-danger">{l s='There was no comunication with Webservice! Try again later!' mod='phcxconnector'}</div>
{/if}

<div class="panel">
    <div class="panel-heading">
        <legend><img src="../img/admin/cog.gif" alt="" width="16" /> 
            {l s='Configuration' mod='phcxconnector'}
        </legend>
    </div>
    <form action="" method="post">
        <div class="col-lg-12">
            <div class="col-lg-6">
                <fieldset>
                <legend>Session Config:</legend>
                    <div class="form-group clearfix">
                        <div class="col-lg-10">
                            <input type="text" placeholder="config_url" id="config_url" name="config_url" value="{$config_url|escape:'htmlall':'UTF-8'}" />
                         </div>
                         <div class="col-lg-2">   
                            {if empty($config_url) and empty($no_config_url) }
                                <img src="../img/admin/status_orange.png" alt="" />
                            {elseif isset($no_config_url)}
                                <img src="../img/admin/status_red.png" alt="" />
                            {else}
                                <img src="../img/admin/status_green.png" alt="" />
                            {/if}
                        </div>
                        <div class="col-lg-10">
                            <input type="text" placeholder="username" id="username" name="username" value="{$username|escape:'htmlall':'UTF-8'}" />
                         </div>
                         <div class="col-lg-2">   
                            {if empty($username) and empty($no_username) }
                                <img src="../img/admin/status_orange.png" alt="" />
                            {elseif isset($no_username)}
                                <img src="../img/admin/status_red.png" alt="" />
                            {else}
                                <img src="../img/admin/status_green.png" alt="" />
                            {/if}
                        </div>
                        <div class="col-lg-10">
                            <input type="password" placeholder="password" id="password" name="password" value="{$password|escape:'htmlall':'UTF-8'}" />
                         </div>
                         <div class="col-lg-2">   
                            {if empty($password) and empty($no_password) }
                                <img src="../img/admin/status_orange.png" alt="" />
                            {elseif isset($no_password)}
                                <img src="../img/admin/status_red.png" alt="" />
                            {else}
                                <img src="../img/admin/status_green.png" alt="" />
                            {/if}
                        </div>
                        <div class="col-lg-10">
                            <input type="text" placeholder="appID" id="appID" name="appID" value="{$appID|escape:'htmlall':'UTF-8'}" />
                         </div>
                         <div class="col-lg-2">   
                            {if empty($appID) and empty($no_appID) }
                                <img src="../img/admin/status_orange.png" alt="" />
                            {elseif isset($no_appID)}
                                <img src="../img/admin/status_red.png" alt="" />
                            {else}
                                <img src="../img/admin/status_green.png" alt="" />
                            {/if}
                        </div>
                    </div>
                </fielset>
            </div>        
            <div class="col-lg-6">
                <fieldset>
                <legend class="text-center">{l s='Help' mod='phcxconnector'}:</legend>
                    <p class="text-justify">- {l s='Generate API Key in PHCX and save it here.' mod='phcxconnector'}</p>
                </fieldset>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="col-lg-6">
                <fieldset>
                <legend>{l s='Products' mod='phcxconnector'}:</legend>
                    <div class="form-group clearfix">
                        <label for="">{l s='Enable/disable syncronization' mod='phcxconnector'}:</label>
                            <img src="../img/admin/enabled.gif" alt="" />
                            <input type="radio" id="'enable_products_sync_1" name="enable_products_sync" value="1" {if $enable_products_sync eq 1}checked{/if}>
                            <label for="'enable_products_sync_1" class="t">{l s='Yes' mod='phcxconnector'}</label>
        
                            <img src="../img/admin/disabled.gif" alt="" />
                            <input type="radio" id="'enable_products_sync_0" name="enable_products_sync" value="0" {if empty($enable_products_sync) || $enable_products_sync eq 0}checked{/if}>
                            <label for="'enable_products_sync_0" class="t">{l s='No' mod='phcxconnector'}</label>
                    </div>
                </fielset>
            </div>
            <div class="col-lg-6">
                <fieldset>
                <legend>&nbsp;</legend>
                    <p class="text-justify">- {l s='Enable product integration and everytime you save your product it will be created in KeyInvoice.' mod='phcxconnector'}</p>
                </fieldset>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="col-lg-6">
                <fieldset>
                <legend>{l s='Customers' mod='phcxconnector'}:</legend>
                    <div class="form-group clearfix">
                        <label for="">{l s='Enable/disable syncronization' mod='phcxconnector'}:</label>
                        <img src="../img/admin/enabled.gif" alt="" />
                        <input type="radio" id="'enable_clients_sync_1" name="enable_clients_sync" value="1" {if $enable_clients_sync eq 1}checked{/if}>
                        <label for="'enable_clients_sync_1" class="t">{l s='Yes' mod='phcxconnector'}</label>
    
                        <img src="../img/admin/disabled.gif" alt="" />
                        <input type="radio" id="'enable_clients_sync_0" name="enable_clients_sync" value="0" {if empty($enable_clients_sync) || $enable_clients_sync eq 0}checked{/if}>
                        <label for="'enable_clients_sync_0" class="t">{l s='No' mod='phcxconnector'}</label>
                    </div>
                </fielset>
            </div>
            <div class="col-lg-6">
                <fieldset>
                <legend>&nbsp;</legend>
                    <p class="text-justify">- {l s='Enable customer integration and everytime you save customer information in fron-office or back-office your customer information will be created / updated in KeyInvoice.' mod='phcxconnector'}</p>
                </fieldset>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="col-lg-6">
                <fieldset>
                <legend>{l s='Orders' mod='phcxconnector'}:</legend>
                    <div class="form-group clearfix">
                        <label for="" >{l s='Enable/disable syncronization' mod='phcxconnector'}:</label>
                        <img src="../img/admin/enabled.gif" alt="" />
                        <input type="radio" id="'enable_orders_sync_1" name="enable_orders_sync" value="1" {if $enable_orders_sync eq 1}checked{/if}>
                        <label for="'enable_orders_sync_1" class="t">{l s='Yes' mod='phcxconnector'}</label>
    
                        <img src="../img/admin/disabled.gif" alt="" />
                        <input type="radio" id="'enable_orders_sync_0" name="enable_orders_sync" value="0" {if empty($enable_orders_sync) || $enable_orders_sync eq 0}checked{/if}>
                        <label for="'enable_orders_sync_0" class="t">{l s='No' mod='phcxconnector'}</label>
                    </div>
                    <div class="form-group clearfix">
                        <label for="">{l s='Default Document Type' mod='phcxconnector'}:</label>
                        {if $enable_orders_sync eq 1}
                            {html_options name=PHCXCONNECTOR_SHIP_DOC_TYPE options=$ShipdocOptions selected=$ShipdefaultSelect}
                        {else}
                            {html_options name=PHCXCONNECTOR_SHIP_DOC_TYPE disabled="disabled" options=$ShipdocOptions selected=$ShipdefaultSelect}
                            <input type="hidden" id="PHCXCONNECTOR_SHIP_DOC_TYPE_hidden" name="PHCXCONNECTOR_SHIP_DOC_TYPE" value="{13|escape:'htmlall':'UTF-8'}" />
                        {/if}
                    </div>
                    {*
                    <div class="form-group clearfix">
                        <label for="">{l s='Default Document Type' mod='phcxconnector'}:</label>
                        {if $enable_orders_sync eq 1}
                            {html_options name=PHCXCONNECTOR_INV_DOC_TYPE options=$InvdocOptions selected=$InvdefaultSelect}
                         {else}   
                            {html_options name=PHCXCONNECTOR_INV_DOC_TYPE disabled="disabled" options=$InvdocOptions selected=$InvdefaultSelect}
                         {/if}
                    </div>
                    *}
                </fielset>
            </div>
            <div class="col-lg-6">
                <fieldset>
                <legend>&nbsp;</legend>
                    <p class="text-justify">- {l s='Enable order integration and everytime you create a order in back-office or your customer creates a order in front-office the billing document will be created in KeyInvoice.' mod='phcxconnector'}</p>
                </fieldset>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="col-lg-6">
                <fieldset>
                <legend>{l s='General' mod='phcxconnector'}:</legend>
                    <div class="form-group clearfix">
                        <label>{l s='Shipping Reference' mod='phcxconnector'}:</label>
                        <input type="text" placeholder="Shipping Cost Mapper" id="PHCXCONNECTOR_SHIPPINGCOST" name="PHCXCONNECTOR_SHIPPINGCOST" value="{$PHCXCONNECTOR_SHIPPINGCOST|escape:'htmlall':'UTF-8'}" />
                    </div>
                </fielset>
            </div>        
            <div class="col-lg-6">
                <fieldset>
                <legend>&nbsp;</legend>
                    <p class="text-justify">- {l s='You must create a Product in PHCX as a "Shipping Cost Carrier". Save here is Reference' mod='phcxconnector'}</p>
                </fieldset>
            </div>
        </div>
        <div class="form-group clearfix">
            <div class="submit col-lg-12">
                <button type="submit" name="ptinvc_save_form" class="button btn btn-default button-medium"><span>{l s='Save' mod='phcxconnector'} <i class="icon-chevron-right right"></i></span></button>
            </div>
        </div>
    </form>
</div>
