<?php namespace Milky\Pagination;

use Milky\Impl\HtmlString;
use Milky\Pagination\LengthAwarePaginator as Paginator;

class BootstrapThreePresenter implements Presenter
{
	use BootstrapThreeNextPreviousButtonRendererTrait, UrlWindowPresenterTrait;

	/**
	 * The paginator implementation.
	 *
	 * @var Paginator
	 */
	protected $paginator;

	/**
	 * The URL window data structure.
	 *
	 * @var array
	 */
	protected $window;

	/**
	 * Create a new Bootstrap presenter instance.
	 *
	 * @param  Paginator $paginator
	 * @param  UrlWindow|null $window
	 * @return void
	 */
	public function __construct( Paginator $paginator, UrlWindow $window = null )
	{
		$this->paginator = $paginator;
		$this->window = is_null( $window ) ? UrlWindow::make( $paginator ) : $window->get();
	}

	/**
	 * Determine if the underlying paginator being presented has pages to show.
	 *
	 * @return bool
	 */
	public function hasPages()
	{
		return $this->paginator->hasPages();
	}

	/**
	 * Convert the URL window into Bootstrap HTML.
	 *
	 * @return HtmlString
	 */
	public function render()
	{
		if ( $this->hasPages() )
			return new HtmlString( sprintf( '<ul class="pagination">%s %s %s</ul>', $this->getPreviousButton(), $this->getLinks(), $this->getNextButton() ) );

		return new HtmlString('');
	}

	/**
	 * Get HTML wrapper for an available page link.
	 *
	 * @param  string $url
	 * @param  int $page
	 * @param  string|null $rel
	 * @return string
	 */
	protected function getAvailablePageWrapper( $url, $page, $rel = null )
	{
		return '<li><a href="' . htmlentities( $url ) . '" ' . ( is_null( $rel ) ? '' : 'rel="' . $rel . '"' ) . '>' . $page . '</a></li>';
	}

	/**
	 * Get HTML wrapper for disabled text.
	 *
	 * @param  string $text
	 * @return string
	 */
	protected function getDisabledTextWrapper( $text )
	{
		return '<li class="disabled"><span>' . $text . '</span></li>';
	}

	/**
	 * Get HTML wrapper for active text.
	 *
	 * @param  string $text
	 * @return string
	 */
	protected function getActivePageWrapper( $text )
	{
		return '<li class="active"><span>' . $text . '</span></li>';
	}

	/**
	 * Get a pagination "dot" element.
	 *
	 * @return string
	 */
	protected function getDots()
	{
		return $this->getDisabledTextWrapper( '...' );
	}

	/**
	 * Get the current page from the paginator.
	 *
	 * @return int
	 */
	protected function currentPage()
	{
		return $this->paginator->currentPage();
	}

	/**
	 * Get the last page from the paginator.
	 *
	 * @return int
	 */
	protected function lastPage()
	{
		return $this->paginator->lastPage();
	}
}
