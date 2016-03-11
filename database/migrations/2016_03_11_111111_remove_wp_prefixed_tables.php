<?php

use App\User;
use App\WpBlog;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveWpPrefixedTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$programs = WpBlog::all();
		foreach($programs as $program) {
			$name = str_replace(".careplanmanager.com","",$program->domain);
			echo PHP_EOL.'processing program:: '.$name;
			$program->name = $name;
			$program->display_name = ucfirst($name);
			$program->save();

			// remove tables
			echo PHP_EOL.'removing all wp_'.$program->blog_id.'_* tables';
			Schema::dropIfExists('wp_'.$program->blog_id.'_clh_userlogins');
			Schema::dropIfExists('wp_'.$program->blog_id.'_commentmeta');
			Schema::dropIfExists('wp_'.$program->blog_id.'_comments');
			Schema::dropIfExists('wp_'.$program->blog_id.'_links');
			Schema::dropIfExists('wp_'.$program->blog_id.'_options');
			Schema::dropIfExists('wp_'.$program->blog_id.'_postmeta');
			Schema::dropIfExists('wp_'.$program->blog_id.'_posts');
			Schema::dropIfExists('wp_'.$program->blog_id.'_term_relationships');
			Schema::dropIfExists('wp_'.$program->blog_id.'_terms');
			Schema::dropIfExists('wp_'.$program->blog_id.'_term_taxonomy');
			Schema::dropIfExists('ma_'.$program->blog_id.'_outbound_log');
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
