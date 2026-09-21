<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create subject_categories table
        if (!Schema::hasTable('subject_categories')) {
            Schema::create('subject_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Seed initial categories matching Bangladeshi Islamic academic context
            DB::table('subject_categories')->insert([
                ['name' => 'আলিম (Alim)', 'code' => 'ALIM', 'description' => 'আলিম কোর্সের নির্ধারিত বিষয়সমূহ', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'দাওরায়ে হাদিস (Dawrah Hadis)', 'code' => 'DAWRAH', 'description' => 'দাওরায়ে হাদিস ও স্নাতকোত্তর সিলেবাস', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'মক্তব ও প্রাথমিক (School Maktab)', 'code' => 'MAKTAB', 'description' => 'মক্তব, নুরানি ও স্কুল স্তরের বিষয়', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'হিফজুল কুরআন (Hifz)', 'code' => 'HIFZ', 'description' => 'হিফজ ও তাজবীদুল কুরআন শিক্ষা', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'ইসলামিক স্টাডিজ (Islamic Studies)', 'code' => 'ISLAMIC', 'description' => 'সাধারণ ও পেশাজীবীদের জন্য ইসলামিক স্টাডিজ', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // 2. Add category_id to subjects table
        if (Schema::hasTable('subjects') && !Schema::hasColumn('subjects', 'category_id')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->unsignedBigInteger('category_id')->nullable()->after('department_id');
                $table->foreign('category_id')->references('id')->on('subject_categories')->nullOnDelete();
            });
        }

        // 3. Enhance subject_modules table
        if (Schema::hasTable('subject_modules')) {
            Schema::table('subject_modules', function (Blueprint $table) {
                if (!Schema::hasColumn('subject_modules', 'file_path')) {
                    $table->string('file_path')->nullable()->after('description');
                }
                if (!Schema::hasColumn('subject_modules', 'drive_link')) {
                    $table->string('drive_link', 500)->nullable()->after('file_path');
                }
                if (!Schema::hasColumn('subject_modules', 'folder_name')) {
                    $table->string('folder_name')->default('General')->after('drive_link');
                }
                if (!Schema::hasColumn('subject_modules', 'is_hidden')) {
                    $table->boolean('is_hidden')->default(false)->after('folder_name');
                }
                if (!Schema::hasColumn('subject_modules', 'recorded_url')) {
                    $table->text('recorded_url')->nullable()->after('is_hidden');
                }
                if (!Schema::hasColumn('subject_modules', 'embed_code')) {
                    $table->text('embed_code')->nullable()->after('recorded_url');
                }
            });
        }

        // 4. Enhance assignments table
        if (Schema::hasTable('assignments')) {
            Schema::table('assignments', function (Blueprint $table) {
                if (!Schema::hasColumn('assignments', 'start_datetime')) {
                    $table->dateTime('start_datetime')->nullable()->after('total_marks');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('assignments') && Schema::hasColumn('assignments', 'start_datetime')) {
            Schema::table('assignments', function (Blueprint $table) {
                $table->dropColumn('start_datetime');
            });
        }

        if (Schema::hasTable('subject_modules')) {
            Schema::table('subject_modules', function (Blueprint $table) {
                $cols = ['file_path', 'drive_link', 'folder_name', 'is_hidden', 'recorded_url', 'embed_code'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('subject_modules', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('subjects') && Schema::hasColumn('subjects', 'category_id')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            });
        }

        if (Schema::hasTable('subject_categories')) {
            Schema::dropIfExists('subject_categories');
        }
    }
};
