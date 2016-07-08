<?php

namespace Penoaks\Providers;

use Penoaks\Routing\Redirector;
use Penoaks\Support\ServiceProvider;
use Penoaks\Http\FormRequest;
use Symfony\Component\HttpFoundation\Request;
use Penoaks\Contracts\Validation\ValidatesWhenResolved;

class FoundationServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->configureFormRequests();
	}

	/**
	 * Configure the form request related services.
	 *
	 * @return void
	 */
	protected function configureFormRequests()
	{
		$this->fw->afterResolving(function (ValidatesWhenResolved $resolved)
{
			$resolved->validate();
		});

		$this->fw->resolving(function (FormRequest $request, $fw)
{
			$this->initializeRequest($request, $fw->bindings['request']);

			$request->setBindings($fw)->setRedirector($fw->make(Redirector::class));
		});
	}

	/**
	 * Initialize the form request with data from the given request.
	 *
	 * @param  \Penoaks\Http\FormRequest  $form
	 * @param  \Symfony\Component\HttpFoundation\Request  $current
	 * @return void
	 */
	protected function initializeRequest(FormRequest $form, Request $current)
	{
		$files = $current->files->all();

		$files = is_array($files) ? array_filter($files) : $files;

		$form->initialize(
			$current->query->all(), $current->request->all(), $current->attributes->all(),
			$current->cookies->all(), $files, $current->server->all(), $current->getContent()
		);

		if ($session = $current->getSession())
{
			$form->setSession($session);
		}

		$form->setUserResolver($current->getUserResolver());

		$form->setRouteResolver($current->getRouteResolver());
	}
}
