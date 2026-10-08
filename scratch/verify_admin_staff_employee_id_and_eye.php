<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\UserManagementController;

function assertTest($condition, $message) {
    if (!$condition) {
        echo "FAILED: {$message}\n";
        exit(1);
    }
    echo "PASSED: {$message}\n";
}

echo "=== VERIFYING TASK 74: ADMIN / STAFF EMPLOYEE ID & PASSWORD EYE TOGGLE ===\n";

// 1. Database schema check
assertTest(Schema::hasColumn('users', 'employee_id'), "Column 'employee_id' exists in 'users' table");

// 2. Model fillable check
$userModel = new User();
assertTest(in_array('employee_id', $userModel->getFillable(), true), "'employee_id' is present in User fillable attributes");

// 3. Admin / Staff creation test via UserManagementController
$adminUser = User::where('role', 'super_admin')->first() ?: User::where('role', 'admin')->first();
if (!$adminUser) {
    $adminUser = User::create([
        'name' => 'Root Super Admin',
        'email' => 'superadmin_test_' . time() . '@example.com',
        'password' => bcrypt('password123'),
        'role' => 'super_admin',
        'is_active' => true,
    ]);
}
\Illuminate\Support\Facades\Auth::login($adminUser);

$testEmail = 'staff_test_' . time() . '@example.com';
$testEmpId = 'EMP-' . rand(1000, 9999);

$controller = new UserManagementController();
$storeRequest = Request::create(route('admin.users.store'), 'POST', [
    'name' => 'আহমেদ রফিক',
    'email' => $testEmail,
    'employee_id' => $testEmpId,
    'designation' => 'অফিস সহকারী ও হিসাব কর্মকর্তা',
    'password' => 'secret12345',
    'role' => 'admin',
    'permissions' => ['accounts', 'students'],
]);

$response = $controller->store($storeRequest);
assertTest($response instanceof \Illuminate\Http\RedirectResponse, "UserManagementController::store redirects successfully");

$createdUser = User::where('email', $testEmail)->first();
assertTest($createdUser !== null, "Created user found in database");
assertTest($createdUser->employee_id === $testEmpId, "Employee ID matches submitted value: '{$createdUser->employee_id}' === '{$testEmpId}'");
assertTest($createdUser->designation === 'অফিস সহকারী ও হিসাব কর্মকর্তা', "Designation saved properly");

// 4. Update test with new employee_id
$newEmpId = $testEmpId . '-UPD';
$updateRequest = Request::create(route('admin.users.update', $createdUser), 'PUT', [
    'name' => 'আহমেদ রফিক আপডেট',
    'email' => $testEmail,
    'employee_id' => $newEmpId,
    'designation' => 'প্রধান হিসাব কর্মকর্তা',
    'role' => 'admin',
    'permissions' => ['accounts', 'students', 'reports'],
]);

$updateResponse = $controller->update($updateRequest, $createdUser);
$createdUser->refresh();
assertTest($createdUser->employee_id === $newEmpId, "Employee ID updated successfully to '{$createdUser->employee_id}'");

// 5. Search test by employee_id
$searchRequest = Request::create(route('admin.users.index'), 'GET', [
    'search' => $newEmpId,
]);
$indexResponse = $controller->index($searchRequest);
$indexHtml = $indexResponse->render();
assertTest(str_contains($indexHtml, $newEmpId), "Search by employee_id returns the user and renders in HTML table");
assertTest(str_contains($indexHtml, 'ID: ' . $newEmpId), "Employee ID is rendered with 'ID:' badge format in index table");

// 6. View verification: admin/users/create.blade.php
$createBlade = file_get_contents(resource_path('views/admin/users/create.blade.php'));
assertTest(str_contains($createBlade, 'name="employee_id"'), "create.blade.php contains name='employee_id' input");
assertTest(str_contains($createBlade, 'ইমপ্লোয়ী আইডি (Employee ID)'), "create.blade.php contains Bengali Employee ID label");
assertTest(str_contains($createBlade, 'togglePasswordVisibility'), "create.blade.php contains togglePasswordVisibility function call");
assertTest(str_contains($createBlade, 'passwordEyeIcon'), "create.blade.php contains eye icon for password field");
assertTest(str_contains($createBlade, 'fa-eye'), "create.blade.php contains FontAwesome eye icon");

// 7. View verification: admin/users/edit.blade.php
$editBlade = file_get_contents(resource_path('views/admin/users/edit.blade.php'));
assertTest(str_contains($editBlade, 'name="employee_id"'), "edit.blade.php contains name='employee_id' input");
assertTest(str_contains($editBlade, 'togglePasswordVisibility'), "edit.blade.php contains togglePasswordVisibility function call");
assertTest(str_contains($editBlade, 'editPasswordEyeIcon'), "edit.blade.php contains eye icon for password field in edit view");

// Cleanup test user
$createdUser->delete();

echo "\n>>> ALL TASK 74 ASSERTIONS PASSED! EXIT CODE 0 <<<\n";
exit(0);
