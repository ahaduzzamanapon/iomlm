<?php

require __DIR__ . '/../vendor/autoload.php';

echo "=== Verifying Modal Scroll and Click-Outside to Close ===\n";

$bladePath = __DIR__ . '/../resources/views/student/fees/index.blade.php';
assert(file_exists($bladePath), "Blade file index.blade.php must exist");

$content = file_get_contents($bladePath);

// 1. Verify payInvoiceModal scrolling and click-outside close
assert(str_contains($content, 'id="payInvoiceModal"'), "payInvoiceModal must exist");
assert(
    preg_match('/id="payInvoiceModal"[^>]*overflow-y:\s*auto/i', $content),
    "payInvoiceModal must have overflow-y: auto for vertical scrolling on backdrop"
);
assert(
    preg_match('/id="payInvoiceModal"[^>]*onclick="if\(event\.target===this\)\s*closePayModal\(\)"/i', $content),
    "payInvoiceModal must have click-outside handler on backdrop"
);
assert(
    preg_match('/<form id="payForm"[^>]*overflow-y:\s*auto/i', $content),
    "payForm must have overflow-y: auto so inner modal content scrolls smoothly"
);
echo "✓ payInvoiceModal: Scrollable container & form, and click-outside handler verified\n";

// 2. Verify adminParticularEditModal scrolling and click-outside close
assert(str_contains($content, 'id="adminParticularEditModal"'), "adminParticularEditModal must exist");
assert(
    preg_match('/id="adminParticularEditModal"[^>]*overflow-y:\s*auto/i', $content),
    "adminParticularEditModal must have overflow-y: auto"
);
assert(
    preg_match('/id="adminParticularEditModal"[^>]*onclick="if\(event\.target===this\)\s*closeAdminParticularEditModal\(\)"/i', $content),
    "adminParticularEditModal must have click-outside handler on backdrop"
);
assert(
    preg_match('/<form id="adminParticularEditForm"[^>]*overflow-y:\s*auto/i', $content),
    "adminParticularEditForm must have overflow-y: auto"
);
echo "✓ adminParticularEditModal: Scrollable container & form, and click-outside handler verified\n";

// 3. Verify adminAddFeeModal scrolling and click-outside close
assert(str_contains($content, 'id="adminAddFeeModal"'), "adminAddFeeModal must exist");
assert(
    preg_match('/id="adminAddFeeModal"[^>]*overflow-y:\s*auto/i', $content),
    "adminAddFeeModal must have overflow-y: auto"
);
assert(
    preg_match('/id="adminAddFeeModal"[^>]*onclick="if\(event\.target===this\)\s*closeAdminAddFeeModal\(\)"/i', $content),
    "adminAddFeeModal must have click-outside handler on backdrop"
);
assert(
    preg_match('/<form id="adminAddFeeForm"[^>]*overflow-y:\s*auto/i', $content),
    "adminAddFeeForm must have overflow-y: auto"
);
echo "✓ adminAddFeeModal: Scrollable container & form, and click-outside handler verified\n";

// 4. Verify Global Window Event Listeners for Backdrop Click and Escape Key
assert(
    str_contains($content, "window.addEventListener('click'") &&
    str_contains($content, "closePayModal()") &&
    str_contains($content, "closeAdminParticularEditModal()") &&
    str_contains($content, "closeAdminAddFeeModal()"),
    "Blade must contain global window click listener closing all modals on backdrop click"
);
assert(
    str_contains($content, "window.addEventListener('keydown'") &&
    str_contains($content, "Escape"),
    "Blade must contain Escape key listener to dismiss active modals"
);
echo "✓ Global click and Escape key listeners verified\n";

// 5. Verify Body Scroll Lock on Open and Unlock on Close
assert(
    str_contains($content, "document.body.style.overflow = 'hidden'") &&
    str_contains($content, "document.body.style.overflow = ''"),
    "Blade must lock body scroll on open and restore on close"
);
echo "✓ Body scroll locking/unlocking verified\n";

echo "\nALL ASSERTIONS PASSED! Modal scroll and click-outside closing fully verified.\n";
exit(0);
