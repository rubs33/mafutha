<?php
namespace Example\Controller;

class ErrorController extends \Mafutha\Web\Mvc\Controller\AbstractController
{
    public function errorAction()
    {
        $exception = $this->getRoute()['params']['exception'];
        if ($this->getRoute()['params']['show_errors']) {
            $esc = function($str) { return htmlspecialchars($str); };

            $code = $this->showCode($exception->getFile(), $exception->getLine(), 10);
            $backtrace = '';
            foreach ($exception->getTrace() as $trace) {
                $backtrace .= $this->buildTrace($trace, $exception) . '<hr />';
            }

            $this->response->getBody()->write(sprintf('<h1>%s#%s</h1>', $esc(get_class($exception)), $esc($exception->getCode())));
            $this->response->getBody()->write(sprintf('<p>Message: %s</p>', $esc($exception->getMessage())));
            $this->response->getBody()->write(sprintf('<p>File: %s</p>', $esc($exception->getFile())));
            $this->response->getBody()->write(sprintf('<p>Line: %s</p>', $esc($exception->getLine())));
            $this->response->getBody()->write(sprintf('<p>Code:</p><pre style="max-width: 100%%; border: 1px inset #CCC; overflow: auto; padding: 10px;">%s</pre>', $code));
            $this->response->getBody()->write(sprintf('<p>Backtrace:</p><div>%s</div>', $backtrace));
            $this->response->getBody()->write('<script>function toggle(id) { var el = document.getElementById(id); el.style.display = el.style.display == "none" ? "block" : "none"; };</script>');
        } else {
            $this->response->getBody()->write('<h1>Error</h1>');
        }

    }

    public function notFoundAction()
    {
        $this->response->getBody()->write('<h1>Page not found</h1>');
    }

    private function showCode($fileName, $errorLine, $linesAround = 5)
    {
        $file = file($fileName, FILE_IGNORE_NEW_LINES);
        $firstLine = max($errorLine - $linesAround, 0);
        $lastLine = min($errorLine + $linesAround, count($file));
        $code = '';
        for ($i = $firstLine; $i < $lastLine; $i++) {
            $code .= sprintf(
                "<div style=\"background-color: %s; padding: 2px;\">%0" . strlen($lastLine) . "d | %s</div>",
                $i + 1 == $errorLine ? '#FF7777' : '#FFFFDD',
                htmlspecialchars($i + 1),
                htmlspecialchars($file[$i])
            );
        }
        return $code;
    }

    private function buildTrace($trace, $exception)
    {
        static $traceId = 0;
        $traceId += 1;
        $id = 'trace-' . $traceId;

        if (isset($trace['file'])) {
            $code = $this->showCode($trace['file'], $trace['line'], 10);
            $str = sprintf(
                '<p><a href="javascript:void()" onclick="toggle(\'%s\')">%s</a>: %s</p>',
                $id,
                htmlspecialchars($trace['file']) . '(' . $trace['line'] . ')',
                $this->buildCall($trace)
            );
            $str .= sprintf('<pre id="%s" style="display: none;">%s</pre>', $id, $code);
        } else {
            $str = sprintf('<p>[internal function]: %s</p>', $this->buildCall($trace));
        }
        return $str;
    }

    private function buildCall($trace)
    {
        if (isset($trace['class'])) {
            return sprintf('<b>%s</b>%s%s(%s)', $trace['class'], $trace['type'], $trace['function'], $this->buildArgs($trace['args']));
        } else {
            return sprintf('%s(%s)', $trace['function'], $this->buildArgs($trace['args']));
        }
    }

    private function buildArgs($args)
    {
        $values = [];
        foreach ($args as $arg) {
            if (is_object($arg)) {
                $values[] = sprintf('object(class=%s)', get_class($arg));
            } else {
                $value = var_export($arg, true);
                if (strlen($value) < 40) {
                    $values[] = htmlspecialchars($value);
                } elseif (is_string($arg)) {
                    $values[] = var_export(htmlspecialchars(substr($arg, 0, 40)) . '&hellip;', true);
                } elseif (is_array($arg)) {
                    $values[] = sprintf('array(size=%d)', count($arg));
                } elseif (is_resource($arg)) {
                    $values[] = sprintf('resouce(type=%s)', get_resource_type($arg));
                } else {
                    $values[] = 'unknown';
                }
            }
        }
        return implode(', ', $values);
    }
}