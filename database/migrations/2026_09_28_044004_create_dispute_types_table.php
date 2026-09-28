<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispute_types', function (Blueprint $table) {
            $table->id();

            $table->string('name_en');
            $table->string('name_bn');

            $table->text('description_en')->nullable();
            $table->text('description_bn')->nullable();

            $table->boolean('status')
                ->default(true)
                ->index();

            $table->timestamps();

            $table->unique('name_en');
            $table->unique('name_bn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispute_types');
    }
};
