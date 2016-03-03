<?php

class ClientToKeyInvoice extends Module
{

    public static function upsertClient($nif, $country, $name, $address, $postalCode, $locality, $phone, $fax, $email, $obs)
    {

        if (!$client = ConfigsValidation::APIWSClient())
            return false;
        
        if (!$session = ConfigsValidation::APIWSSession($client, 'ClientToKeyInvoice'))
            return false;
        
        // check if exists to always upsert
        $clientExists = $client->clientExists("$session", "$nif");
        $result = $clientExists[0];


        if ($result == 1) {
            $result = $client->updateClient("$session", "$nif", "$name", "$address", "$postalCode", "$locality", "$phone", "$fax", "$email", "$obs");

        } else {
            $result = $client->insertClient("$session", "$nif", "$name", "$address", "$postalCode", "$locality", "$phone", "$fax", "$email", "$obs");

            // não faz validação do nif mas não aceita o country por isso merda para isto
            //$result = $client->insertForeignClient("$session", "$nif", "$country", "$name", "$address", "$postalCode", "$locality", "$phone", "$fax", "$email", "$obs");
        }

        return $result;
    }

    public static function saveByAddressObject($address) {

        $company    = isset($address->company) ? $address->company : "";
        $first_name = isset($address->firstname) ? $address->firstname : "";
        $last_name  = isset($address->lastname) ? $address->lastname : "";

        $address1   = isset($address->address1) ? $address->address1 : "";
        $address2   = isset($address->address2) ? $address->address2 : "";

        $nif        = isset($address->dni) ? $address->dni : "";
        $vat        = isset($address->vat_number) ? $address->vat_number : "";

        $postcode   = isset($address->postcode) ? $address->postcode : "";
        $city       = isset($address->city) ? $address->city : "";
        $country    = isset($address->country) ? $address->country : "";

        $obs        = isset($address->other) ? $address->other : "";
        $phone      = isset($address->phone) ? $address->phone : "";

        // transformações das colunas
        $address = $address1 . ", " . $address2;
        $name = $company == "" ? ($first_name . " " . $last_name) : $company;
        $locality = $city;
        $fax = "";
        $email = "";

        if ($vat == "")
            return array(0, "numero VAT tem de ser preenchido");

        // se o vat number não estiver preenchido então enviar erro.

        //$country_code = DB::getInstance()->getValue("SELECT iso_code FROM "._DB_PREFIX_."country a, "._DB_PREFIX_."country_lang b WHERE a.id_country = b.id_country and b.name = '".$country."'");
        $country_code = "PT";

        return ClientToKeyInvoice::upsertClient($vat, $country_code, $name, $address, $postcode, $locality, $phone, $fax, $email, $obs);
    }

    public static function saveByIdAddress($idAddress)
    {
        if (Validate::isLoadedObject($address = new Address($idAddress))) {
            return ClientToKeyInvoice::saveByAddressObject($address);
        }

        return null;
    }
}