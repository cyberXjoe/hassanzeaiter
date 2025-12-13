<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('category_field_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_field_id')->constrained('category_fields')->cascadeOnDelete();
            $table->string('label');
            $table->string('value');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['category_field_id', 'value']);
        });
    }

    public function down() {
        Schema::dropIfExists('category_field_options');
    }
};
