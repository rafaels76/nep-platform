<?php
// database/migrations/2026_09_11_000001_create_companies_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->ulid('id')->primary(); // identificador permanente (sección 3.1)
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('tax_id')->nullable()->index(); // RIF / EIN / identificación fiscal
            $table->string('status')->default('active'); // active | inactive | suspended
            $table->json('metadata')->nullable(); // contexto operacional extensible sin migraciones nuevas
            $table->timestamps();
            $table->softDeletes(); // nunca se pierde el contexto histórico de una empresa
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
