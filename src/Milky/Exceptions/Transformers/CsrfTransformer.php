<?php namespace Milky\Exceptions\Transformers;

use Exception;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * This is the csrf transformer class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class CsrfTransformer implements TransformerInterface
{
    /**
     * Transform the provided exception.
     *
     * @param \Exception $exception
     *
     * @return \Exception
     */
    public function transform(Exception $exception)
    {
        if ($exception instanceof TokenMismatchException) {
            $exception = new BadRequestHttpException($exception->getMessage() ?: 'CSRF token validation failed.', $exception, $exception->getCode());
        }

        return $exception;
    }
}
