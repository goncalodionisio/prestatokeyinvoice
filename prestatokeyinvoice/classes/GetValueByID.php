<?php 

class PrestaToKeyInvoiceGetValueByID extends Module
{
    public static function getTaxByID($id_tax)
    {
        $tax_rate = DB::getInstance()->getValue("SELECT SUBSTRING_INDEX(rate,'.',1) as rate FROM `"._DB_PREFIX_."tax` WHERE `id_tax` = '".(int)$id_tax."'");
        return $tax_rate;
    }
}