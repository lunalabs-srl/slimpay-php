# SlimPay PHP
A simple PHP package to integrate the **SlimPay checkout** on your application that supports both **iframe** and **redirect** checkout.

<br />

## Installation
You can directly install the package with:
```bash
composer require lunalabs-srl/slimpay-php
```
<br />

## Test the package
Create a simple PHP file with these lines:
```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use LunaLabs\SlimPayPhp\SlimPayPhp;
use LunaLabs\SlimPayPhp\SlimPayNotification;
use LunaLabs\SlimPayPhp\Exceptions\SlimPayPhpException;

// Slimpay credentials.
$slimpayConfig = [
    'creditor'   => 'xxxxxxxxxxx',
    'appId'      => 'xxxxxxxxxxxx',
    'appSecret'  => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
    'baseUri'    => 'https://api.preprod.slimpay.com',
    'profileUri' => 'https://api.slimpay.net',
    'apiVersion' => 'v1',
    'mode'       => 'iframe' // iframe|redirect
];

// Instance.
$slimpay = new SlimPayPhp($slimpayConfig);
```

<br />

# Credit card checkout with SlimPay iframe or redirection
To create the SlimPay checkout for Credit Cards you have to init an array like this one below and call the **checkout** method.
Customize the data and the return urls (failureUrl, successUrl and cancelUrl) with yours.
```php
$data = [
    'started'        => true,
    'locale'         => 'it',
    'paymentScheme'  => 'CARD',
    'creditor'       => ['reference' => 'xxxxxxxxxxx'],
    'items'          => [['type' => 'cardAlias']],
    'subscriber'     => [
        'reference'  => 'yourUniqueUserId',
        'givenName'  => 'John',
        'familyName' => 'Doe',
        'email'      => 'john.doe@domain.com',
        'telephone'  => '+393470000000',
    ],
    'failureUrl' => 'http://yourdomain.com/failure.php',
    'successUrl' => 'http://yourdomain.com/success.php',
    'cancelUrl'  => 'http://yourdomain.com/cancel.php'
];

try {
    // This call returns a response with the order representation
    // in which you can find the unique ID. Save it to keep the order
    // reference (for example, if the order fails).
    $response = $slimpay->checkout($data);
    $slimpay->showCheckoutPage($response);

} catch (SlimPayPhpException $e) {

    // In case of error, you can handle the formatted object response through some useful properties.
    header('Content-Type: application/json');
    echo json_encode($e->errorFormatter());
}
```
If the response has the **user approval link** you will be redirected to the **SlimPay checkout page**.
Once you have filled the checkout form with your Credit Card details, a detailed response will be sent to the **Server notification URI** set in your 
SlimPay application, containing the **get-card-alias link** to retrieve the **credit card ID** and **reference ID** to be sent to your Payment Gateway to finish the flow.
[Server Notification Reference](https://support.slimpay.com/hc/en-us/articles/360001565338-URLs-Management)
```php
$creditCardAlias = $slimpay->getResource('https://api.slimpay.net/card-aliases/00000000-0000-0000-0000-000000000000');
```
In this response you will find the **id**, **reference** and **status** for the used credit card and now you will able to 
store these data in the payment gateway.

<br />

# SEPA checkout with SlimPay iframe or redirection
To create the SlimPay iframe for SEPA direct debit you have to init an array like this one below and call the **checkout** method.
Customize the data and the return urls (failureUrl, successUrl and cancelUrl) with yours.
```php
$data = [
    'started'    => true,
    'creditor'   => ['reference' => 'xxxxxxxxxxx'],
    'subscriber' => ['reference' => 'yourUniqueUserId'],
    'items'      => [[
        'type' => 'signMandate',
        'mandate' => [
            'signatory' => [
                'givenName'      => 'John',
                'familyName'     => 'Doe',
                'email'          => 'john.doe@domain.com',
                'telephone'      => '+393470000000',
                'billingAddress' => [
                    'street1'    => 'Address street 123',
                    'postalCode' => '01234',
                    'city'       => 'CityName',
                    'country'    => 'IT'
                ],
            ]
        ]
    ]],
    'failureUrl' => 'http://yourdomain.com/failure.php',
    'successUrl' => 'http://yourdomain.com/success.php',
    'cancelUrl'  => 'http://yourdomain.com/cancel.php'
];

try {
    // This call returns a response with the order representation
    // in which you can find the unique ID. Save it to keep the order
    // reference (for example, if the order fails).
    $response = $slimpay->checkout($data);
    $slimpay->showCheckoutPage($response);

} catch (SlimPayPhpException $e) {

    // In case of error, you can handle the formatted object response through some useful properties.
    header('Content-Type: application/json');
    echo json_encode($e->errorFormatter());
}
```
If the response has the **user approval link** you will be redirected to the **SlimPay checkout page**.
Once you have filled the checkout form with your IBAN, a detailed response will be sent to the **Server notification URI** set in your 
SlimPay application, containing the **checkout status** and the link (**get-mandate**) to retrieve the created mandate. Calling this link 
(like this one: https://api.slimpay.net/mandates/xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx) you can retrieve the **reference ID** 
and the **UMR (RUM) number** to create the payment method in your payment gateway for this user. 

<br />

# Get a resource through authenticated call
To retrieve a resource, you can use the **getResource()** method by passing the URL as shown below.
```php
try {
    $response = $slimpay->getResource('https://api.slimpay.net/RESOURCE-NAME/00000000-0000-0000-0000-000000000000');

} catch (SlimPayPhpException $e) {

    // In case of error, you can handle the formatted object response through some useful properties.
    header('Content-Type: application/json');
    echo json_encode($e->errorFormatter());
}
```

<br />

## Server notification URL

It is possible to handle the **SlimPay server notification**.
To instantiate the notification handler, write these lines:
```php
$notification = new SlimPayNotification();
$response     = $notification->getResponse();
```

If you want to **log the notification response**, you can inject you custom logger as parameter.
Pay attention that your logger must have a "**write()**" method inside, as shown in this simple example below.
```php
class Log
{
    public function write($input)
    {
        $path = "./slimpay_notifications.log";
        error_log(json_encode($input), 3, $path);
    }
}

$customLog    = new Log;
$notification = new SlimPayNotification($customLog);
$response     = $notification->getResponse();
```

<br />

## TODO

### Authentication
- [x] Authentication flow

### Credit card checkout
- [x] Create order
- [x] Checkout redirect
- [x] Embedded Iframe
- [x] Get notification response to retrieve the card and reference IDs

### SEPA checkout
- [x] Create order
- [x] Iframe redirect
- [x] Get notification response to retrieve the RUM

### Testing
- [x] Unit testing

