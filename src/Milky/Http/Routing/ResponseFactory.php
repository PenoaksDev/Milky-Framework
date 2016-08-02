<?php namespace Milky\Http\Routing;

use JsonSerializable;
use Milky\Helpers\Str;
use Milky\Http\JsonResponse;
use Milky\Http\RedirectResponse;
use Milky\Http\Response;
use Milky\Http\View\Factory;
use Milky\Http\HttpFactory;
use Milky\Impl\Arrayable;
use Milky\Services\ServiceFactory;
use Milky\Traits\Macroable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResponseFactory extends ServiceFactory
{
	use Macroable;

	/**
	 * The view factory instance.
	 *
	 * @var Factory
	 */
	protected $view;

	/**
	 * The redirector instance.
	 *
	 * @var Redirector
	 */
	protected $redirector;

	public static function build()
	{
		return new static( Factory::i(), HttpFactory::i()->redirector() );
	}

	/**
	 * Create a new response factory instance.
	 *
	 * @param  Factory $view
	 * @param  Redirector $redirector
	 * @return void
	 */
	public function __construct( Factory $view, Redirector $redirector )
	{
		parent::__construct();

		$this->view = $view;
		$this->redirector = $redirector;
	}

	/**
	 * Return a new response from the application.
	 *
	 * @param  string $content
	 * @param  int $status
	 * @param  array $headers
	 * @return Response
	 */
	public function make( $content = '', $status = 200, array $headers = [] )
	{
		return new Response( $content, $status, $headers );
	}

	/**
	 * Return a new view response from the application.
	 *
	 * @param  string $view
	 * @param  array $data
	 * @param  int $status
	 * @param  array $headers
	 * @return Response
	 */
	public function view( $view, $data = [], $status = 200, array $headers = [] )
	{
		return static::make( $this->view->make( $view, $data )->render(), $status, $headers );
	}

	/**
	 * Return a new JSON response from the application.
	 *
	 * @param  string|array $data
	 * @param  int $status
	 * @param  array $headers
	 * @param  int $options
	 * @return JsonResponse
	 */
	public function json( $data = [], $status = 200, array $headers = [], $options = 0 )
	{
		if ( $data instanceof Arrayable && !$data instanceof JsonSerializable )
			$data = $data->toArray();

		return new JsonResponse( $data, $status, $headers, $options );
	}

	/**
	 * Return a new JSONP response from the application.
	 *
	 * @param  string $callback
	 * @param  string|array $data
	 * @param  int $status
	 * @param  array $headers
	 * @param  int $options
	 * @return JsonResponse
	 */
	public function jsonp( $callback, $data = [], $status = 200, array $headers = [], $options = 0 )
	{
		return $this->json( $data, $status, $headers, $options )->setCallback( $callback );
	}

	/**
	 * Return a new streamed response from the application.
	 *
	 * @param  \Closure $callback
	 * @param  int $status
	 * @param  array $headers
	 * @return StreamedResponse
	 */
	public function stream( $callback, $status = 200, array $headers = [] )
	{
		return new StreamedResponse( $callback, $status, $headers );
	}

	/**
	 * Create a new file download response.
	 *
	 * @param  \SplFileInfo|string $file
	 * @param  string $name
	 * @param  array $headers
	 * @param  string|null $disposition
	 * @return BinaryFileResponse
	 */
	public function download( $file, $name = null, array $headers = [], $disposition = 'attachment' )
	{
		$response = new BinaryFileResponse( $file, 200, $headers, true, $disposition );

		if ( !is_null( $name ) )
			return $response->setContentDisposition( $disposition, $name, str_replace( '%', '', Str::ascii( $name ) ) );

		return $response;
	}

	/**
	 * Return the raw contents of a binary file.
	 *
	 * @param  \SplFileInfo|string $file
	 * @param  array $headers
	 * @return  BinaryFileResponse
	 */
	public function file( $file, array $headers = [] )
	{
		return new BinaryFileResponse( $file, 200, $headers );
	}

	/**
	 * Create a new redirect response to the given path.
	 *
	 * @param  string $path
	 * @param  int $status
	 * @param  array $headers
	 * @param  bool|null $secure
	 * @return RedirectResponse
	 */
	public function redirectTo( $path, $status = 302, $headers = [], $secure = null )
	{
		return $this->redirector->to( $path, $status, $headers, $secure );
	}

	/**
	 * Create a new redirect response to a named route.
	 *
	 * @param  string $route
	 * @param  array $parameters
	 * @param  int $status
	 * @param  array $headers
	 * @return RedirectResponse
	 */
	public function redirectToRoute( $route, $parameters = [], $status = 302, $headers = [] )
	{
		return $this->redirector->route( $route, $parameters, $status, $headers );
	}

	/**
	 * Create a new redirect response to a controller action.
	 *
	 * @param  string $action
	 * @param  array $parameters
	 * @param  int $status
	 * @param  array $headers
	 * @return RedirectResponse
	 */
	public function redirectToAction( $action, $parameters = [], $status = 302, $headers = [] )
	{
		return $this->redirector->action( $action, $parameters, $status, $headers );
	}

	/**
	 * Create a new redirect response, while putting the current URL in the session.
	 *
	 * @param  string $path
	 * @param  int $status
	 * @param  array $headers
	 * @param  bool|null $secure
	 * @return RedirectResponse
	 */
	public function redirectGuest( $path, $status = 302, $headers = [], $secure = null )
	{
		return $this->redirector->guest( $path, $status, $headers, $secure );
	}

	/**
	 * Create a new redirect response to the previously intended location.
	 *
	 * @param  string $default
	 * @param  int $status
	 * @param  array $headers
	 * @param  bool|null $secure
	 * @return RedirectResponse
	 */
	public function redirectToIntended( $default = '/', $status = 302, $headers = [], $secure = null )
	{
		return $this->redirector->intended( $default, $status, $headers, $secure );
	}
}
