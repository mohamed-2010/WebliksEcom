<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBulkDiscountTable  extends Migration
{

    public function up()
    {
        Schema::create('bulk_discount', function (Blueprint $table) {
            $table->id();
            $table->string('category_ids');
            $table->dateTime('date_start');
            $table->dateTime('date_end');
            $table->string('discount');
            $table->string('discount_type');
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
        Schema::dropIfExists('bulk_discount');
    }
}


