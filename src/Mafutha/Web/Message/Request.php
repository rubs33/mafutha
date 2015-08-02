<?php
namespace Mafutha\Web\Message;

/**
 * This class implements \Psr\Http\Message\RequestInterface and can keep
 * the base path of application URL to hint routers.
 *
 * Example:
 * Development enviromnent uses base URL: http://localhost/mafutha/
 * Production environment uses base URL: http://www.mafutha.com/
 * So, when a route was specified by "/test", it must match:
 * path "/mafutha/test" in development environment and
 * path "/test" in production environment
 *
 * @author Rubens Takiguti Ribeiro <rubs33@gmail.com>
 */
class Request extends \GuzzleHttp\Psr7\Request implements \Psr\Http\Message\RequestInterface
{
    /**
     * Base path of application URL
     *
     * @var string
     */
    protected $basePath;

    /**
     * Set a base path
     *
     * @var string
     * @return $this
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
        return $this;
    }

    /**
     * Get the base path
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Get the relative path of Request URI, dropping the base path if present
     *
     * @return string
     */
    public function getRelativePath()
    {
        if ($this->basePath) {
            $basePathLength = strlen($this->getBasePath());
            if (substr_compare($this->getUri()->getPath(), $this->getBasePath(), 0, $basePathLength) === 0) {
                return substr($this->getUri()->getPath(), $basePathLength);
            }
        }
        return $this->getUri()->getPath();
    }
}