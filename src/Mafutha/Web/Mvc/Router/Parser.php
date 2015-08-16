<?php
declare(strict_types=1);
namespace Mafutha\Web\Mvc\Router;

/**
 * The Router Parser is responsible for read a Route file (txt) and build a PHP array with definitions.
 * It can be used to write a PHP file (to be a cached version of routes file).
 *
 * @author Rubens Takiguti Ribeiro <rubs33@gmail.com>
 */
class Parser
{
    /**
     * Tokens from txt file
     *
     * @var array
     */
    protected $tokens;

    /**
     * Params from text file
     *
     * @var array
     */
    protected $params;

    /**
     * Routes definitions (processed tokens)
     *
     * @var array
     */
    protected $routes;

    /**
     * Route couter (used to give name to routes without name)
     *
     * @var int
     */
    protected $routeCounter;

    /**
     * Parse the txt file
     *
     * @param string $inputFile
     * @return void
     */
    public function parseFile(string $inputFile)
    {
        $this->parseFileTokens($inputFile);
        $this->buildRoutes();
        $this->buildRoutesMatcher($this->routes);
        $this->buildRoutesBuilder($this->routes);
        $this->clearRoutesAux($this->routes);
    }

    /**
     * Writhe the processed routes to a PHP file
     *
     * @param string $outputFile
     * @param bool $compress Write the file without unnecessary spaces
     * @return void
     */
    public function writeRoutes(string $outputFile, bool $compress = false)
    {
        if (!$this->routes) {
            throw new \LogicException('No route has been processed');
        }

        $fileName = __CLASS__;
        $routes = $this->varExport($this->routes, $compress);

        $fileContent = <<<EOF
<?php
/**
 * This file was generated automatically by "{$fileName}".
 * Do not modify it directly.
 */
return {$routes};
EOF;

        $done = file_put_contents($outputFile, $fileContent);
        if (!$done) {
            throw new \RuntimeException(sprintf('Error while writing to the file %s', $outputFile));
        }
    }

    /**
     * Get processed routes
     *
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Parse input file and create tokens
     *
     * @param string $inputFile
     * @return void
     */
    protected function parseFileTokens(string $inputFile)
    {
        $this->tokens = [];
        $this->params = [];

        $state = null;

        $input = new \SplFileObject($inputFile);
        $lineNumber = 0;
        while (!$input->eof()) {
            $lineNumber += 1;
            $line = rtrim($input->fgets());
            if (trim($line) === '' || substr(ltrim($line), 0, 1) == '#') {
                continue;
            }
            if ($line == '[ROUTES]') {
                $state = 'ROUTES';
            } elseif ($line == '[PARAMS]') {
                $state = 'PARAMS';
            } elseif ($state == 'ROUTES') {
                $this->tokens[] = $this->parseRoute($line, $lineNumber);
            } elseif ($state == 'PARAMS') {
                $param = $this->parseParam($line, $lineNumber);
                $this->params[$param['name']] = $param['expression'];
            } else {
                throw new \Exception(sprintf("Invalid content at line %d\nContent: %s", $lineNumber, $line));
            }
        }
    }

    /**
     * Create a token of a route from a raw string of text file
     *
     * @param string $rawRoute
     * @param int $lineNumber
     * @return array
     */
    protected function parseRoute(string $rawRoute, int $lineNumber): array
    {
        if (!preg_match('/^(?<indent>\s*)(?<route>[^\s]+)(?:\s+#\s*(?<specs>.*?))?\s*$/', $rawRoute, $matches)) {
            throw new \Exception(sprintf("Invalid route at line %d\nContent: %s", $lineNumber, $rawRoute));
        }
        if (isset($matches['specs'])) {
            $routeSpecs = json_decode($matches['specs'], true);
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new \Exception(sprintf("Invalid JSON at line %d\nError: %s\nContent: %s", $lineNumber, json_last_error_msg(), $rawRoute));
            }
        } else {
            $routeSpecs = [];
        }
        $routeSpecs['route'] = $matches['route'];
        $routeSpecs['indent'] = intval(strlen($matches['indent']) / 4);

        return $routeSpecs;
    }

    /**
     * Parse a token of a param from a raw string of text file
     *
     * @param string $rawParam
     * @param int $lineNumber
     * @return array
     */
    protected function parseParam(string $rawParam, int $lineNumber): array
    {
        if (!preg_match('/^(?<name>[^=\s]+)\s*=\s*(?<expression>.*?)\s*$/', $rawParam, $matches)) {
            throw new \Exception(sprintf("Invalid param at line %d\nContent: %s", $lineNumber, $rawParam));
        }
        return [
            'name' => $matches['name'],
            'expression' => $matches['expression']
        ];
    }

    /**
     * Build the routes from the processed tokens
     *
     * @return void
     */
    protected function buildRoutes()
    {
        $this->routes = [];
        $this->routeCouter = 0;
        foreach ($this->tokens as $token) {
            $this->buildRoute($token);
            $this->routeCouter += 1;
        }
    }

    /**
     * Build a route from a token
     *
     * @param array $token
     * @return void
     */
    protected function buildRoute(array $token)
    {
        $route = [
            'defaults' => $token,
            'params'   => [],
            'regexp'   => null,
            'build'    => null
        ];

        $level = $token['indent'];
        $name = $token['name'] ?? '_route' . $this->routeCouter;

        if (preg_match_all('/<(?<param>[^>]+)>/', $token['route'], $matches)) {
            foreach ($matches['param'] as $param) {
                if (!isset($this->params[$param])) {
                    throw new \Exception(sprintf('Missing param definition: %s', $param));
                }
                if (!isset($route['defaults'][$param])) {
                    $route['defaults'][$param] = null;
                }
                $route['params'][$param] = $this->params[$param];
            }
        }

        $lastRef = null;
        $ref = &$this->routes;
        while ($level > 0) {
            if (empty($ref)) {
                throw new \Exception(sprintf("Route %s should not be indented", $route['route']));
            }
            end($ref);
            $lastRouteIndex = key($ref);
            if (!isset($ref[$lastRouteIndex]['child_routes'])) {
                $ref[$lastRouteIndex]['child_routes'] = [];
            }
            $lastRef = &$ref[$lastRouteIndex];
            $ref = &$ref[$lastRouteIndex]['child_routes'];
            $level -= 1;
        }

        $route['parentRoute'] = &$lastRef;

        $ref[$name] = $route;
    }

    /**
     * Build the "matcher" part of the routes
     *
     * @param array $routes
     * @return void
     */
    protected function buildRoutesMatcher(array &$routes)
    {
        foreach ($routes as &$route) {
            $route['regexp'] = $this->buildRouteMatcher($route);
            if (isset($route['child_routes'])) {
                $this->buildRoutesMatcher($route['child_routes']);
            }
        }
    }

    /**
     * Build the "matcher" part of a route
     *
     * @param array $route
     * @return string
     */
    protected function buildRouteMatcher(array $route): string
    {
        $regexp = $route['defaults']['route'];

        $tr = [
            '(' => '(?:',
            ')' => ')?'
        ];
        $regexp = strtr($regexp, $tr);
        $regexp = sprintf('#^%s%s$#u', $regexp, isset($route['child_routes']) ? '(?P<subpath>.*)' : '');

        if (!empty($route['params'])) {
            $tr = [];
            foreach ($route['params'] as $param => $exp) {
                $tr[sprintf('<%s>', $param)] = sprintf('(?P<%s>%s)', $param, $exp);
            }
            $regexp = strtr($regexp, $tr);
        }
        return $regexp;
    }

    /**
     * Build the "builder" part of the routes
     *
     * @param array $routes
     * @return void
     */
    protected function buildRoutesBuilder(array &$routes)
    {
        foreach ($routes as &$route) {
            if (isset($route['defaults']['name'])) {
                $strBuilder = $this->buildRouteBuilderString($route);
                $route['build'] = $this->buildRouteBuilderArray($strBuilder, $route);
            }

            if (isset($route['child_routes'])) {
                $this->buildRoutesBuilder($route['child_routes']);
            }
        }
    }

    /**
     * Recursive method to create the "builder" part of a route
     *
     * @param string $strBuilder
     * @param array $route
     * @return array
     */
    protected function buildRouteBuilderArray(string $strBuilder, array $route): array
    {
        $builder = [];

        $builderParts = $this->getRouteParts($strBuilder);
        foreach ($builderParts as $buildPart) {
            $strPart = substr($strBuilder, $buildPart['start'], $buildPart['end'] - $buildPart['start']);

            switch ($buildPart['type']) {
                case 'literal':
                    $builder[] = [
                        'type' => 'literal',
                        'value' => $strPart,
                        'params' => $this->getParamsFromRoute($strPart, $route)
                    ];
                    break;
                case 'optional':
                    $builder[] = [
                        'type' => 'optional',
                        'value' => $this->buildRouteBuilderArray($strPart, $route),
                        'params' => $this->getParamsFromRoute($strPart, $route)
                    ];
                    break;
            }
        }

        return $builder;
    }

    /**
     * Find the offsets of optional portions of the routes
     * (used by buildRouteBuilderArray)
     *
     * @param string $strBuilder
     * @return array
     */
    protected function getRouteParts(string $strBuilder): array
    {
        $parts = [];
        $openBrackets = 0;
        $start = 0;
        $end = 0;

        $length = strlen($strBuilder);
        for ($i = 0; $i < $length; $i++) {
            $char = $strBuilder[$i];
            switch ($char) {
                case '(':
                    if ($openBrackets > 0) {
                        $end += 1;
                    } else {
                        if ($start != $end) {
                            $parts[] = [
                                'type'  => 'literal',
                                'start' => $start,
                                'end'   => $end
                            ];
                        }
                        $start = $i + 1;
                        $end = $i + 1;
                    }
                    $openBrackets += 1;
                    break;
                case ')':
                    if ($openBrackets > 0) {
                        $openBrackets -= 1;
                        if ($openBrackets == 0) {
                            $parts[] = [
                                'type'  => 'optional',
                                'start' => $start,
                                'end'   => $end
                            ];
                            $start = $i + 1;
                            $end = $i + 1;
                        } else {
                            $end += 1;
                        }
                    } else {
                        throw new \LogicException('Invalid route (unbalanced brackets): ' . $strBuilder);
                    }
                    break;
                default:
                    $end += 1;
                    break;
            }
        }

        if ($openBrackets > 0) {
            throw new \LogicException('Invalid route (unbalanced brackets): ' . $strBuilder);
        }
        if ($start != $end) {
            $parts[] = [
                'type'  => 'literal',
                'start' => $start,
                'end'   => $end
            ];
        }

        return $parts;
    }

    /**
     * Recursive method to create the full route path of a route
     *
     * @param array $route
     * @return string
     */
    protected function buildRouteBuilderString(array $route): string
    {
        return (isset($route['parentRoute']) ? $this->buildRouteBuilderString($route['parentRoute']) : '') . $route['defaults']['route'];
    }

    /**
     * Return the route path of the parent route of a route (or an empty string if it has not any parent route)
     *
     * @param array $route
     * @return string
     */
    protected function getParentRouteBuilder(array $route): string
    {
        if (!isset($route['parentRoute'])) {
            return '';
        }
        return $this->getParentRouteBuilder($route['parentRoute']) . $route['parentRoute']['build'];
    }

    /**
     * Get the variable params of a route
     *
     * @param string $strRoute
     * @param array $route
     * @return array
     */
    protected function getParamsFromRoute(string $strRoute, array $route): array
    {
        $params = [];
        if (!preg_match_all('#<([^>]+)>#', $strRoute, $matches)) {
            return $params;
        }
        foreach ($matches[1] as $param) {
            if (array_key_exists($param, $route['params'])) {
                $params[$param] = $route['params'][$param];
            } else {
                $params[$param] = null;
            }
        }
        return $params;
    }

    /**
     * Clear unnecessary data of processed routes
     *
     * @param array $routes
     * @return void
     */
    protected function clearRoutesAux(array &$routes)
    {
        foreach ($routes as &$route) {
            unset(
                $route['defaults']['indent'],
                $route['defaults']['name'],
                $route['defaults']['route'],
                $route['params'],
                $route['parentRoute']
            );
            if (isset($route['child_routes'])) {
                $child = $route['child_routes'];
                unset($route['child_routes']);
                $this->clearRoutesAux($child);
                $route['child_routes'] = $child;
            }
        }
    }

    /**
     * Own implementation of "var_export" function to allow indentation or
     * compression of the returned string
     *
     * @param mixed $var
     * @param bool $compress
     * @param int $indent
     * @return string
     */
    protected function varExport($var, bool $compress = false, int $indent = 0): string
    {
        $strIndent = $compress ? '' : str_repeat('    ', $indent);
        $newLine = $compress ? '' : "\n";

        $code = $strIndent;
        if (is_int($var) || is_float($var) || is_bool($var) || is_string($var) || is_null($var)) {
            $code .= var_export($var, true);
        } elseif (is_array($var)) {
            if (empty($var)) {
                $code .= '[]';
            } elseif (array_keys($var) === range(0, count($var) - 1)) {
                $code .= '[' . $newLine;
                foreach ($var as $value) {
                    $code .= $this->varExport($value, $compress, $indent + 1) . ',' . $newLine;
                }
                $code .= $strIndent . ']';
            } else {
                $code .= '[' . $newLine;
                foreach ($var as $key => $value) {
                    $code .= sprintf(
                        '%s => %s,%s',
                        $this->varExport($key, $compress, $indent + 1),
                        ltrim($this->varExport($value, $compress, $indent + 1)),
                        $newLine
                    );
                }
                $code .= $strIndent . ']';
            }
        }
        return $code;
    }

}