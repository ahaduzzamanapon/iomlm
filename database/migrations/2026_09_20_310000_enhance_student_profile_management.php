<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add course access and fee adjustment columns to students table
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'has_course_access')) {
                $table->boolean('has_course_access')->default(true)->after('status');
            }
            if (!Schema::hasColumn('students', 'fee_package_id')) {
                $table->foreignId('fee_package_id')->nullable()->after('has_course_access')
                      ->constrained('course_fee_packages')->nullOnDelete();
            }
            if (!Schema::hasColumn('students', 'monthly_discount')) {
                $table->decimal('monthly_discount', 10, 2)->default(0)->after('fee_package_id');
            }
            if (!Schema::hasColumn('students', 'discount_type')) {
                $table->string('discount_type', 20)->default('FIXED')->after('monthly_discount'); // FIXED or PERCENT
            }
            if (!Schema::hasColumn('students', 'poor_fund_remarks')) {
                $table->text('poor_fund_remarks')->nullable()->after('discount_type');
            }
        });

        // 2. Add description to audit_logs if not present
        Schema::table('audit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('audit_logs', 'description')) {
                $table->string('description', 255)->nullable()->after('event');
            }
        });

        // 3. Create login_histories table
        if (!Schema::hasTable('login_histories')) {
            Schema::create('login_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('device', 50)->nullable();
                $table->string('browser', 50)->nullable();
                $table->string('platform', 50)->nullable();
                $table->boolean('is_impersonated')->default(false);
                $table->foreignId('impersonated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('login_at')->useCurrent();
                $table->timestamps();

                $table->index(['user_id', 'login_at']);
                $table->index(['student_id', 'login_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_histories');

        Schema::table('audit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('audit_logs', 'description')) {
                $table->dropColumn('description');
            }
        });

        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'fee_package_id')) {
                $table->dropForeign(['fee_package_id']);
                $table->dropColumn('fee_package_id');
            }
            $table->dropColumn([
                'has_course_access',
                'monthly_discount',
                'discount_type',
                'poor_fund_remarks',
            ]);
        });
    }
};
