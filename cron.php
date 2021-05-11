<?php

// /** From \Library\Shared */
// function request(String $url, Array $params = []):string {
//   $response = '';
//   $data = http_build_query( $params );

//   // Setup stream context
//   $context = stream_context_create( [

//     'http' => [

//       'method' => 'POST',
//       'header' => "Content-Type: application/x-www-form-urlencoded\r\ncontent-Length: "
//         . strlen( $data ) . "\r\n",
//       'content' => $data,
//       // 'ignore_errors' => true,
//     ]

//   ] );
//   // Debug info
//   $response = file_get_contents( "https://$url", false, $context );
//   return $response;
// }

// $db = new \Library\MySQL('core',
//       \Library\MySQL::connect(
//         $this->getVar('DB_HOST', 'e'),
//         $this->getVar('DB_USER', 'e'),
//         $this->getVar('DB_PASS', 'e')
//       ) );

// $tenant = $_SERVER['TENANT'];
// $client = $_SERVER['CLIENT'];
// $scope = 'https://graph.microsoft.com/.default';
// $secret = $_SERVER['SECRET'];

// $url = "login.microsoftonline.com/". $tenant ."/oauth2/v2.0/token";

// $data = array(
//   'client_id' => $client,
//   'client_secret' => $secret,
//   'scope' => $scope,
//   'grant_type' => 'client_credentials'
// );

// $response = request($url, $data);
// $result = json_decode($response, true);

// $credentials = $db->select(['Credentials' => []])
//               ->where(['Credentials' => ['title' => 'sync']])->one();
// if ($credentials) {
//   $db->update('Credentials', [
//     'token' => $result['access_token'], 
//   ])->where(['Credentials' => ['title' => 'sync']])->run();
// } 
// else {
//   $db->insert(['Credentials' => [
//     'title' => 'sync',
//     'token' => $result['access_token'],
//   ]])->run();
// }

file_get_contents('https://academic.pnit.od.ua/teams/refresh');