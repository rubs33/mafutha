<?php
declare(strict_types=1);
namespace Mafutha\Behavior\Object;

/**
 * Hook trait is responsible for append and execute functions in certain ponts of a class
 *
 * @author Rubens Takiguti Ribeiro <rubs33@gmail.com>
 */
trait Hook
{
    /**
     * List of hooks
     *
     * @var array
     */
    protected $hooks = [];

    /**
     * Add a hook function to be executed at some point of the class.
     *
     * @param int $point Class defined point
     * @param callable $function Any callable (closure or callback)
     *     The function does not accept any parameter and does not return any value
     * @param bool $prepend Prepend function instead appending it
     * @return $this
     */
    public function addHook(int $point, callable $function, bool $prepend = false): self
    {
        if (!isset($this->hooks[$point])) {
            $this->hooks[$point] = [];
        }
        if ($prepend) {
            array_unshift($this->hooks[$point], $function);
        } else {
            array_push($this->hooks[$point], $function);
        }
        return $this;
    }

    /**
     * Execute registered functions for some point of the class.
     *
     * @param int $point Class defined point
     * @return void
     */
    protected function executeHook(int $point)
    {
        if (!isset($this->hooks[$point])) {
            return;
        }
        foreach ($this->hooks[$point] as $function) {
            call_user_func($function);
        }
    }
}