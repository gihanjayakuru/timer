<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Silber\Bouncer\Database\Models;

class CreateBouncerTables extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create(Models::table('roles'), function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('name')->index();
			$table->string('title')->nullable();
			$table->integer('level')->unsigned()->nullable();
			$table->string('description')->nullable();
			$table->integer('scope')->nullable()->index();
			$table->boolean('assign_by_default')->default(false);
			$table->boolean('allow_to_be_deleted')->default(true);
			$table->timestamps();
			$table->unique(
				['name', 'scope'],
				'roles_name_unique'
			);
		});

		Schema::create('ability_categories', function ($table) {
			$table->bigIncrements('id');
			$table->string('slug')->unique();
			$table->string('name')->nullable();
			$table->text('default_abilities')->nullable();
			$table->timestamps();
		});

		Schema::create(Models::table('abilities'), function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('name', 255);
			$table->string('title')->nullable();
			$table->bigInteger('entity_id')->unsigned()->nullable();
			$table->string('entity_type', 255)->nullable();
			$table->boolean('only_owned')->default(false);
			$table->text('options')->nullable();
			$table->integer('scope')->nullable()->index();
			$table->timestamps();
			$table->unique(
				['name', 'entity_id', 'entity_type', 'only_owned'],
				'abilities_unique_index'
			);

			$table->integer('ability_category_id')->nullable()->references('id')->on('ability_categories');
		});

		Schema::create(Models::table('assigned_roles'), function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->bigInteger('role_id')->unsigned()->index();
			$table->bigInteger('entity_id')->unsigned();
			$table->string('entity_type');
			$table->integer('restricted_to_id')->unsigned()->nullable();
			$table->string('restricted_to_type')->nullable();
			$table->integer('scope')->nullable()->index();

			$table->index(
				['entity_id', 'entity_type', 'scope'],
				'assigned_roles_entity_index'
			);

			$table->foreign('role_id')
				  ->references('id')->on(Models::table('roles'))
				  ->onUpdate('cascade')->onDelete('cascade');
		});

		Schema::create(Models::table('permissions'), function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->bigInteger('ability_id')->unsigned()->index();
			$table->bigInteger('entity_id')->unsigned()->nullable();
			$table->string('entity_type')->nullable();
			$table->boolean('forbidden')->default(false);
			$table->integer('scope')->nullable()->index();

			$table->index(
				['entity_id', 'entity_type', 'scope'],
				'permissions_entity_index'
			);

			$table->foreign('ability_id')
				  ->references('id')->on(Models::table('abilities'))
				  ->onUpdate('cascade')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop(Models::table('permissions'));
		Schema::drop(Models::table('assigned_roles'));
		Schema::drop(Models::table('roles'));
		Schema::drop(Models::table('abilities'));
		Schema::drop('ability_categories');

	}
}
