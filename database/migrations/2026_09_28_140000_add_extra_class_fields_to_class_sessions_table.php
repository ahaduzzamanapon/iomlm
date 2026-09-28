<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('class_sessions', 'is_extra_class')) {
                $table->boolean('is_extra_class')->default(false)->after('routine_entry_id');
            }
            if (!Schema::hasColumn('class_sessions', 'title')) {
                $table->string('title')->nullable()->after('is_extra_class');
            }
            if (!Schema::hasColumn('class_sessions', 'end_time')) {
                $table->time('end_time')->nullable()->after('start_time');
            }
            if (!Schema::hasColumn('class_sessions', 'reason')) {
                $table->string('reason')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('class_sessions', 'is_extra_class')) $columns[] = 'is_extra_class';
            if (Schema::hasColumn('class_sessions', 'title')) $columns[] = 'title';
            if (Schema::hasColumn('class_sessions', 'end_time')) $columns[] = 'end_time';
            if (Schema::hasColumn('class_sessions', 'reason')) $columns[] = 'reason';

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
