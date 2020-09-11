<?php
/**
 * This file is part of the SlimPay PHP package.
 *
 * (c) Alessandro Orrù <alessandro.orru@aleostudio.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace LunaLabs\SlimPayPhp;

// Package classes.
use LunaLabs\SlimPayPhp\Http\Client;
use LunaLabs\SlimPayPhp\Exceptions\SlimPayPhpException;
use GuzzleHttp\Exception\GuzzleException;


class SlimPayPhp
{
    /**
     * @var Client $client
     */
    private $client;

    /**
     * @var array $config
     */
    private $config;


    /**
     * SlimPay PHP constructor.
     *
     * @param  array  $config SlimPay PHP configuration.
     * @param  Client $client The Guzzle HTTP client.
     * @throws SlimPayPhpException
     */
    public function __construct(array $config = null, Client $client = null)
    {
        if (is_null($client)) {
            if (is_null($config)) throw new SlimPayPhpException('The SlimPay PHP auth configuration is missing');
            $client = new Client($config);
        }

        $this->config = $config;
        $this->client = $client;
    }


    /**
     * Sends to SlimPay a checkout request with the payment method set. This resource will return
     * an HAL JSON with all the referenced resources.
     *
     * @param  array $data
     * @return mixed
     * @throws SlimPayPhpException|GuzzleException
     */
    public function checkout(array $data)
    {
        return $this->client->request('POST', '/orders', [ 'json' => $data ])->toObject();
    }


    /**
     * Retrieves a resource by the given endpoint (it must have the authentication bearer).
     *
     * @param  string $endpoint
     * @param  array $params
     * @return mixed
     * @throws SlimPayPhpException|GuzzleException
     */
    public function getResource(string $endpoint, array $params = [])
    {
        return $this->client->request('GET', $endpoint, $params)->toObject();
    }


    /**
     * Prints the checkout Iframe HTML code or redirect to the checkout page.
     *
     * @param  object $response
     * @param  bool   $raw
     * @return mixed
     * @throws SlimPayPhpException|GuzzleException
     */
    public function showCheckoutPage(object $response, bool $raw = false)
    {
        $resourceLinks = [
            'userApproval'     => $this->config['profileUri'].'/alps#user-approval',
            'extendedApproval' => $this->config['profileUri'].'/alps#extended-user-approval'
        ];

        if ($this->config['mode'] == 'iframe') {

            $link    = $response->_links->{$resourceLinks['extendedApproval']}->href;
            $link    = str_replace('{?mode}', '', $link);
            $encoded = $this->getResource($link, ['mode' => 'iframeembedded']);
            $html    = base64_decode($encoded->content);

            if ($raw) return $html;
            echo $html;

        } else {
            if ($raw) return $response->_links->{$resourceLinks['userApproval']}->href;
            header('Location: ' . $response->_links->{$resourceLinks['userApproval']}->href);
        }
    }


    /**
     * Checks if the given response is valid.
     *
     * @param  object $response
     * @return bool
     */
    public function isValidResponse(object $response): bool
    {
        return property_exists($response, '_links');
    }


    /**
     * Retrieves the given payment detail.
     *
     * @param  string $payment
     * @return object
     */
    public function getPayment(string $payment): object
    {
        return $this->getResource($this->config['baseUri'].'/payments/'.$payment, []);
    }
}
