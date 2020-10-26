<?php

//Now start executing the function
testSMS();

function testSMS($mobnr) {

    //authoriseren   
    $client_id="your client-id";   
    $client_secret="your secret";
    $ch=curl_init();
    curl_setopt($ch, CURLOPT_URL,"https://api-prd.kpn.com/oauth/client_credential/accesstoken?grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,"client_id=".$client_id."&client_secret=".$client_secret);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response=json_decode(curl_exec($ch));
    curl_close ($ch);
    if (!is_object($response)) {
        setError(54,array('sendSMS('.$mobnr.') mislukt. Foute response van KPN Authorisatie',$response));
        return false;
    }
    $token_type=$response->token_type; //Bearer
    $access_token=$response->access_token; //04QJZEJKF5JHlM7hGJartNxFtV3E

    if (($token_type!="Bearer") || !$access_token) {
        // setError(55,array('sendSMS('.$mobnr.') mislukt. Onbekende response van KPN Authorisatie',$response));
        return false;
    }

    $smscode=mt_rand(100000, 999999);
    $data=array(    "sender"=>"ATS", 
            "messages"=>array( array(
                "mobile_number"=>$mobnr,
                "content"=>"Bevestigingscode: ".$smscode
            ))
    );
    //zenden
    $ch=curl_init();
    curl_setopt($ch, CURLOPT_URL,"https://api-prd.kpn.com/messaging/sms-kpn/v1/send");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER,array(
                        "Content-Type: application/json",
                        "accept: application/json",
                        "authorization: Bearer ".$access_token
                    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response=json_decode(curl_exec($ch));
    curl_close ($ch);

    if ((strtoupper($response->status)!='OK') || !$response->document_id) {
       // setError(56,array('sendSMS('.$mobnr.') mislukt. Onbekende response van KPN sms-send',$data,$response));
        return false;
    }
    return $smscode;
}

?>