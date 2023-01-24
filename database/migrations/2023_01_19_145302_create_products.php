<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
			$table->string('code');
			$table->enum('status', ['published', 'draft', 'trash']);
			$table->longText('url');
			$table->string('creator');
			$table->string('name');
			$table->string('quantity');
			$table->longText('brands');
			$table->longText('categories');
			$table->longText('labels');
			$table->longText('cities');
			$table->longText('purchase_places');
			$table->longText('stores');
			$table->longText("ingredients");
			$table->longText('traces');
			$table->longText('serving_size');
			$table->longText('serving_quantity');
			$table->longText('nutriscore_score');
			$table->longText('nutriscore_grade');
			$table->string('main_category');
			$table->string('image_url');
			$table->string('imported_t');
			$table->timestamps();
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
