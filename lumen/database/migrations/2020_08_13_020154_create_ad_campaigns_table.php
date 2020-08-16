<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ad_campaigns', function (Blueprint $table) {
            $table->increments('id');
            
            $table->string('name', 100)
                ->nullable(false)
                ->unique();
            
            $table->decimal('cash_back_rate', 5, 2)
                ->nullable(false);
            
            $table->dateTimeTz('date_from', 0)->nullable(false);
            $table->dateTimeTz('date_to', 0)->nullable(false);
            
            $table->boolean('deleted')->default(0);
            $table->dateTimeTz('deleted_at', 0)->nullable(true);
            
            $table->timestamps();
            
            $table->index(['deleted', 'name']);
        });
        
        // relationship between ad_campaigns and Merchants
        Schema::create('ad_campaign_merchants', function (Blueprint $table) {
            
            $table->unsignedInteger('ad_campaign_id')
                ->nullable(false);
            $table->foreign('ad_campaign_id')
                ->references('id')
                ->on('ad_campaigns')
                ->onDelete('cascade');
            
            $table->unsignedInteger('merchant_id')
                ->nullable(false);
            $table->foreign('merchant_id')
                ->references('id')
                ->on('merchants')
                ->onDelete('cascade');
            
            $table->timestamps();
            
            $table->unique(['ad_campaign_id', 'merchant_id']);
            
            $table->index('merchant_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ad_campaigns');
        Schema::dropIfExists('ad_campaigns_merchants');
    }
}
