{if isset($confirmation_key)}
    <div class="alert alert-success">{l s='Settings updated' mod='prestatokeyinvoice'}</div>
{elseif isset($no_confirmation_key)}
    <div class="alert alert-danger">{l s='Key not recognized!' mod='prestatokeyinvoice'}</div>
{elseif isset($no_configuration_key)}
    <div class="alert alert-info">{l s='No API key set yet' mod='prestatokeyinvoice'}</div>
{/if}
{if isset($no_soap)}
    <div class="alert alert-danger">{l s='There was no comunication with Webservice! Try again later!' mod='prestatokeyinvoice'}</div>
{/if}

<div class="panel">
    <div class="panel-heading">
        <legend><img src="../img/admin/cog.gif" alt="" width="16" /> 
            {l s='Configuration' mod='prestatokeyinvoice'}
        </legend>
    </div>
    <form action="" method="post">
        <div class="col-lg-12">
	        <div class="col-lg-6">
	            <fieldset>
	            <legend>KIAPI Key:</legend>
	                <div class="form-group clearfix">
	                    <div class="col-lg-10">
	                        <input type="text" placeholder="kiapi_key" id="kiapi_key" name="kiapi_key" value="{$kiapi_key|escape:'htmlall':'UTF-8'}" />
	                     </div>
	                     <div class="col-lg-2">   
	                        {if empty($kiapi_key) and empty($no_confirmation_key) }
	                            <img src="../img/admin/status_orange.png" alt="" />
	                        {elseif isset($no_confirmation_key)}
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
	            <legend class="text-center">{l s='Help' mod='prestatokeyinvoice'}:</legend>
	        		<p class="text-justify">- {l s='Generate API Key in Keyinvoice and save it here.' mod='prestatokeyinvoice'}</p>
	        	</fieldset>
	        </div>
        </div>
        <div class="col-lg-12">
	        <div class="col-lg-6">
	            <fieldset>
	            <legend>{l s='Products' mod='prestatokeyinvoice'}:</legend>
	                <div class="form-group clearfix">
	                    <label for="">{l s='Enable/disable syncronization' mod='prestatokeyinvoice'}:</label>
	                        <img src="../img/admin/enabled.gif" alt="" />
	                        <input type="radio" id="'enable_products_sync_1" name="enable_products_sync" value="1" {if $enable_products_sync eq 1}checked{/if}>
	                        <label for="'enable_products_sync_1" class="t">{l s='Yes' mod='prestatokeyinvoice'}</label>
	    
	                        <img src="../img/admin/disabled.gif" alt="" />
	                        <input type="radio" id="'enable_products_sync_0" name="enable_products_sync" value="0" {if empty($enable_products_sync) || $enable_products_sync eq 0}checked{/if}>
	                        <label for="'enable_products_sync_0" class="t">{l s='No' mod='prestatokeyinvoice'}</label>
	                </div>
	            </fielset>
	        </div>
	        <div class="col-lg-6">
	        	<fieldset>
	            <legend>&nbsp;</legend>
	        		<p class="text-justify">- {l s='Help text about Products' mod='prestatokeyinvoice'}</p>
	        	</fieldset>
	        </div>
        </div>
        <div class="col-lg-12">
	        <div class="col-lg-6">
	            <fieldset>
	            <legend>{l s='Customers' mod='prestatokeyinvoice'}:</legend>
	                <div class="form-group clearfix">
	                    <label for="">{l s='Enable/disable syncronization' mod='prestatokeyinvoice'}:</label>
	                    <img src="../img/admin/enabled.gif" alt="" />
	                    <input type="radio" id="'enable_clients_sync_1" name="enable_clients_sync" value="1" {if $enable_clients_sync eq 1}checked{/if}>
	                    <label for="'enable_clients_sync_1" class="t">{l s='Yes' mod='prestatokeyinvoice'}</label>
	
	                    <img src="../img/admin/disabled.gif" alt="" />
	                    <input type="radio" id="'enable_clients_sync_0" name="enable_clients_sync" value="0" {if empty($enable_clients_sync) || $enable_clients_sync eq 0}checked{/if}>
	                    <label for="'enable_clients_sync_0" class="t">{l s='No' mod='prestatokeyinvoice'}</label>
	                </div>
	            </fielset>
	        </div>
	        <div class="col-lg-6">
	        	<fieldset>
	            <legend>&nbsp;</legend>
	        		<p class="text-justify">- {l s='Help text about Customers' mod='prestatokeyinvoice'}</p>
	        	</fieldset>
	        </div>
        </div>
        <div class="col-lg-12">
	        <div class="col-lg-6">
	            <fieldset>
	            <legend>{l s='Orders' mod='prestatokeyinvoice'}:</legend>
	                <div class="form-group clearfix">
	                    <label for="" >{l s='Enable/disable syncronization' mod='prestatokeyinvoice'}:</label>
	                    <img src="../img/admin/enabled.gif" alt="" />
	                    <input type="radio" id="'enable_orders_sync_1" name="enable_orders_sync" value="1" {if $enable_orders_sync eq 1}checked{/if}>
	                    <label for="'enable_orders_sync_1" class="t">{l s='Yes' mod='prestatokeyinvoice'}</label>
	
	                    <img src="../img/admin/disabled.gif" alt="" />
	                    <input type="radio" id="'enable_orders_sync_0" name="enable_orders_sync" value="0" {if empty($enable_orders_sync) || $enable_orders_sync eq 0}checked{/if}>
	                    <label for="'enable_orders_sync_0" class="t">{l s='No' mod='prestatokeyinvoice'}</label>
	                </div>
	                <div class="form-group clearfix">
	                    <label for="">{l s='Default Shipping docType' mod='prestatokeyinvoice'}:</label>
	                    {if $enable_orders_sync eq 1}
	                        {html_options name=PRESTATOKEYINVOICE_SHIP_DOC_TYPE options=$ShipdocOptions selected=$ShipdefaultSelect}
	                    {else}
	                        {html_options name=PRESTATOKEYINVOICE_SHIP_DOC_TYPE disabled="disabled" options=$ShipdocOptions selected=$ShipdefaultSelect}
	                    {/if}
	                </div>
	                <div class="form-group clearfix">
	                    <label for="">{l s='Default Invoice docType' mod='prestatokeyinvoice'}:</label>
	                    {if $enable_orders_sync eq 1}
	                        {html_options name=PRESTATOKEYINVOICE_INV_DOC_TYPE options=$InvdocOptions selected=$InvdefaultSelect}
	                     {else}   
	                        {html_options name=PRESTATOKEYINVOICE_INV_DOC_TYPE disabled="disabled" options=$InvdocOptions selected=$InvdefaultSelect}
	                     {/if}
	                </div>
	            </fielset>
	        </div>
	        <div class="col-lg-6">
	        	<fieldset>
	            <legend>&nbsp;</legend>
	        		<p class="text-justify">- {l s='Help text about Orders' mod='prestatokeyinvoice'}</p>
	        	</fieldset>
	        </div>
        </div>
        <div class="col-lg-12">
	        <div class="col-lg-6">
	            <fieldset>
	            <legend>{l s='General' mod='prestatokeyinvoice'}:</legend>
	                <div class="form-group clearfix">
	                    <label>Shipping Reference:</label>
	                    <input type="text" placeholder="Shipping Cost Mapper" id="PRESTATOKEYINVOICE_SHIPPINGCOST" name="PRESTATOKEYINVOICE_SHIPPINGCOST" value="{$PRESTATOKEYINVOICE_SHIPPINGCOST|escape:'htmlall':'UTF-8'}" />
	                </div>
	            </fielset>
	        </div>        
	        <div class="col-lg-6">
	            <fieldset>
	            <legend>&nbsp;</legend>
	        		<p class="text-justify">- {l s='You must create a Product in Keyinvoice as a "Shipping Cost Carrier". Save here is Reference' mod='prestatokeyinvoice'}</p>
	        	</fieldset>
	        </div>
        </div>
        <div class="form-group clearfix">
	        <div class="submit col-lg-12">
	            <button type="submit" name="ptinvc_save_form" class="button btn btn-default button-medium"><span>{l s='Save' mod='prestatokeyinvoice'} <i class="icon-chevron-right right"></i></span></button>
	        </div>
        </div>
    </form>
</div>
