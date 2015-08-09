<?php
/**
 * Created by PhpStorm.
 * User: roderik
 * Date: 09/08/15
 * Time: 15:30
 */

namespace Kunstmaan\Skylab\Exceptions;


class SkylabException extends \ErrorException
{

    private $context;

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the exception
     * @link http://php.net/manual/en/errorexception.construct.php
     * @param string $message [optional] The Exception message to throw.
     * @param int $code [optional] The Exception code.
     * @param int $severity [optional] The severity level of the exception.
     * @param string $filename [optional] The filename where the exception is thrown.
     * @param int $lineno [optional] The line number where the exception is thrown.
     * @param \Exception $previous [optional] The previous exception used for the exception chaining.
     * @param array $context
     */
    public function __construct($message = "", $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__, $previous = null, $context = array()) {
        parent::__construct($message, $code, $severity, $filename, $lineno, $previous);
        $this->context = $context;
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param mixed $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }



}