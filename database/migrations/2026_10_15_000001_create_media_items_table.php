<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('media_items', function (Blueprint $table) {
            $table->id();
            $table->string('type', 10);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('creator')->nullable();
            $table->unsignedSmallInteger('published_year')->nullable();
            $table->text('cover_url')->nullable();
            $table->string('external_source')->nullable();
            $table->string('external_id')->nullable();
            $table->timestamps();
            $table->unique(['external_source', 'external_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('media_items');
    }
};
