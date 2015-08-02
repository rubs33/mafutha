<?php
namespace Mafutha\Web\Mvc\Router;

/**
 * Literal Route
 *
 * @author Rubens Takiguti Ribeiro <rubs33@gmail.com>
 */
class LiteralRoute extends AbstractRoute
{

    /**
     * {@inheritdoc}
     *
     * @param \Mafutha\Web\Message\Request $request
     * @return bool
     */
    public function match(\Mafutha\Web\Message\Request $request)
    {
        return $request->getRelativePath() === $this->getRoute();
    }

    /**
     * {@inheritdoc}
     *
     * @param array $options
     * @return string
     */
    public function assemble(array $options)
    {
        return $this->getRoute();
    }

}