<?php
namespace Mafutha\Web\Message;

class Request extends \GuzzleHttp\Psr7\Request implements \Psr\Http\Message\RequestInterface
{
    protected $basePath;

    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

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