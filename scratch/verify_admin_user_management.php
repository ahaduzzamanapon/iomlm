<?php

use App\Models\SupportDepartment;
use App\Models\User;
use App\Http\Middleware\AdminModuleAccessMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "====================================================\n";
echo "=== VERIFYING ADMIN ROLE-BASED USER MANAGEMENT   ===\n";
echo "====================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertCondition($description, $condition) {
    global $passCount, $failCount;
    if ($condition) {
        echo "  [PASS] {$description}\n";
        $passCount++;
    } else {
        echo "  [FAIL] {$description}\n";
        $failCount++;
    }
}

// 1. Verify User::adminModules()
$modules = User::adminModules();
assertCondition("User::adminModules() returns 11 functional modules", count($modules) === 11);
assertCondition("Module 'accounts' exists with Bangladeshi title", isset($modules['accounts']) && !empty($modules['accounts']['name']));
assertCondition("Module 'user_management' exists", isset($modules['user_management']));

// 2. Verify Super Admin Access
$superAdmin = new User([
    'name' => 'Super Test Admin',
    'email' => 'super_test_' . time() . '@example.com',
    'role' => 'super_admin',
    'admin_permissions' => null,
]);
assertCondition("Super Admin isSuperAdmin() returns true", $superAdmin->isSuperAdmin() === true);
assertCondition("Super Admin canAccess('academic') returns true", $superAdmin->canAccess('academic') === true);
assertCondition("Super Admin canAccess('accounts') returns true", $superAdmin->canAccess('accounts') === true);
assertCondition("Super Admin canAccess('user_management') returns true", $superAdmin->canAccess('user_management') === true);

// 3. Verify Legacy/Unconfigured Admin Access (empty permissions)
$legacyAdmin = new User([
    'name' => 'Legacy Admin',
    'email' => 'legacy_test_' . time() . '@example.com',
    'role' => 'admin',
    'admin_permissions' => null,
]);
assertCondition("Legacy Admin without permissions has full access to 'accounts'", $legacyAdmin->canAccess('accounts') === true);
assertCondition("Legacy Admin without permissions has full access to 'academic'", $legacyAdmin->canAccess('academic') === true);

// 4. Verify Restricted Admin User (Permissions set)
$restrictedAdmin = User::create([
    'name'                => 'Accounts Staff',
    'email'               => 'staff_' . uniqid() . '@example.com',
    'password'            => bcrypt('password123'),
    'role'                => 'admin',
    'designation'         => 'সহকারী হিসাব কর্মকর্তা',
    'admin_permissions'   => ['accounts', 'support'],
    'can_provide_support' => true,
    'is_active'           => true,
]);

assertCondition("Restricted Admin canAccess('accounts') returns true", $restrictedAdmin->canAccess('accounts') === true);
assertCondition("Restricted Admin canAccess('support') returns true", $restrictedAdmin->canAccess('support') === true);
assertCondition("Restricted Admin canAccess('academic') returns false", $restrictedAdmin->canAccess('academic') === false);
assertCondition("Restricted Admin canAccess('teachers') returns false", $restrictedAdmin->canAccess('teachers') === false);
assertCondition("Restricted Admin canAccess('user_management') returns false", $restrictedAdmin->canAccess('user_management') === false);

// 5. Verify Support Department Sync
$dept = SupportDepartment::firstOrCreate(
    ['name' => 'একাউন্টস হেল্পডেস্ক'],
    ['description' => 'ফি ও পেমেন্ট সংক্রান্ত হেল্পডেস্ক', 'is_active' => true]
);
$restrictedAdmin->supportDepartments()->sync([$dept->id]);
$restrictedAdmin->load('supportDepartments');
assertCondition("Support Department successfully linked to admin user", $restrictedAdmin->supportDepartments->contains($dept->id));
assertCondition("Restricted admin isSupportAgent() returns true", $restrictedAdmin->isSupportAgent() === true);

// 6. Verify AdminModuleAccessMiddleware
auth()->login($restrictedAdmin);

$middleware = new AdminModuleAccessMiddleware();

// Test 6a: Allowed module request
$requestAllowed = Request::create('/admin/accounts', 'GET');
$responseAllowed = $middleware->handle($requestAllowed, function ($req) {
    return response('OK', 200);
}, 'accounts');
assertCondition("Middleware allows request when user has permission (200 OK)", $responseAllowed->getStatusCode() === 200);

// Test 6b: Disallowed module request (Web redirect to dashboard with error)
$requestDenied = Request::create('/admin/academic-years', 'GET');
$responseDenied = $middleware->handle($requestDenied, function ($req) {
    return response('OK', 200);
}, 'academic');
assertCondition("Middleware blocks request when user lacks permission (302 Redirect)", $responseDenied->getStatusCode() === 302);
assertCondition("Session error flash message exists on denied request", session()->has('error'));

// Test 6c: Disallowed module request (JSON / AJAX request returns 403)
$requestJson = Request::create('/admin/academic-years', 'GET', [], [], [], [
    'HTTP_ACCEPT' => 'application/json',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
]);
$responseJson = $middleware->handle($requestJson, function ($req) {
    return response('OK', 200);
}, 'academic');
assertCondition("Middleware returns 403 JSON response for unauthorized AJAX requests", $responseJson->getStatusCode() === 403);

// 7. Verify Active Status Toggle
$restrictedAdmin->is_active = false;
$restrictedAdmin->save();
$freshUser = User::find($restrictedAdmin->id);
assertCondition("Admin user active status toggles to false", $freshUser->is_active === false);

// 8. Clean up test records
$restrictedAdmin->supportDepartments()->detach();
$restrictedAdmin->delete();
assertCondition("Test user cleanly deleted from database", User::find($restrictedAdmin->id) === null);

echo "\n====================================================\n";
echo "SUMMARY: Passes = {$passCount}, Failures = {$failCount}\n";
echo "====================================================\n";

if ($failCount > 0) {
    exit(1);
}

exit(0);
