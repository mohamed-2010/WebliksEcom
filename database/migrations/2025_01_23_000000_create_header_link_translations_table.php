<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHeaderLinkTranslationsTable extends Migration
{
    public function up()
    {
        Schema::create('header_link_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('header_link_id');
            
            // The display label for this link in the given language
            $table->string('url')->nullable();
            
            // The language code, e.g. 'en', 'ar', etc.
            $table->string('lang', 5)->index(); 
            
            // Slug if you want it translatable as well, or you can omit
            $table->string('slug')->nullable(); 
            
            $table->timestamps();

            $table->foreign('header_link_id')
                  ->references('id')->on('header_links')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('header_link_translations');
    }
}
