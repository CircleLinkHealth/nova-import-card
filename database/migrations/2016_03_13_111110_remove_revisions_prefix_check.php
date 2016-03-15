<?php

use App\Role;
use App\User;
use App\WpBlog;
use App\CLH\Repositories\UserRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Symfony\Component\HttpFoundation\ParameterBag;

class RemoveRevisionsPrefixCheck extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('lv_revisions'))
		{
			Schema::rename('lv_revisions', 'revisions');
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// cant reverse this
	}

}
