<?php namespace Milky\Exceptions\Displayers;

/**
 * This is the json api displayer class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class JsonApiDisplayer extends JsonDisplayer implements DisplayerInterface
{
    /**
     * Get the supported content type.
     *
     * @return string
     */
    public function contentType()
    {
        return 'application/vnd.api+json';
    }
}
