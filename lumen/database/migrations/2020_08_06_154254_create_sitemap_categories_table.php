<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSitemapCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::create('sitemap_categories', function (Blueprint $table) {
            $table->increments('id');
            
            $table->string('name', 50)
                ->nullable(false)
                ->unique();
            
            $table->integer('parent_id')
                ->unsigned()
                ->nullable(true)
                ->comment('The Sitemap cateogory parent id (can be null and it means is one of the top categories)');
            
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
        Schema::dropIfExists('sitemap_categories');
    }
}
