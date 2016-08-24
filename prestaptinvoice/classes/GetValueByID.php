<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    Majoinfa - Sociedade Unipessoal Lda
 * @copyright 2016-2021 Majoinfa - Sociedade Unipessoal Lda
 * @license   LICENSE.txt
 */

class PTInvoiceConnectorGetValueByID extends Module
{
    /**
     * @param $id_tax
     *
     * @return false|null|string
     *
     * @since version
     */
    public static function getTaxByID($id_tax)
    {
        $tax_rate = DB::getInstance()->getValue(
            "SELECT SUBSTRING_INDEX(rate,'.',1) as rate 
              FROM `" . _DB_PREFIX_ . "tax` 
              WHERE `id_tax` = '" . (int)$id_tax . "'"
        );
        return $tax_rate;
    }


    /**
     * Nos produtos não é passado o id_tax mas sim o id_tax_rules_group.
     * @param $id
     *
     *
     * @return false|null|string
     *
     * @since version
     */
    public static function getTaxByRulesGroup($id)
    {
        $country = Configuration::get('PS_COUNTRY_DEFAULT');
        $id_tax = DB::getInstance()->getValue(
            "SELECT id_tax FROM " . _DB_PREFIX_ . "tax_rule where 
            id_tax_rules_group = " . (int)$id . " and id_country = " . $country
        );

        return PTInvoiceConnectorGetValueByID::getTaxByID($id_tax);
    }
}
