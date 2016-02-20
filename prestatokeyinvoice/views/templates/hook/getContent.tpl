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

<fieldset>
	<h2>{l s='Module configuration' mod='prestatokeyinvoice'}</h2>
	<div class="panel">
		<div class="panel-heading">
			<legend><img src="../img/admin/cog.gif" alt="" width="16" /> 
				{l s='Configuration' mod='prestatokeyinvoice'}
			</legend>
		</div>
		<form action="" method="post">
			<div class="form-group clearfix">
				<label class="col-lg-2">KIAPI Key:</label>
				<div class="col-lg-8">
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



			<br>

			<div class="form-group clearfix">
				<label for="" class="col-lg-3">{l s='Enable products syncronization' mod='prestatokeyinvoice'}:</label>
				<div class="col-lg-9">
					<img src="../img/admin/enabled.gif" alt="" />
					<input type="radio" id="'enable_products_sync_1" name="enable_products_sync" value="1" {if $enable_products_sync eq 1}checked{/if}>
					<label for="'enable_products_sync_1" class="t">{l s='Yes' mod='prestatokeyinvoice'}</label>

					<img src="../img/admin/disabled.gif" alt="" />
					<input type="radio" id="'enable_products_sync_0" name="enable_products_sync" value="0" {if empty($enable_products_sync) || $enable_products_sync eq 0}checked{/if}>
					<label for="'enable_products_sync_0" class="t">{l s='No' mod='prestatokeyinvoice'}</label>
				</div>
			</div>

			<div class="form-group clearfix">
				<label for="" class="col-lg-3">{l s='Enable clients syncronization' mod='prestatokeyinvoice'}:</label>
				<div class="col-lg-9">
					<img src="../img/admin/enabled.gif" alt="" />
					<input type="radio" id="'enable_clients_sync_1" name="enable_clients_sync" value="1" {if $enable_clients_sync eq 1}checked{/if}>
					<label for="'enable_clients_sync_1" class="t">{l s='Yes' mod='prestatokeyinvoice'}</label>

					<img src="../img/admin/disabled.gif" alt="" />
					<input type="radio" id="'enable_clients_sync_0" name="enable_clients_sync" value="0" {if empty($enable_clients_sync) || $enable_clients_sync eq 0}checked{/if}>
					<label for="'enable_clients_sync_0" class="t">{l s='No' mod='prestatokeyinvoice'}</label>
				</div>
			</div>

			<div class="form-group clearfix">
				<label for="" class="col-lg-3">{l s='Enable orders syncronization' mod='prestatokeyinvoice'}:</label>
				<div class="col-lg-9">
					<img src="../img/admin/enabled.gif" alt="" />
					<input type="radio" id="'enable_orders_sync_1" name="enable_orders_sync" value="1" {if $enable_orders_sync eq 1}checked{/if}>
					<label for="'enable_orders_sync_1" class="t">{l s='Yes' mod='prestatokeyinvoice'}</label>

					<img src="../img/admin/disabled.gif" alt="" />
					<input type="radio" id="'enable_orders_sync_0" name="enable_orders_sync" value="0" {if empty($enable_orders_sync) || $enable_orders_sync eq 0}checked{/if}>
					<label for="'enable_orders_sync_0" class="t">{l s='No' mod='prestatokeyinvoice'}</label>
				</div>
			</div>

			<div class="submit">
				<button type="submit" name="ptinvc_save_form" class="button btn btn-default button-medium"><span>{l s='Save' mod='prestatokeyinvoice'} <i class="icon-chevron-right right"></i></span></button>
			</div>

		</form>

	</div>
</fieldset>
