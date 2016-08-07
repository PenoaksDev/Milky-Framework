<?php namespace Milky\Exceptions\Transformers;

use Exception;

/**
 * This is the transformer interface.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
interface TransformerInterface
{
    /**
     * Transform the provided exception.
     *
     * @param \Exception $exception
     *
     * @return \Exception
     */
    public function transform(Exception $exception);
}
