<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_id')->unique();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->json('raw')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('categories');
    }
};
