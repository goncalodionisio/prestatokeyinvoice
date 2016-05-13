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

class ClientToPTInvoice extends Module
{

    public static function upsertClient(
        $nome,
        $ncont,
        $telefone,
        $tlmvl,
        $email,
        $iban,
        $nib,
        $morada,
        $morada2,
        $local,
        $codpost,
        $pais,
        $obs
    ) {


        /*
            "nome": "A Contradança, S.A.",
            "ncont": "206677324",   nif
            "telefone": "322 645 635",
            "tlmvl": "922 373 838",
            "email": "contradanca@musica.pt",
            "iban": "",
            "nib": "",
            "morada": "Praça do Município, N4",
            "morada2": "",
            "local": "Porto",
            "codpost": "4100-334",
            "pais": "Portugal",
        */

        // check if exists to always upsert
        $ptinvoiceOps = new PTInvoiceOperations();
        $response = $ptinvoiceOps->login();

        if ($response[0] == "nok")
            return $response;

        // client exists
        $response = $ptinvoiceOps->query("ClWS", array(array('column' => 'ncont', 'value' => $ncont)));
        $status = PTInvoiceOperations::ResponseStatus($response);
        if ($status[0] == 'nok') { return $status; }

        if (count($response['result']) != 0) {
            $ststamp = $response['result'][0]['clstamp'];

            $status = PTInvoiceOperations::ResponseStatus($ptinvoiceOps->update("ClWS", $ststamp, "nome", '"'.$nome.'"'));  if ($status[0] == 'nok') { return $status; }
            $status = PTInvoiceOperations::ResponseStatus($ptinvoiceOps->update("ClWS", $ststamp, "ncont", $ncont));        if ($status[0] == 'nok') { return $status; }

            if (isset($telefone)) {$status = PTInvoiceOperations::ResponseStatus($ptinvoiceOps->update("ClWS", $ststamp, "telefone", '"' . $telefone . '"'));   if ($status[0] == 'nok') { return $status; }}
            if (isset($tlmvl)) {$status = PTInvoiceOperations::ResponseStatus($ptinvoiceOps->update("ClWS", $ststamp, "tlmvl", '"' . $tlmvl . '"'));            if ($status[0] == 'nok') { return $status; }}
            if (isset($email)) {$status = PTInvoiceOperations::ResponseStatus($ptinvoiceOps->update("ClWS", $ststamp, "email", '"' . $email . '"'));            if ($status[0] == 'nok') { return $status; }}
            if (isset($iban)) {$status = PTInvoiceOperations::ResponseStatus($ptinvoiceOps->update("ClWS", $ststamp, "iban", '"' . $iban . '"'));               if ($status[0] == 'nok') { return $status; }}
            if (isset($nib)) {$status = PTInvoiceOperations::ResponseStatus($ptinvoiceOps->update("ClWS", $ststamp, "nib", '"' . $nib . '"'));                  if ($status[0] == 'nok') { return $status; }}

            $status = PTInvoiceOperations::ResponseStatus($ptinvoiceOps->update("ClWS", $ststamp, "morada",  '"' . $morada . '"'));         if ($status[0] == 'nok') { return $status; }
            $status = PTInvoiceOperations::ResponseStatus($ptinvoiceOps->update("ClWS", $ststamp, "morada2", '"' . $morada2 . '"'));        if ($status[0] == 'nok') { return $status; }
            $status = PTInvoiceOperations::ResponseStatus($ptinvoiceOps->update("ClWS", $ststamp, "local", '"' . $local . '"'));            if ($status[0] == 'nok') { return $status; }
            $status = PTInvoiceOperations::ResponseStatus($ptinvoiceOps->update("ClWS", $ststamp, "codpost", '"' . $codpost . '"'));        if ($status[0] == 'nok') { return $status; }
            $status = PTInvoiceOperations::ResponseStatus($ptinvoiceOps->update("ClWS", $ststamp, "obs", '"' . $obs . '"'));                if ($status[0] == 'nok') { return $status; }
            $status = PTInvoiceOperations::ResponseStatus($result = $ptinvoiceOps->update("ClWS", $ststamp, "pais", '"' . $pais . '"'));    if ($status[0] == 'nok') { return $status; }

            $result = $ptinvoiceOps->save("ClWS", $result['result'][0]);

            $ptinvoiceOps->logout();
            return PTInvoiceOperations::ResponseStatus($result);
        } else {
            // new customer
            $result = $ptinvoiceOps->newInstance("ClWS",$params = array ('ndos' => 0));

            $result['result'][0]['nome'] = $nome;
            $result['result'][0]['ncont'] = $ncont;
            if (isset($telefone))   { $result['result'][0]['telefone'] = $telefone; }
            if (isset($tlmvl))      { $result['result'][0]['tlmvl'] = $tlmvl; }
            if (isset($email))      { $result['result'][0]['email'] = $email; }
            if (isset($iban))       { $result['result'][0]['iban'] = $iban; }
            if (isset($nib))        { $result['result'][0]['nib'] = $nib; }
            $result['result'][0]['morada'] = $morada;
            $result['result'][0]['morada2'] = $morada2;
            $result['result'][0]['local'] = $local;
            $result['result'][0]['codpost'] = $codpost;
            $result['result'][0]['pais'] = $pais;

            $result = $ptinvoiceOps->save("ClWS", $result['result'][0]);

            $ptinvoiceOps->logout();
            return PTInvoiceOperations::ResponseStatus($result);
        }

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
        $country    = isset($address->country) ? $address->country : "";

        $obs        = isset($address->other) ? $address->other : "";
        $obs        = "Cliente criado via PTInvoice Connector";
        $phone      = isset($address->phone) ? $address->phone : "";

        /*********************************************************************/
        $name = $company == "" ? ($first_name . " " . $last_name) : $company;
        $fax = "";
        $email = null; # detail is empty
        $tlmvl = null;
        $iban = null;
        $nib = null;

        if (Validate::isLoadedObject($customer = new Customer($address->id_customer))) {
            $email = $customer->email;
        }

        if ($vat == "") {
            return array(0, "numero VAT tem de ser preenchido");
        }

        /*
            "nome": "A Contradança, S.A.",
            "ncont": "206677324",   nif
            "telefone": "322 645 635",
            "tlmvl": "922 373 838",
            "email": "contradanca@musica.pt",
            "iban": "",
            "nib": "",
            "morada": "Praça do Município, N4",
            "morada2": "",
            "local": "Porto",
            "codpost": "4100-334",
            "pais": "Portugal",
            "obs": "",
        */

        return ClientToPTInvoice::upsertClient(
            $name,
            $vat,
            $phone,
            $tlmvl,
            $email,
            $iban,
            $nib,
            $address1,
            $address2,
            $city,
            $postcode,
            $country,
            $obs
        );
    }

    public static function saveByIdAddress($idAddress)
    {
        if (Validate::isLoadedObject($address = new Address($idAddress))) {
            return ClientToPTInvoice::saveByAddressObject($address);
        }

        return null;
    }

    public static function getAddress($ncont)
    {
        // check if exists to always upsert
        $ptinvoiceOps = new PTInvoiceOperations();
        $response = $ptinvoiceOps->login();

        if ($response[0] == "nok") {
            $ptinvoiceOps->logout();
            return null;
        }

        // client exists
        $response = $ptinvoiceOps->query("ClWS", array(array('column' => 'ncont', 'value' => $ncont)));

        $ptinvoiceOps->logout();

        $status = PTInvoiceOperations::ResponseStatus($response);
        if ($status[0] == 'nok' || count($response['result']) == 0) {
            return null;
        }

        return $response['result'];
    }


}
