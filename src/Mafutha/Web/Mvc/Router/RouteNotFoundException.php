<?php
declare(strict_types=1);
namespace Mafutha\Web\Mvc\Router;

use \Psr\Http\Message\RequestInterface;

/**
 * This exceptions is used to inform the web application that no route was found
 *
 * @author Rubens Takiguti Ribeiro <rubs33@gmail.com>
 */
class RouteNotFoundException extends \Exception
{
    /**
     * The request that did not match any route
     *
     * @var \Psr\Http\Message\RequestInterface
     */
    public $request;

    /**
     * {@inheritdoc}
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @param string $message
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct(RequestInterface $request, string $message = 'Route not found', int $code = 0, \Exception $previous = null)
    {
        $this->request = $request;
        parent::__construct($message, $code, $previous);
    }
}