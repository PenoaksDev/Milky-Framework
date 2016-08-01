<?php namespace Milky\Exceptions\Displayers;

use Exception;
use Milky\Exceptions\ExceptionInfo;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * This is the json displayer class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class JsonDisplayer implements DisplayerInterface
{
    /**
     * The exception info instance.
     *
     * @var \Milky\Exceptions\ExceptionInfo
     */
    protected $info;

    /**
     * Create a new json displayer instance.
     *
     * @param \Milky\Exceptions\ExceptionInfo $info
     *
     * @return void
     */
    public function __construct(ExceptionInfo $info)
    {
        $this->info = $info;
    }

    /**
     * Get the error response associated with the given exception.
     *
     * @param \Exception $exception
     * @param string     $id
     * @param int        $code
     * @param string[]   $headers
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function display(Exception $exception, $id, $code, array $headers)
    {
        $info = $this->info->generate($exception, $id, $code);

        $error = ['id' => $id, 'status' => $info['code'], 'title' => $info['name'], 'detail' => $info['detail']];

        return new JsonResponse(['errors' => [$error]], $code, array_merge($headers, ['Content-Type' => $this->contentType()]));
    }

    /**
     * Get the supported content type.
     *
     * @return string
     */
    public function contentType()
    {
        return 'application/json';
    }

    /**
     * Can we display the exception?
     *
     * @param \Exception $original
     * @param \Exception $transformed
     * @param int        $code
     *
     * @return bool
     */
    public function canDisplay(Exception $original, Exception $transformed, $code)
    {
        return true;
    }

    /**
     * Do we provide verbose information about the exception?
     *
     * @return bool
     */
    public function isVerbose()
    {
        return false;
    }
}
