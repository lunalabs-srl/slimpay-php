<?php
/**
 * This file is part of the SlimPay PHP package.
 *
 * (c) Alessandro Orrù <alessandro.orru@aleostudio.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace LunaLabs\SlimPayPhp\Exceptions;

use \Exception;


class SlimPayPhpException extends Exception
{
    /**
     * Constructor.
     *
     * @param string          $error
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct(string $error, int $code = 0, Exception $previous = null)
    {
        parent::__construct($error, $code, $previous);
    }


    /**
     * If an exception is thrown, this method return a simple object
     * with the received error code and message.
     *
     * @link   https://dev.slimpay.com/hapi/overview/errors
     *
     * @return object
     */
    public function errorFormatter()
    {
        return (object) [
            'error'           => true,
            'http_code'       => $this->getCode(),
            'response'        => $this->getPrevious()->getMessage(),
            'slimpay_code'    => (json_decode($this->getMessage())->code)    ?? 0,
            'slimpay_message' => (json_decode($this->getMessage())->message) ?? ''
        ];
    }
}
