<?php namespace Milky\Facades;

use Milky\Http\View\Engines\EngineInterface;
use Milky\Http\View\Engines\EngineResolver;
use Milky\Http\View\ViewFactory;
use Milky\Impl\Htmlable;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class View extends BaseFacade
{
	/**
	 * @see ViewFactory
	 */
	protected function __getResolver()
	{
		return ViewFactory::class;
	}

	/**
	 * Get the evaluated view contents for the given file.
	 *
	 * @param  string $path
	 * @param  array $data
	 * @param  array $mergeData
	 *
	 * @return View
	 */
	public static function file( $path, $data = [], $mergeData = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Get the evaluated view contents for the given view.
	 *
	 * @param  string $view
	 * @param  array $data
	 * @param  array $mergeData
	 *
	 * @return View
	 */
	public static function make( $view, $data = [], $mergeData = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Get the evaluated view contents for the given view.
	 *
	 * @param  string $view
	 * @param  array $data
	 * @param  array $mergeData
	 *
	 * @return string
	 */
	public static function render( $view, $data = [], $mergeData = [] )
	{
		return static::make( $view, $data, $mergeData )->render();
	}

	/**
	 * Get the evaluated view contents for a named view.
	 *
	 * @param  string $view
	 * @param  mixed $data
	 *
	 * @return View
	 */
	public static function of( $view, $data = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Register a named view.
	 *
	 * @param  string $view
	 * @param  string $name
	 */
	public static function name( $view, $name )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Add an alias for a view.
	 *
	 * @param  string $view
	 * @param  string $alias
	 */
	public static function alias( $view, $alias )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Determine if a given view exists.
	 *
	 * @param  string $view
	 *
	 * @return bool
	 */
	public static function exists( $view )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Get the rendered contents of a partial from a loop.
	 *
	 * @param  string $view
	 * @param  array $data
	 * @param  string $iterator
	 * @param  string $empty
	 *
	 * @return string
	 */
	public static function renderEach( $view, $data, $iterator, $empty = 'raw|' )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Get the appropriate view engine for the given path.
	 *
	 * @param  string $path
	 *
	 * @return EngineInterface
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function getEngineFromPath( $path )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Add a piece of shared data to the environment.
	 *
	 * @param  array|string $key
	 * @param  mixed $value
	 *
	 * @return mixed
	 */
	public static function share( $key, $value = null )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Register a view creator event.
	 *
	 * @param  array|string $views
	 * @param  \Closure|string $callback
	 *
	 * @return array
	 */
	public static function creator( $views, $callback )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Register multiple view composers via an array.
	 *
	 * @param  array $composers
	 *
	 * @return array
	 */
	public static function composers( array $composers )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Register a view composer event.
	 *
	 * @param  array|string $views
	 * @param  \Closure|string $callback
	 *
	 * @return array
	 */
	public static function composer( $views, $callback )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Call the composer for a given view.
	 *
	 * @param  View $view
	 */
	public static function callComposer( View $view )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Call the creator for a given view.
	 *
	 * @param  View $view
	 */
	public static function callCreator( View $view )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Start injecting content into a section.
	 *
	 * @param  string $section
	 * @param  string $content
	 */
	public static function startSection( $section, $content = '' )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Inject inline content into a section.
	 *
	 * @param  string $section
	 * @param  string $content
	 */
	public static function inject( $section, $content )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Stop injecting content into a section and return its contents.
	 *
	 * @return string
	 */
	public static function yieldSection()
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Stop injecting content into a section.
	 *
	 * @param  bool $overwrite
	 *
	 * @return string
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function stopSection( $overwrite = false )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Stop injecting content into a section and append it.
	 *
	 * @return string
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function appendSection()
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Get the string contents of a section.
	 *
	 * @param  string $section
	 * @param  string $default
	 *
	 * @return string
	 */
	public static function yieldContent( $section, $default = '' )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Start injecting content into a push section.
	 *
	 * @param  string $section
	 * @param  string $content
	 */
	public static function startPush( $section, $content = '' )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Stop injecting content into a push section.
	 *
	 * @return string
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function stopPush()
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Get the string contents of a push section.
	 *
	 * @param  string $section
	 * @param  string $default
	 *
	 * @return string
	 */
	public static function yieldPushContent( $section, $default = '' )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Flush all of the section contents.
	 */
	public static function flushSections()
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Flush all of the section contents if done rendering.
	 */
	public static function flushSectionsIfDoneRendering()
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Increment the rendering counter.
	 */
	public static function incrementRender()
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Decrement the rendering counter.
	 */
	public static function decrementRender()
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Check if there are no active render operations.
	 *
	 * @return bool
	 */
	public static function doneRendering()
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Add a location to the array of view locations.
	 *
	 * @param  string $location
	 */
	public static function addLocation( $location )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Add a new namespace to the loader.
	 *
	 * @param  string $namespace
	 * @param  string|array $hints
	 */
	public static function addNamespace( $namespace, $hints )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Prepend a new namespace to the loader.
	 *
	 * @param  string $namespace
	 * @param  string|array $hints
	 */
	public static function prependNamespace( $namespace, $hints )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Register a valid view extension and its engine.
	 *
	 * @param  string $extension
	 * @param  string $engine
	 * @param  \Closure $resolver
	 */
	public static function addExtension( $extension, $engine, $resolver = null )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Get the extension to engine bindings.
	 *
	 * @return array
	 */
	public static function getExtensions()
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Get the engine resolver instance.
	 *
	 * @return EngineResolver
	 */
	public static function getEngineResolver()
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Get an item from the shared data.
	 *
	 * @param  string $key
	 * @param  mixed $default
	 *
	 * @return mixed
	 */
	public static function shared( $key, $default = null )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Get all of the shared data for the environment.
	 *
	 * @return array
	 */
	public static function getShared()
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Check if section exists.
	 *
	 * @param  string $name
	 *
	 * @return bool
	 */
	public static function hasSection( $name )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Get the entire array of sections.
	 *
	 * @return array
	 */
	public static function getSections()
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Get all of the registered named views in environment.
	 *
	 * @return array
	 */
	public static function getNames()
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Escape HTML entities in a string.
	 *
	 * @param  Htmlable|string $value
	 * @return string
	 */
	public static function escape( $value )
	{
		if ( $value instanceof Htmlable )
			return $value->toHtml();

		return htmlentities( $value, ENT_QUOTES, 'UTF-8', false );
	}
}
