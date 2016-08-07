<?php namespace Milky\Database\Eloquent\Nested\Generators;

use Milky\Filesystem\Filesystem;

abstract class Generator
{
	/**
	 * The filesystem instance.
	 *
	 * @var Filesystem
	 */
	protected $files = null;

	/**
	 * Create a new MigrationGenerator instance.
	 *
	 * @param Filesystem $files
	 * @return void
	 */
	function __construct( Filesystem $files )
	{
		$this->files = $files;
	}

	/**
	 * Get the filesystem instance.
	 *
	 * @return Filesystem
	 */
	public function getFilesystem()
	{
		return $this->files;
	}

	/**
	 * Get the given stub by name.
	 *
	 * @param  string $table
	 */
	protected function getStub( $name )
	{
		if ( stripos( $name, '.php' ) === false )
			$name = $name . '.php';

		$this->files->get( $this->getStubPath() . '/' . $name );
	}

	/**
	 * Get the path to the stubs.
	 *
	 * @return string
	 */
	public function getStubPath()
	{
		return __DIR__ . '/stubs';
	}

	/**
	 * Parse the provided stub and replace via the array given.
	 *
	 * @param string $stub
	 * @param string $replacements
	 * @return string
	 */
	protected function parseStub( $stub, $replacements = [] )
	{
		$output = $stub;

		foreach ( $replacements as $key => $replacement )
		{
			$search = '{{' . $key . '}}';
			$output = str_replace( $search, $replacement, $output );
		}

		return $output;
	}

	/**
	 * Inflect to a class name
	 *
	 * @param string $input
	 * @return string
	 */
	protected function classify( $input )
	{
		return studly_case( str_singular( $input ) );
	}

	/**
	 * Inflect to table name
	 *
	 * @param string $input
	 * @return string
	 */
	protected function tableize( $input )
	{
		return snake_case( str_plural( $input ) );
	}
}
