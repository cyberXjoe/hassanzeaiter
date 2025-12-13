<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('category_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('handle');
            $table->string('type');
            $table->boolean('required')->default(false);
            $table->json('meta')->nullable();
            $table->unsignedBigInteger('external_id')->nullable();
            $table->timestamps();
            $table->unique(['category_id', 'handle']);
        });
    }

    public function down() {
        Schema::dropIfExists('category_fields');
    }
};
