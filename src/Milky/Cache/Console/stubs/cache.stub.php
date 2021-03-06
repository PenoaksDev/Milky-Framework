<?php


use Milky\Database\Migrations\Migration;

use Milky\Database\Schema\Blueprint;

class CreateCacheTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 */
	public function up()
	{
		Schema::create( 'cache', function ( Blueprint $table )
		{
			$table->string( 'key' )->unique();
			$table->text( 'value' );
			$table->integer( 'expiration' );
		} );
	}

	/**
	 * Reverse the migrations.
	 *
	 */
	public function down()
	{
		Schema::drop( 'cache' );
	}
}
