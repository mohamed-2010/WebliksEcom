<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class AddMetaFieldsToPageTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('page_translations', function (Blueprint $table) {
            $table->text('meta_title')->nullable()->after('content');
            $table->string('meta_description')->nullable()->after('meta_title');
            $table->string('keywords')->nullable()->after('meta_description');
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('page_translations', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description', 'keywords']);
        });
    }
}