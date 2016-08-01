<?php namespace Milky\Exceptions\Filters;

use Exception;
use Illuminate\Http\Request;

/**
 * This is the can display filter class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class CanDisplayFilter
{
    /**
     * Filter and return the displayers.
     *
     * @param \Milky\Exceptions\Displayers\DisplayerInterface[] $displayers
     * @param \Illuminate\Http\Request                                   $request
     * @param \Exception                                                 $original
     * @param \Exception                                                 $transformed
     * @param int                                                        $code
     *
     * @return \Milky\Exceptions\Displayers\DisplayerInterface[]
     */
    public function filter(array $displayers, Request $request, Exception $original, Exception $transformed, $code)
    {
        foreach ($displayers as $index => $displayer) {
            if (!$displayer->canDisplay($original, $transformed, $code)) {
                unset($displayers[$index]);
            }
        }

        return array_values($displayers);
    }
}
