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
    <div class="alert alert-danger">{l s='There was no comunication with Webservice! Try again later!' mod='keyinvoiceconnector'}</div>
{/if}
{if isset($result)}
    <div class="alert alert-info">
    {foreach from=$result item=results}
        {$results.message|escape:'htmlall':'UTF-8'}
    {/foreach}
    </div>
{/if}
{if isset($productExists)}
    {var_dump($productExists|escape:'htmlall':'UTF-8')}
{/if}
<fieldset>
    <div class="panel">
        <div class="panel-heading">
            <legend> 
                {l s='Product Integration with KeyInvoice' mod='keyinvoiceconnector'}
            </legend>
        </div>
        <div class="form-group clearfix">
            <form action="" method="post" id="send-product-keyinvoice" >
                <input type="hidden" name="ref" value="{$ref|escape:'htmlall':'UTF-8'}" />
                <input type="hidden" name="designation" value="{$designation|escape:'htmlall':'UTF-8'}" />
                <input type="hidden" name="shortName" value="{$shortName|escape:'htmlall':'UTF-8'}" />
                <input type="hidden" name="tax" value="{$tax|escape:'htmlall':'UTF-8'}" />
                <input type="hidden" name="obs" value="{$obs|escape:'htmlall':'UTF-8'}" />
                <input type="hidden" name="isService" value="{$isService|escape:'htmlall':'UTF-8'}" />
                <input type="hidden" name="hasStocks" value="{$hasStocks|escape:'htmlall':'UTF-8'}" />
                <input type="hidden" name="active" value="{$active|escape:'htmlall':'UTF-8'}" />
                <input type="hidden" name="shortDesc" value="{$shortDesc|escape:'htmlall':'UTF-8'}" />
                <input type="hidden" name="longDesc" value="{$longDesc|escape:'htmlall':'UTF-8'}" />
                <input type="hidden" name="price" value="{$price|escape:'htmlall':'UTF-8'}" />
                <input type="hidden" name="vendorRef" value="{$vendorRef|escape:'htmlall':'UTF-8'}" />
                <input type="hidden" name="ean" value="{$ean|escape:'htmlall':'UTF-8'}" />
                <input type="hidden" name="kiapi_key" value="{$kiapi_key|escape:'htmlall':'UTF-8'}" />
                <div class="submit">
                    <button type="submit" name="process_product_form" class="button btn btn-default button-medium"><span>{l s='Send Product' mod='keyinvoiceconnector'} <i class="icon-chevron-right right"></i></span></button>
                </div>
            </form>
        </div>
    </div>
    <div class="panel">
        <div><h4>Ref: </h4><p class="text-justify">{$ref|escape:'htmlall':'UTF-8'}</p></div>
        <div><h4>Designation: </h4><p class="text-justify">{$designation|escape:'htmlall':'UTF-8'}</p></div>
        <div><h4>ShortName: </h4><p class="text-justify">{$shortName|escape:'htmlall':'UTF-8'}</p></div>
        <div><h4>TAX: </h4><p class="text-justify">{$tax|escape:'htmlall':'UTF-8'}</p></div>
        <div><h4>ShortDesc: </h4><p class="text-justify">{$shortDesc|escape:'htmlall':'UTF-8'}</p></div>
        <div><h4>LongDesc: </h4><p class="text-justify">{$longDesc|escape:'htmlall':'UTF-8'}</p></div>
        <div><h4>Price: </h4><span class="text-justify">{$price|escape:'htmlall':'UTF-8'}</span></div>
        <div><h4>VendorRef: </h4><span class="text-justify">{$vendorRef|escape:'htmlall':'UTF-8'}</span></div>
        <div><h4>EAN: </h4><span class="text-justify">{$ean|escape:'htmlall':'UTF-8'}</span></div>
        
    </div>
</fieldset>
</div>