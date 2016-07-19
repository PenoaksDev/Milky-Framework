<?php
namespace Penoaks\View;

use Penoaks\Bindings\Bindings;
use Penoaks\Contracts\View\Factory as ViewFactory;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class ViewSkel
{
	private $view;
	private $data;
	private $mergeData;

	public function __construct( $view, $data = [], $mergeData = [] )
	{
		$this->view = $view;
		$this->data = $data;
		$this->mergeData = $mergeData;
	}

	public function make()
	{
		/**
		 * @var ViewFactory
		 */
		$view = Bindings::get( ViewFactory::class );

		return $view->make( $this->view, $this->data, $this->mergeData );
	}

	public static function view( $name, $data = [], $mergeData = [] )
	{
		return new self( $name, $data, $mergeData );
	}
}
