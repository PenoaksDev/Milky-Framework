<?php namespace Milky\Exceptions\Transformers;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * This is the auth transformer class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class AuthTransformer implements TransformerInterface
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
        if ($exception instanceof AuthorizationException) {
            $exception = new AccessDeniedHttpException($exception->getMessage(), $exception, $exception->getCode());
        }

        return $exception;
    }
}
