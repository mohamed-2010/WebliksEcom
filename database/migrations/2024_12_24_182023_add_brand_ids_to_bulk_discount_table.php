<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBrandIdsToBulkDiscountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bulk_discount', function (Blueprint $table) {
            $table->text('brand_ids')->nullable()->after('category_ids');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bulk_discount', function (Blueprint $table) {
            $table->dropColumn(['brand_ids']);
        });
    }
}
