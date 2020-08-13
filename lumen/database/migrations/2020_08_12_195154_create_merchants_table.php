<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchants', function (Blueprint $table) {
            $table->increments('id');
            
            $table->string('name', 100)
                ->nullable(false)
                ->unique();
            
            $table->string('url', 100)
                ->nullable(false)
                ->unique();
            
            $table->longText('description');
            
            $table->unsignedInteger('sitemap_category_id')
                ->nullable(false)
                ->comment('The Sitemap cateogory id (can not be null)');
            $table->foreign('sitemap_category_id')
                ->references('id')
                ->on('sitemap_categories')
                ->onDelete('cascade');
                
            $table->boolean('deleted')->default(0);
            $table->dateTimeTz('deleted_at', 0)->nullable(true);
            
            $table->timestamps();
            
            $table->index(['deleted', 'sitemap_category_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('merchants');
    }
}
