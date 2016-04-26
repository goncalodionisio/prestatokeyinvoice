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
 *  @author    Majoinfa - Sociedade Unipessoal Lda
 *  @copyright 2016-2021 Majoinfa - Sociedade Unipessoal Lda
 *  @license   LICENSE.txt
*/

class ClientToPHCX extends Module
{

    public static function upsertClient(
        $nif,
        $country_code,
        $name,
        $address,
        $postalCode,
        $locality,
        $phone,
        $fax,
        $email,
        $obs
    ) {

        if (!$client = ConfigsValidation::APIWSClient()) {
            return false;
        }
        
        if (!$session = ConfigsValidation::APIWSSession($client, 'ClientToPHCX')) {
            return false;
        }
        
        // check if exists to always upsert
        $clientExists = $client->clientExists("$session", "$nif");
        $result = $clientExists[0];
        


        if ($result == 1) {
            $result = $client->updateClient(
                "$session",
                "$nif",
                "$name",
                "$address",
                "$postalCode",
                "$locality",
                "$phone",
                "$fax",
                "$email",
                "$obs"
            );

        } else {
            $result = $client->insertClient(
                "$session",
                "$nif",
                "$name",
                "$address",
                "$postalCode",
                "$locality",
                "$phone",
                "$fax",
                "$email",
                "$obs"
            );

            /**
             * não faz validação do nif mas não aceita o country por isso merda para isto
             * $result = $client->insertForeignClient("$session", "$nif", "$country", 
             * "$name", "$address", "$postalCode", "$locality", "$phone", "$fax", "$email", "$obs");
            */
        }

        return $result;
    }

    public static function saveByAddressObject($address)
    {

        $company    = isset($address->company) ? utf8_encode($address->company) : "";

        $first_name = isset($address->firstname) ? utf8_encode($address->firstname) : "";
        $last_name  = isset($address->lastname) ? utf8_encode($address->lastname) : "";

        $address1   = isset($address->address1) ? utf8_encode($address->address1) : "";
        $address2   = isset($address->address2) ? utf8_encode($address->address2) : "";

        #$nif        = isset($address->dni) ? $address->dni : "";
        $vat        = isset($address->vat_number) ? $address->vat_number : "";

        $postcode   = isset($address->postcode) ? $address->postcode : "";
        $city       = isset($address->city) ? $address->city : "";
        //$country    = isset($address->country) ? $address->country : "";

        # $obs        = isset($address->other) ? $address->other : "";
        $obs        = "Cliente criado via PHCX Connector";
        $phone      = isset($address->phone) ? $address->phone : "";

        // transformações das colunas
        $full_address = $address1 . ", " . $address2;
        $name = $company == "" ? ($first_name . " " . $last_name) : $company;
        $locality = $city;
        $postcode = $postcode ." ". $city;
        $fax = "";
        $email = ""; # detail is empty

        if (Validate::isLoadedObject($customer = new Customer($address->id_customer))) {
            $email = $customer->email;
        }

        if ($vat == "") {
            return array(0, "numero VAT tem de ser preenchido");
        }

        /**
         * se o vat number não estiver preenchido então enviar erro.
         * $country_code = DB::getInstance()->getValue(
         * "SELECT iso_code FROM "._DB_PREFIX_."country a, 
         * "._DB_PREFIX_."country_lang b WHERE a.id_country = b.id_country and b.name = '".$country."'");
         */
        
        $country_code = "PT";

        return ClientToPHCX::upsertClient(
            $vat,
            $country_code,
            $name,
            $full_address,
            $postcode,
            $locality,
            $phone,
            $fax,
            $email,
            $obs
        );
    }

    public static function saveByIdAddress($idAddress)
    {
        if (Validate::isLoadedObject($address = new Address($idAddress))) {
            return ClientToPHCX::saveByAddressObject($address);
        }

        return null;
    }
}
