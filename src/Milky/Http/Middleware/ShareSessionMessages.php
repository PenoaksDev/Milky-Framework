<?php namespace Milky\Http\Middleware;

use Closure;
use Milky\Helpers\ViewErrorBag;
use Milky\Http\Request;
use Milky\Http\View\ViewFactory;

class ShareSessionMessages
{
	/**
	 * The view factory implementation.
	 *
	 * @var ViewFactory
	 */
	protected $view;

	/**
	 * Create a new error binder instance.
	 *
	 * @param  ViewFactory  $view
	 * @return void
	 */
	public function __construct(ViewFactory $view)
	{
		$this->view = $view;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		// If the current session has an "errors" variable bound to it, we will share
		// its value with all view instances so the views can easily access errors
		// without having to bind. An empty bag is set when there aren't errors.
		$this->view->share(
			'errors', $request->session()->get('errors') ?: new ViewErrorBag
		);

		$this->view->share(
			'messages', $request->session()->get('messages') ?: new ViewErrorBag
		);

		// Putting the errors in the view for every view allows the developer to just
		// assume that some errors are always available, which is convenient since
		// they don't have to continually run checks for the presence of errors.

		return $next($request);
	}
}
