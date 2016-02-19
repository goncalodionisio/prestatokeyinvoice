<div class="panel col-lg-6">
<h3 class="tab"> <i class="icon-info"></i> {l s='Presta To KeyInvoice' mod='prestatokeyinvoice'}</h3>
<div class="form-group clearfix">
	<form action="" method="post" id="send-order-keyinvoice" >
		<div class="submit">
			<button type="submit" name="process_sync_order" class="button btn btn-default button-medium"><span>{l s='Send Order' mod='prestatokeyinvoice'} <i class="icon-chevron-right right"></i></span></button>
		</div>
	</form>
</div>
{*
{if isset($cartProduct)}
<br>############## $cartProduct.product_name <br>
{foreach from=$cartProducts item=cartProduct}
	{$cartProduct.product_name};<br>
{/foreach}
{var_dump($cartProducts)}
{/if}
<br>############## orderCustomer <br>
{var_dump($orderCustomer)}
<br>############## id_address_delivery <br>
{var_dump($id_address_delivery)}
<br>############## id_address_invoice <br>
{var_dump($id_address_invoice)}
<br>################## address_delivery_fields <br>
{var_dump($address_delivery_fields)}
<br>################## address_invoice_fields <br>
{var_dump($address_invoice_fields)}

<br>################## result_header <br>
{var_dump($result_header)}
<br>################## result_header <br>
{$result_header_res}
<br>################## result_line <br>
{var_dump($result_line)}
*}
</div>