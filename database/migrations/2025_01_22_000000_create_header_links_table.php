<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHeaderLinksTable extends Migration
{
    public function up()
    {
        Schema::create('header_links', function (Blueprint $table) {
            $table->id();
            // The actual URL (or route) we want to open when this link is clicked
            $table->string('url')->nullable(); 
            // If you prefer a slug or something else, you can include that
            $table->string('slug')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('header_links');
    }
}
