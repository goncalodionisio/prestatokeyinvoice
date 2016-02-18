<div class="col-lg-6">
{if isset($no_soap)}
	<div class="alert alert-danger">{l s='There was no comunication with Webservice! Try again later!' mod='prestatokeyinvoice'}</div>
{/if}
{if isset($result)}
	<div class="alert alert-info">
	{foreach from=$result item=results}
		{$results.message|escape:'htmlall':'UTF-8'}
	{/foreach}
	</div>
{/if}
{if isset($product)}
	{var_dump($product|escape:'htmlall':'UTF-8')}
{/if}
<fieldset>
	<div class="panel">
		<div class="panel-heading">
			<legend> 
				{l s='Product Integration with KeyInvoice' mod='prestatokeyinvoice'}
			</legend>
		</div>
		<div class="form-group clearfix">
			<form action="" method="post" id="send-product-keyinvoice" >
				<div class="submit">
					<button type="submit" name="process_product_form" class="button btn btn-default button-medium"><span>{l s='Send Product' mod='prestatokeyinvoice'} <i class="icon-chevron-right right"></i></span></button>
				</div>
			</form>
		</div>
	</div>
	{*
	<div class="panel">
		<div><h4>Ref: </h4><p class="text-justify">{$ref|escape:'htmlall':'UTF-8'}</p></div>
		<div><h4>Designation: </h4><p class="text-justify">{$designation|escape:'htmlall':'UTF-8'}</p></div>
		<div><h4>ShortName: </h4><p class="text-justify">{$shortName|escape:'htmlall':'UTF-8'}</p></div>
		<div><h4>TAX: </h4><p class="text-justify">{$tax}</p></div>
		<div><h4>ShortDesc: </h4><p class="text-justify">{$shortDesc|escape:'htmlall':'UTF-8'}</p></div>
		<div><h4>LongDesc: </h4><p class="text-justify">{$longDesc|escape:'htmlall':'UTF-8'}</p></div>
		<div><h4>Price: </h4><span class="text-justify">{$price|escape:'htmlall':'UTF-8'}</span></div>
		<div><h4>VendorRef: </h4><span class="text-justify">{$vendorRef|escape:'htmlall':'UTF-8'}</span></div>
		<div><h4>EAN: </h4><span class="text-justify">{$ean|escape:'htmlall':'UTF-8'}</span></div>
		
	</div>
	*}
</fieldset>
</div>