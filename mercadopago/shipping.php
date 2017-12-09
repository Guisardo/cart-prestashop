<?php
$expiration = 6000;

$headers = apache_request_headers();
header("Expires: " . gmdate('D, d M Y H:i:s \G\M\T', time() + $expiration));
header("Last-Modified: ".gmdate("D, d M Y H:i:s", time())." GMT"); 

if(isset($headers['If-Modified-Since'])) {
  if(@strtotime($headers['If-Modified-Since']) > (time() - $expiration))
  {
    header('Not Modified',true,304);
    exit;
  }
}
header('Cache-Control: private, max-age='.$expiration);


if (!defined('_PS_ROOT_DIR_')) {
    define('_PS_ROOT_DIR_', dirname(__FILE__).'/../../');
}

require_once(_PS_ROOT_DIR_.'/config/config.inc.php');
require_once(_PS_ROOT_DIR_.'/init.php');

include dirname(__FILE__).'/includes/MPApi.php';

$mp = new MPApi(
            Configuration::get('MERCADOPAGO_CLIENT_ID'),
            Configuration::get('MERCADOPAGO_CLIENT_SECRET')
        );

$zip_code = $_GET['z'];
if (Context::getContext()->customer->logged && !$zip_code) {
    $cart = Context::getContext()->cart;
    if ($cart->id_address_invoice) {
        $address_invoice = new Address((integer) $cart->id_address_invoice);
        $zip_code = $address_invoice->postcode;
    }
}

header('Content-Type: application/json');
if ($zip_code) {

    $paramsMP = array(
        "dimensions" => "30x30x30,500",
        "zip_code" => $zip_code,
        "item_price"=> "100.58",
        'free_method' => '', // optional
    );

    $response = $mp->calculateEnvios($paramsMP);

    echo json_encode($response['response'], JSON_PRETTY_PRINT);
} else {
    echo '{}';
}
