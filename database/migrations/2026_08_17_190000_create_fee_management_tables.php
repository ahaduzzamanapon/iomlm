<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── Fee Heads ──────────────────────────────────────────────
        Schema::create('fee_heads', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);                    // e.g. Tuition Fee, Mid Term Fee
            $table->string('slug', 100)->unique();          // auto-generated
            $table->boolean('is_static')->default(false);   // Admission & Retake = static
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // ── Course Fee Packages ────────────────────────────────────
        Schema::create('course_fee_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);                    // e.g. Category 50, Category 100
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Course Fee Package Items ───────────────────────────────
        Schema::create('course_fee_package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('course_fee_packages')->cascadeOnDelete();
            $table->foreignId('fee_head_id')->constrained('fee_heads')->restrictOnDelete();
            $table->string('label', 150)->nullable();       // display label override
            $table->unsignedSmallInteger('quantity')->default(1);    // total count (e.g. 36 classes)
            $table->decimal('amount_per_unit', 10, 2)->default(0);  // per class/unit
            $table->decimal('total_amount', 10, 2)->default(0);     // quantity × amount_per_unit
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed static fee heads
        DB::table('fee_heads')->insert([
            [
                'name'       => 'Admission Fee',
                'slug'       => 'admission_fee',
                'is_static'  => true,
                'is_active'  => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Retake Fee',
                'slug'       => 'retake_fee',
                'is_static'  => true,
                'is_active'  => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('course_fee_package_items');
        Schema::dropIfExists('course_fee_packages');
        Schema::dropIfExists('fee_heads');
    }
};
