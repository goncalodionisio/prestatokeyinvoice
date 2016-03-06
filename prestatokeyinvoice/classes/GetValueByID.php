<?php 

class PrestaToKeyInvoiceGetValueByID extends Module
{
    public static function getTaxByID($id_tax)
    {
        $tax_rate = DB::getInstance()->getValue("SELECT SUBSTRING_INDEX(rate,'.',1) as rate FROM `"._DB_PREFIX_."tax` WHERE `id_tax` = '".(int)$id_tax."'");
        return $tax_rate;
    }

    # Nos produtos não é passado o id_tax mas sim o id_tax_rules_group.
    public static function getTaxByRulesGroup($id)
    {
        $country = Configuration::get('PS_COUNTRY_DEFAULT');
        $id_tax = DB::getInstance()->getValue("SELECT id_tax FROM "._DB_PREFIX_."tax_rule where id_tax_rules_group = ". $id ." and id_country = ". $country);

        return PrestaToKeyInvoiceGetValueByID::getTaxByID($id_tax);
    }
}