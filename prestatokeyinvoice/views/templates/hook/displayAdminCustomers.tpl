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
<div class="col-lg-6">
	{if isset($no_soap)}
		<div class="alert alert-danger">{l s='There was no comunication with Webservice! Try again later!' mod='prestatokeyinvoice'}</div>
	{/if}
	{if isset($result)}
		<div class="alert alert-info">
		{foreach from=$result item=results}
			{$results.message}
		{/foreach}
		</div>
	{/if}
	<fieldset>
		<div class="panel">
			<div class="panel-heading">
				<legend> 
					{l s='Customer Integration with KeyInvoice' mod='prestatokeyinvoice'}
				</legend>
			</div>
			<div class="form-group clearfix">
			<form action="" method="post" id="send-client-keyinvoice">			
				<label class="col-lg-2 ">API:</label>
				<input type="hidden" name="nif" value="{$nif}" />
				<input type="hidden" name="name" value="{$name}" />
				<input type="hidden" name="address" value="{$address}" />
				<input type="hidden" name="postalCode" value="{$postalCode}" />
				<input type="hidden" name="locality" value="{$locality}" />
				<input type="hidden" name="phone" value="{$phone}" />
				<input type="hidden" name="fax" value="{$fax}" />
				<input type="hidden" name="email" value="{$email}" />
				<input type="hidden" name="obs" value="{$obs}" />
				<input type="hidden" name="kiapi_key" value="{$kiapi_key}" />
				<div class="submit">
						<button type="submit" name="process_client_form" class="button btn btn-default button-medium"><span>{l s='Send Client' mod='prestatokeyinvoice'} <i class="icon-chevron-right right"></i></span></button>
					</div>
			</form>
			</div>
		</div>
		<div class="panel">
			<div><h4>Nif: </h4><p class="text-justify">{$nif}</p></div>
			<div><h4>Name: </h4><p class="text-justify">{$name}</p></div>
			<div><h4>Address: </h4><p class="text-justify">{$address}</p></div>
			<div><h4>Postal Code: </h4><p class="text-justify">{$postalCode}</p></div>
			<div><h4>Locality: </h4><p class="text-justify">{$locality}</p></div>
			<div><h4>Phone: </h4><p class="text-justify">{$phone}</p></div>
			<div><h4>Fax: </h4><span class="text-justify">{$fax}</span></div>
			<div><h4>Email: </h4><span class="text-justify">{$email}</span></div>
			<div><h4>Obs: </h4><span class="text-justify">{$obs}</span></div>
			
		</div>
	</fieldset>
</div>