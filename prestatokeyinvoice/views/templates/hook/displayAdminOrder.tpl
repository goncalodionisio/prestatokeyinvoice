<div class="panel">
<br>############## $cartProduct.product_name <br>
{foreach from=$cartProducts item=cartProduct}
	{$cartProduct.product_name};<br>
{/foreach}
<br>############## id_address_delivery <br>
{var_dump($id_address_delivery)}
<br>############## id_address_invoice <br>
{var_dump($id_address_invoice)}
<br>################## address_delivery_fields <br>
{var_dump($address_delivery_fields)}
<br>################## address_invoice_fields <br>
{var_dump($address_invoice_fields)}

</div>