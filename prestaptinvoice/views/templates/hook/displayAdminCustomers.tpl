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

{if isset($send_to_pt_invoice_confirmation)}
    <div class="alert alert-success">{l s='Address Saved' mod='prestaptinvoice'}</div>
{/if}

<div class="col-lg-6">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-eye"></i>
            {l s='Customer Address Integration with PHCFX' mod='prestaptinvoice'}
        </div>
        <form action="" method="post">
            <table class="table">
                <thead></thead>
                <tbody>
                {foreach $address_list as $address}
                    <tr>
                        <td>{$address.vat_number|escape:'htmlall':'UTF-8'}</td>
                        <td>{$address.company|escape:'htmlall':'UTF-8'}</td>
                        <td>{$address.firstname|escape:'htmlall':'UTF-8'} {$address.lastname|escape:'htmlall':'UTF-8'}</td>
                        <td>{$address.address1|escape:'htmlall':'UTF-8'}, {$address.address2|escape:'htmlall':'UTF-8'}</td>
                        <td>
                            <input type="radio" name="ptinvoice_address_radio" value="{$address.id_address|escape:'htmlall':'UTF-8'}" {if $selected_address eq $address.id_address}checked{/if}>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
            <button type="submit" name="ptinvoice_save_address" class="button btn btn-default button-medium">
                <span>
                    {l s='Send to PHCFX' mod='prestaptinvoice'}
                    <i class="icon-chevron-right right"></i>
                </span>
            </button>
        </form>
    </div>
</div>