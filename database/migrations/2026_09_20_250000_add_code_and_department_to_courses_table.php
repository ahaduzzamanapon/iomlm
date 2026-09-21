<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'code')) {
                $table->string('code', 10)->nullable()->after('name');
            }
            if (!Schema::hasColumn('courses', 'department')) {
                $table->string('department', 100)->nullable()->after('department_id');
            }
        });

        // Seed initial codes and departments based on user requirements & screenshots
        $courses = DB::table('courses')->get();
        foreach ($courses as $c) {
            $name = $c->name;
            $code = null;
            $dept = 'BA in Dawah and Islamic Studies';

            if (stripos($name, 'Alim') !== false) {
                $code = '01';
                $dept = 'BA in Dawah and Islamic Studies';
            } elseif (stripos($name, 'Farz') !== false) {
                $code = '02';
                $dept = 'Farz E Ain Course';
            } elseif (stripos($name, 'Hifz') !== false) {
                $code = '03';
                $dept = 'Hifz Course';
            } elseif (stripos($name, 'Nazera') !== false || stripos($name, 'নাজেরা') !== false) {
                $code = '04';
                $dept = 'Nazera Course';
            } elseif (stripos($name, 'Maktab') !== false) {
                $code = '05';
                $dept = 'School Maktab';
            } elseif (stripos($name, 'Tajweed') !== false || stripos($name, 'তাজবীদ') !== false) {
                $code = '10';
                $dept = 'Single Course';
            } elseif (stripos($name, 'Aqeedah') !== false || stripos($name, 'আকিদা') !== false) {
                $code = '11';
                $dept = 'Single Course';
            } elseif (stripos($name, 'Fiqh') !== false || stripos($name, 'ফিকহ') !== false) {
                $code = '12';
                $dept = 'Single Course';
            } elseif (stripos($name, 'Dua') !== false || stripos($name, 'দুয়া') !== false || stripos($name, 'DNS') !== false) {
                $code = '13';
                $dept = 'Single Course';
            } elseif (stripos($name, 'Adab') !== false || stripos($name, 'আদাব') !== false || stripos($name, 'ATI') !== false) {
                $code = '14';
                $dept = 'Single Course';
            } elseif (stripos($name, 'Seerah') !== false || stripos($name, 'সিরাত') !== false || stripos($name, 'SER') !== false) {
                $code = '15';
                $dept = 'Single Course';
            } elseif (stripos($name, 'Dawrah') !== false || stripos($name, 'দাওরাহ') !== false) {
                $code = '16';
                $dept = 'Dawrah Hadith';
            } else {
                $code = str_pad(($c->id % 100), 2, '0', STR_PAD_LEFT);
                $dept = 'BA in Dawah and Islamic Studies';
            }

            DB::table('courses')->where('id', $c->id)->update([
                'code'       => $c->code ?: $code,
                'department' => $c->department ?: $dept,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'code')) {
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('courses', 'department')) {
                $table->dropColumn('department');
            }
        });
    }
};
