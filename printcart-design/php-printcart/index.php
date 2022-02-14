<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php';
$config = array(
    'Username' => 'printcart@gmail.com',
    'Password' => 'printcart'
);

$printcart = new PHPPrintcart\PrintcartSDK($config);

$postArray = array(
    "name" => "T-shirt update 2",
    "dynamic_side" => 1,
    "viewport_width" => 50.5,
    "viewport_height" => 50.5,
    "scale" => 50.5,
    "dpi" => 100,
    "dimension_unit" => "inch",
    "status" => "publish",
    "enable_design" => 1,
    "max_file_upload" => 50,
    "min_jpg_dpi" => 10,
    "allowed_file_types" => [
        "jpg",
        "pdf",
        "png"
    ]
);

$multipleArray = array(
    'products' => [
        [
            "id" => "0dcccd18-18e1-4fbf-b26b-234944746ee9",
            "name" => 'cong 1232'
        ],
        [
            "id" => "41fec099-789e-444e-81d5-a29078f175b6",
            "name" => "nguyen"
        ],
    ]
);


//GET
$response = $printcart->Product()->get();
// $productID = '1b665d2f-5a29-3e03-8698-01e4dc603fa9';
// $response = $printcart->Product($productID)->get();
// $response = $printcart->Product($productID)->Design->get();

//POST
// $response = $printcart->Product()->post($postArray);
  
//PUT
// $response = $printcart->Product('0dcccd18-18e1-4fbf-b26b-234944746ee9')->put($postArray);
// $response = $printcart->Product()->put_batch($multipleArray);

//DELETE
// $response = $printcart->Product('7115e6d1-44f3-4f35-bca3-9c02432819f0')->delete();
// $response = $printcart->Product()->delete_batch($multipleArray);

// $response = $printcart->Side('a17dbbda-fc2e-39e3-90c4-0597997fa866')->Template()->get();
print_r($response);die;