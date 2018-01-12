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

    if (in_array($zip_code, array(
        '9410',
        '9411',
        '9420',
        '9421'
    ))) {
        echo '{
    "custom_message": {
        "display_mode": null,
        "reason": ""
    },
    "options": [
        {
            "tags": [],
            "id": 386467783,
            "estimated_delivery_time": {
                "unit": "hour",
                "shipping": 24,
                "schedule": null,
                "pay_before": null,
                "time_frame": {
                    "to": null,
                    "from": null
                },
                "offset": {
                    "shipping": 24,
                    "date": "2018-01-17T00:00:00.000-03:00"
                },
                "date": "2018-01-16T00:00:00.000-03:00",
                "type": "known_frame",
                "handling": 24
            },
            "list_cost": "300 aprox.",
            "currency_id": "ARS",
            "shipping_option_type": "address",
            "shipping_method_type": "standard",
            "name": "Normal a domicilio",
            "display": "recommended",
            "cost": "300 aprox.",
            "discount": {
                "promoted_amount": 0,
                "rate": 0,
                "type": "none"
            },
            "shipping_method_id": 73328
        }
    ],
    "destination": {
        "zip_code": "'.$zip_code.'",
        "extended_attributes": null,
        "state": {
            "id": "AR-T",
            "name": "Tierra del Fuego"
        },
        "country": {
            "id": "AR",
            "name": "Argentina"
        },
        "city": {
            "id": null,
            "name": null
        }
    }
}';
    } else {
        $paramsMP = array(
            "dimensions" => "30x30x30,500",
            "zip_code" => $zip_code,
            "item_price"=> "100.58",
            'free_method' => '', // optional
        );

        $response = $mp->calculateEnvios($paramsMP);

        echo json_encode($response['response'], JSON_PRETTY_PRINT);
    }
} else {
    echo '{}';
}
