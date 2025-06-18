<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNavbarTable  extends Migration
{

    public function up()
    {
        Schema::create('home_navbar_translations', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('link');
            $table->string('lang'); // to store the language code
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
        Schema::dropIfExists('home_navbar_translations');
    }
}


