<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchantAffiliatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // relationship with merchants
        Schema::create('merchant_affiliates', function (Blueprint $table) {
    
            $table->unsignedInteger('merchant_id')
                ->nullable(false);
            $table->foreign('merchant_id')
                ->references('id')
                ->on('merchants')
                ->onDelete('cascade');
            
            $table->longText('logo1_url')
                ->nullable(true);
            
            $table->longText('logo2_url')
                ->nullable(true);
            
            $table->longText('logo3_url')
                ->nullable(true);
            
            // Shipping
            $table->string('shipping_address_first_name', 20)
                ->nullable(true);
            
            $table->string('shipping_address_last_name', 20)
                ->nullable(true);
            
            $table->string('shipping_address_street', 100)
                ->nullable(true);
            
            $table->string('shipping_address_postalcode', 10)
                ->nullable(true);
            
            $table->string('shipping_address_state', 20)
                ->nullable(true);

            $table->string('shipping_address_country_code', 3)
                ->nullable(true)
                ->comment('Format: a three-letter (ISO 3166-1 alpha-3)');;
            
            // Billing
            $table->string('billing_address_first_name', 20)
                ->nullable(true);
            
            $table->string('billing_address_last_name', 20)
                ->nullable(true);
            
            $table->string('billing_address_street', 100)
                ->nullable(true);
            
            $table->string('billing_address_postalcode', 10)
                ->nullable(true);
            
            $table->string('billing_address_state', 20)
                ->nullable(true);

            $table->string('billing_address_country_code', 3)
                ->nullable(true)
                ->comment('Format: a three-letter (ISO 3166-1 alpha-3)');;
            
            $table->decimal('cash_back_rate', 5, 2)
                ->nullable(false);
            
            $table->timestamps();
            
            $table->primary('merchant_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('merchant_affiliates');
    }
}
