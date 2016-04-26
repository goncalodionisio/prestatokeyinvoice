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

{if isset($confirmation_ok)}
    <div class="alert alert-success">{l s='Order Created in PHCX' mod='phcxconnector'}</div>
{/if}
<div class="panel col-lg-6">
    <h3 class="tab"> <i class="icon-info"></i> {l s='KeyInvoice Connector' mod='phcxconnector'}</h3>
    <div class="form-group clearfix">
        <form action="" method="post" id="send-order-keyinvoice" >
        <div class="col-lg-12">
            <label for="" class="col-lg-4">{l s='Document Type' mod='phcxconnector'}:</label>
            <div class="col-lg-8">
                {html_options name=PHCXCONNECTOR_SHIP_DOC_TYPE options=$ShipdocOptions selected=$ShipdefaultSelect}
            </div>
        </div>
        {*
        <div class="col-lg-12">
            <label for="" class="col-lg-4">{l s='Document Type' mod='phcxconnector'}:</label>
            <div class="col-lg-8">
                {html_options name=PHCXCONNECTOR_INV_DOC_TYPE options=$InvdocOptions selected=$InvdefaultSelect}
            </div> 
        </div> 
        *}
         <div class="col-lg-12">  
            <div class="submit">
                <button type="submit" name="process_sync_order" class="button btn btn-default button-medium"><span>{l s='Send to KeyInvoice' mod='phcxconnector'} <i class="icon-chevron-right right"></i></span></button>
            </div>
        </div>
        </form>
    </div>
</div>
