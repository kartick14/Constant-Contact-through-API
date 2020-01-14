<?php
require('con.php');

$redirectURI = 'http://104.248.8.25/ccnewsletter/response.php';
$clientId = '441f4aef-c736-41c9-a8f2-59bb1c138366';
$clientSecret = 'JXldK2nHIKCmlK9KZpVIgw';

$newsletter_subscriber_list_id = '50b1203c-0bc8-11ea-9f65-d4ae5292c4dd';
$newsletter_alert_list_id = '096eebc8-159b-11ea-b180-d4ae5284344f';

$sql = "SELECT * FROM ect_cc_table";
$result = mysqli_query($con, $sql);

if($_GET['code'] != ''){
	$code = $_GET['code']; 
	$result = getAccessToken($redirectURI, $clientId, $clientSecret, $code);
    //print_r($result);
	$json_decode_result = json_decode($result, true);	
    //print_r($json_decode_result); echo '<br>';

    if(empty($json_decode_result['error'])){
    	$sql = "UPDATE ect_cc_table SET access_token = '".$json_decode_result['access_token']."', refresh_token = '".$json_decode_result['refresh_token']."', token_type = '".$json_decode_result['token_type']."', datetime = NOW() WHERE id = 1";
    	$result = mysqli_query($con, $sql);
        echo '<br>';
        echo search_user_by_email($resultt,'kartick1@example.com',$newsletter_subscriber_list_id);  
    }
	
}elseif (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result); 		  
	$resultt = refreshToken($row['refresh_token'], $clientId, $clientSecret); 
	$resultt = json_decode($resultt, true);
    if(empty($resultt['error'])){
        $sql = "UPDATE ect_cc_table SET access_token = '".$resultt['access_token']."', refresh_token = '".$resultt['refresh_token']."', token_type = '".$resultt['token_type']."', datetime = NOW() WHERE id = 1";
        $result = mysqli_query($con, $sql);
        echo search_user_by_email($resultt,'kartick1@example.com',$newsletter_subscriber_list_id);        
    }
}


function search_user_by_email($json_decode_result,$email_id,$list_id){
    if(empty($email_id)){
        $email_id = 'test@example.com';
    }

    $access_token = $json_decode_result['access_token']; 
    $refresh_token = $json_decode_result['refresh_token']; 
    $token_type = $json_decode_result['token_type'];  

    $curl_json_result = get_curl_body("https://api.cc.email/v3/contacts?email=".$email_id."&lists=".$list_id."&include_count=false",$token_type,$access_token);   
    $curl_assoc_result = json_decode($curl_json_result, true); 
    if(!empty($curl_assoc_result['contacts'])){
        return $curl_assoc_result['contacts'][0]['contact_id'];
    }else{
        return 0;
    }
}


function get_all_subscribers($json_decode_result,$list_id){	
	$access_token = $json_decode_result['access_token']; 
	$refresh_token = $json_decode_result['refresh_token']; 
	$token_type = $json_decode_result['token_type'];

	$curl_json_result = get_curl_body("https://api.cc.email/v3/contacts?lists=".$list_id."&include_count=false",$token_type,$access_token);
    $curl_assoc_result = json_decode($curl_json_result, true); 
}

function create_new_contact($json_decode_result,$first_name,$last_name,$email_id,$tags_list,$list_id){   

    $access_token = $json_decode_result['access_token']; 
    $refresh_token = $json_decode_result['refresh_token']; 
    $token_type = $json_decode_result['token_type'];          

    $curl_json_result = post_put_curl_body("POST",$first_name,$last_name,$email_id,$tags_list,$token_type,$access_token,$list_id);
    
}

function get_curl_body($url,$token_type,$access_token){
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "Accept: */*",
        "Accept-Encoding: gzip, deflate",
        "Authorization: ".$token_type." ".$access_token,
        "Cache-Control: no-cache",
        "Connection: keep-alive",
        "Host: api.cc.email",
        "Postman-Token: 1726eb09-cc8b-4d6d-843c-f8b0c6dfb10e,59e5b0b5-0564-44a0-85ef-7eeb33a4f22d",
        "User-Agent: PostmanRuntime/7.19.0",
        "cache-control: no-cache"
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
      return $err;
    } else {
      return $response;
    }
}

function post_put_curl_body($method = "POST",$first_name,$last_name,$email_id,$tags_list,$token_type,$access_token,$list_id){

    if($method == 'PUT'){
        $curl_post_body = "{\n  \"email_address\": {\n    \"address\": \"$email_id\",\n    \"permission_to_send\": \"implicit\"\n  },\n  \"first_name\": \"$first_name\",\n  \"last_name\": \"$last_name\",\n  \"update_source\": \"Contact\",\n  \"custom_fields\": [\n    {\n      \"custom_field_id\": \"6985e5dc-15a7-11ea-b180-d4ae5284344f\",\n      \"value\": \"$tags_list\"\n    }\n  ],\n  \"list_memberships\": [\n    \"$list_id\"\n  ]\n}";
    }else{
        $curl_post_body = "{\n  \"email_address\": {\n    \"address\": \"$email_id\",\n    \"permission_to_send\": \"implicit\"\n  },\n  \"first_name\": \"$first_name\",\n  \"last_name\": \"$last_name\",\n  \"create_source\": \"Contact\",\n  \"custom_fields\": [\n    {\n      \"custom_field_id\": \"6985e5dc-15a7-11ea-b180-d4ae5284344f\",\n      \"value\": \"$tags_list\"\n    }\n  ],\n  \"list_memberships\": [\n    \"$list_id\"\n  ]\n}";
    }

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.cc.email/v3/contacts",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_POSTFIELDS => $curl_post_body,
      CURLOPT_HTTPHEADER => array(
        "Accept: */*",
        "Accept-Encoding: gzip, deflate",
        //"Authorization: Bearer dTRf41L0ThkrUR4IlV3TGEO6GOJs",
        "Authorization: ".$token_type." ".$access_token,
        "Cache-Control: no-cache",
        "Connection: keep-alive",
        "Content-Length: 400",
        "Content-Type: application/json",
        "Host: api.cc.email",
        "Postman-Token: ea462408-b665-48c8-9b19-13776e7f0fd0,d5f708c6-7265-4640-bb5c-d3d61430d826",
        "User-Agent: PostmanRuntime/7.19.0",
        "cache-control: no-cache"
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
      return $err;
    } else {
      return $response;
    }
}

/*
 * This function can be used to exchange an authorization code for an access token.
 * Make this call by passing in the code present when the account owner is redirected back to you.
 * The response will contain an 'access_token' and 'refresh_token'
 */

/***
 * @param $redirectURI - URL Encoded Redirect URI
 * @param $clientId - API Key
 * @param $clientSecret - API Secret
 * @param $code - Authorization Code
 * @return string - JSON String of results
 */
function getAccessToken($redirectURI, $clientId, $clientSecret, $code) {
    // Use cURL to get access token and refresh token
    $ch = curl_init();

    // Define base URL
    $base = 'https://idfed.constantcontact.com/as/token.oauth2';

    // Create full request URL
    $url = $base . '?code=' . $code . '&redirect_uri=' . $redirectURI . '&grant_type=authorization_code&scope=contact_data';
    curl_setopt($ch, CURLOPT_URL, $url);

    // Set authorization header
    // Make string of "API_KEY:SECRET"
    $auth = $clientId . ':' . $clientSecret;
    // Base64 encode it
    $credentials = base64_encode($auth);
    // Create and set the Authorization header to use the encoded credentials
    $authorization = 'Authorization: Basic ' . $credentials;
    curl_setopt($ch, CURLOPT_HTTPHEADER, array($authorization));

    // Set method and to expect response
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Make the call
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
}


/*
 * This function can be used to exchange a refresh token for a new access token and refresh token.
 * Make this call by passing in the refresh token returned with the access token.
 * The response will contain a new 'access_token' and 'refresh_token'
 */

/***
 * @param $refreshToken - The refresh token provided with the previous access token
 * @param $clientId - API Key
 * @param $clientSecret - API Secret
 * @return string - JSON String of results
 */
function refreshToken($refreshToken, $clientId, $clientSecret) {
    // Use cURL to get a new access token and refresh token
    $ch = curl_init();

    // Define base URL
    $base = 'https://idfed.constantcontact.com/as/token.oauth2';

    // Create full request URL
    $url = $base . '?refresh_token=' . $refreshToken . '&grant_type=refresh_token'; echo $url;
    curl_setopt($ch, CURLOPT_URL, $url);

    // Set authorization header
    // Make string of "API_KEY:SECRET"
    $auth = $clientId . ':' . $clientSecret;
    // Base64 encode it
    $credentials = base64_encode($auth);
    // Create and set the Authorization header to use the encoded credentials
    $authorization = 'Authorization: Basic ' . $credentials;
    curl_setopt($ch, CURLOPT_HTTPHEADER, array($authorization));

    // Set method and to expect response
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Make the call
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
?>