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
use LunaLabs\SlimPayPhp\Exceptions\SlimPayPhpException;


class SlimPayNotification
{
    /**
     * Custom log system to write the response data.
     * It needs a method "write()".
     *
     * @var object $log
     */
    private $log;


    /**
     * SlimPay Notification constructor.
     * Sets the custom log system, if given.
     *
     * @param object $log   Custom log system.
     */
    public function __construct(object $log = null)
    {
        if (!is_null($log)) $this->log = $log;
    }


    /**
     * Returns the response as array.
     *
     * If set in the instance, a custom log system method "write()"
     * will be called to save the output.
     *
     * @return array
     * @throws SlimPayPhpException
     */
    public function getResponse(): array
    {
        // Read the input stream and decode the given JSON.
        $rawResponse = file_get_contents("php://input");
        $response    = json_decode($rawResponse, true);

        if (!is_array($response))
            throw new SlimPayPhpException('Failed to decode JSON object');

        // If an external logger is passed, the "write" method will be called.
        if (!is_null($this->log))
            $this->log->write($response);

        return $response;
    }
}
