<?php
require_once __DIR__ . '/session_bootstrap.php';
mitas_start_session();
require_once __DIR__ . '/auth.php';

// CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Validate CSRF for POST requests
$csrf_ok = true;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_ok = isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

$dbFile = __DIR__ . '/data/mitas_system.sqlite';
if (getenv('DATABASE_URL') === false && !is_dir(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0775, true);
}

$translations = [
    'en' => [
        'site_title' => 'Mitas Online Business System',
        'welcome' => 'Welcome to the online business system',
        'subtitle' => 'Manage inventory, customers, sales, and invoices from any browser.',
        'login_title' => 'Sign in',
        'forgot_password' => 'Forgot password?',
        'change_password' => 'Change password',
        'current_password' => 'Current password',
        'new_password' => 'New password (12+ characters with upper, lower, number, and symbol)',
        'confirm_password' => 'Confirm new password',
        'generate_password' => 'Generate strong password',
        'show_password' => 'Show',
        'hide_password' => 'Hide',
        'password_updated' => 'Password changed successfully.',
        'password_current_invalid' => 'Current password is incorrect.',
        'passwords_do_not_match' => 'The new passwords do not match.',
        'password_change_required' => 'Change your temporary password before continuing.',
        'back_to_sign_in' => 'Back to sign in',
        'login_choose_title' => 'Choose your sign-in',
        'login_choose_description' => 'Select the sign-in page assigned to your account.',
        'login_admin_title' => 'Administrator sign-in',
        'login_staff_title' => 'Staff sign-in',
        'login_manager_title' => 'Manager sign-in',
        'role_admin' => 'Admin',
        'role_staff' => 'Staff',
        'role_manager' => 'Manager',
        'login_admin_description' => 'Sign in with your administrator account.',
        'login_staff_description' => 'Sign in with your staff account.',
        'login_manager_description' => 'Sign in with your manager account.',
        'back_to_roles' => 'Choose another role',
        'continue_to_login' => 'Continue to sign in',
        'skip_to_content' => 'Skip to content',
        'account_management' => 'Account management',
        'create_account' => 'Create account',
        'new_account_password' => 'Password (at least 12 characters)',
        'account_role' => 'Role',
        'account_status' => 'Status',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'save_account' => 'Save account',
        'save_new_account' => 'Create account',
        'reset_password' => 'New password (leave blank to keep current)',
        'account_created' => 'Account created. The user must change the temporary password at sign-in.',
        'account_updated' => 'Account updated. Any temporary password must be changed at sign-in.',
        'account_exists' => 'That username is already in use.',
        'account_invalid' => 'Enter a valid username, role, and status.',
        'password_too_short' => 'Password must be at least 12 characters.',
        'account_self_lockout' => 'You cannot deactivate or change the role of your own account.',
        'last_admin_required' => 'At least one active Admin account must remain.',
        'username' => 'Username',
        'password' => 'Password',
        'login' => 'Login',
        'logout' => 'Logout',
        'language' => 'Language',
        'english' => 'English',
        'swahili' => 'Swahili',
        'apply' => 'Apply',
        'dashboard' => 'Dashboard',
        'products' => 'Products',
        'customers' => 'Customers',
        'suppliers' => 'Suppliers',
        'purchases' => 'Sales Orders',
        'invoices' => 'Invoices',
        'revenue' => 'Revenue',
        'low_stock' => 'Low Stock',
        'total_products' => 'Total Products',
        'total_customers' => 'Total Customers',
        'total_suppliers' => 'Total Suppliers',
        'total_invoices' => 'Invoices',
        'inventory_value' => 'Inventory Value',
        'add_product' => 'Add Product',
        'product_name' => 'Product name',
        'category' => 'Category',
        'quantity' => 'Quantity',
        'buy_price' => 'Buy price',
        'sell_price' => 'Sell price',
        'location' => 'Location',
        'save_product' => 'Save Product',
        'add_customer' => 'Add Customer',
        'customer_name' => 'Customer name',
        'phone' => 'Phone',
        'email' => 'Email',
        'address' => 'Address',
        'save_customer' => 'Save Customer',
        'add_supplier' => 'Add Supplier',
        'supplier_name' => 'Supplier name',
        'save_supplier' => 'Save Supplier',
        'create_purchase' => 'Create Sales Order',
        'purchase_item' => 'Sale item',
        'purchase_category' => 'Sale category',
        'purchase_quantity' => 'Sale quantity',
        'purchase_amount' => 'Sales amount (TZS)',
        'save_purchase' => 'Save Sales Order',
        'apply_adjustment' => 'Apply Adjustment',
        'sale_category' => 'Sale category',
        'discount_percent' => 'Discount (%)',
        'document_queue' => 'Document Queue',
        'pending' => 'Pending',
        'paid' => 'Paid',
        'sold' => 'Sold',
        'print' => 'Print',
        'create_invoice' => 'Create Invoice',
        'invoice_number' => 'Invoice number',
        'invoice_item' => 'Item description',
        'invoice_total' => 'Invoice total',
        'invoice_notes' => 'Invoice notes',
        'save_invoice' => 'Save Invoice',
        'inventory_list' => 'Inventory List',
        'recent_orders' => 'Recent Orders',
        'recent_invoices' => 'Recent Invoices',
        'customer' => 'Customer',
        'item' => 'Item',
        'amount' => 'Amount',
        'status' => 'Status',
        'notes' => 'Notes',
        'created' => 'Created',
        'product_added' => 'Product saved successfully.',
        'payment_added' => 'Payment recorded successfully.',
        'adjustment_applied' => 'Stock adjusted successfully.',
        'order_placed' => 'Order recorded successfully.',
        'customer_added' => 'Customer saved successfully.',
        'supplier_added' => 'Supplier saved successfully.',
        'purchase_added' => 'Sales order saved successfully.',
        'invoice_added' => 'Invoice saved successfully.',
        'print_invoice' => 'Print Invoice',
        'no_records' => 'No records yet.'
    ],
    'sw' => [
        'site_title' => 'Mfumo wa Biashara wa Mitas Mtandaoni',
        'welcome' => 'Karibu kwenye mfumo wa biashara mtandaoni',
        'subtitle' => 'Dhibiti hesabu, wateja, mauzo, na ankara kutoka kivinjari chochote.',
        'login_title' => 'Ingia',
        'forgot_password' => 'Umesahau nenosiri?',
        'change_password' => 'Badilisha nenosiri',
        'current_password' => 'Nenosiri la sasa',
        'new_password' => 'Nenosiri jipya (herufi 12+, kubwa, ndogo, namba na alama)',
        'confirm_password' => 'Thibitisha nenosiri jipya',
        'generate_password' => 'Tengeneza nenosiri imara',
        'show_password' => 'Onyesha',
        'hide_password' => 'Ficha',
        'password_updated' => 'Nenosiri limebadilishwa.',
        'password_current_invalid' => 'Nenosiri la sasa si sahihi.',
        'passwords_do_not_match' => 'Nenosiri jipya halilingani.',
        'password_change_required' => 'Badilisha nenosiri la muda kabla ya kuendelea.',
        'back_to_sign_in' => 'Rudi kuingia',
        'login_choose_title' => 'Chagua ukurasa wa kuingia',
        'login_choose_description' => 'Chagua ukurasa wa kuingia uliotengewa akaunti yako.',
        'login_admin_title' => 'Kuingia kwa msimamizi',
        'login_staff_title' => 'Kuingia kwa mfanyakazi',
        'login_manager_title' => 'Kuingia kwa meneja',
        'role_admin' => 'Msimamizi',
        'role_staff' => 'Mfanyakazi',
        'role_manager' => 'Meneja',
        'login_admin_description' => 'Ingia kwa kutumia akaunti yako ya msimamizi.',
        'login_staff_description' => 'Ingia kwa kutumia akaunti yako ya mfanyakazi.',
        'login_manager_description' => 'Ingia kwa kutumia akaunti yako ya meneja.',
        'back_to_roles' => 'Chagua nafasi nyingine',
        'continue_to_login' => 'Endelea kuingia',
        'skip_to_content' => 'Ruka hadi kwenye maudhui',
        'account_management' => 'Usimamizi wa akaunti',
        'create_account' => 'Tengeneza akaunti',
        'new_account_password' => 'Nenosiri (angalau herufi 12)',
        'account_role' => 'Nafasi',
        'account_status' => 'Hali',
        'active' => 'Inatumika',
        'inactive' => 'Haifanyi kazi',
        'save_account' => 'Hifadhi akaunti',
        'save_new_account' => 'Tengeneza akaunti',
        'reset_password' => 'Nenosiri jipya (acha wazi kuhifadhi la sasa)',
        'account_created' => 'Akaunti imetengenezwa. Mtumiaji lazima abadilishe nenosiri la muda atakapoingia.',
        'account_updated' => 'Akaunti imesasishwa. Nenosiri lolote la muda lazima libadilishwe mtumiaji anapoingia.',
        'account_exists' => 'Jina hilo la mtumiaji tayari linatumika.',
        'account_invalid' => 'Weka jina, nafasi na hali sahihi.',
        'password_too_short' => 'Nenosiri lazima liwe na herufi 12 au zaidi.',
        'account_self_lockout' => 'Huwezi kuzima akaunti yako au kubadilisha nafasi yako.',
        'last_admin_required' => 'Lazima ibaki angalau akaunti moja ya Msimamizi inayotumika.',
        'username' => 'Jina la mtumiaji',
        'password' => 'Neno la siri',
        'login' => 'Ingia',
        'logout' => 'Toka',
        'language' => 'Lugha',
        'english' => 'Kiingereza',
        'swahili' => 'Kiswahili',
        'apply' => 'Tumia',
        'dashboard' => 'Dashibodi',
        'products' => 'Bidhaa',
        'customers' => 'Wateja',
        'suppliers' => 'Watoa Huduma',
        'purchases' => 'Agizo la Mauzo',
        'invoices' => 'Ankara',
        'revenue' => 'Mapato',
        'low_stock' => 'Hisa za Chini',
        'total_products' => 'Jumla ya Bidhaa',
        'total_customers' => 'Jumla ya Wateja',
        'total_suppliers' => 'Jumla ya Watoa Huduma',
        'total_invoices' => 'Ankara',
        'inventory_value' => 'Thamani ya Hifadhi',
        'add_product' => 'Ongeza Bidhaa',
        'product_name' => 'Jina la bidhaa',
        'category' => 'Aina',
        'quantity' => 'Idadi',
        'buy_price' => 'Bei ya kununua',
        'sell_price' => 'Bei ya kuuza',
        'location' => 'Mahali',
        'save_product' => 'Hifadhi Bidhaa',
        'add_customer' => 'Ongeza Mteja',
        'customer_name' => 'Jina la mteja',
        'phone' => 'Simu',
        'email' => 'Barua pepe',
        'address' => 'Anwani',
        'save_customer' => 'Hifadhi Mteja',
        'add_supplier' => 'Ongeza Mtoa Huduma',
        'supplier_name' => 'Jina la mtoa huduma',
        'save_supplier' => 'Hifadhi Mtoa Huduma',
        'create_purchase' => 'Tengeneza Agizo la Mauzo',
        'purchase_item' => 'Bidhaa ya mauzo',
        'purchase_category' => 'Aina ya mauzo',
        'purchase_quantity' => 'Idadi ya mauzo',
        'purchase_amount' => 'Kiasi cha mauzo (TZS)',
        'save_purchase' => 'Hifadhi Agizo la Mauzo',
        'apply_adjustment' => 'Tumia Marekebisho',
        'sale_category' => 'Aina ya mauzo',
        'discount_percent' => 'Punguzo (%)',
        'document_queue' => 'Foleni ya Nyaraka',
        'pending' => 'Inasubiri',
        'paid' => 'Imelipwa',
        'sold' => 'Iliuzwa',
        'print' => 'Chapisha',
        'create_invoice' => 'Tengeneza Ankarah',
        'invoice_number' => 'Namba ya ankara',
        'invoice_item' => 'Maelezo ya bidhaa',
        'invoice_total' => 'Jumla ya ankara',
        'invoice_notes' => 'Maelezo ya ankara',
        'save_invoice' => 'Hifadhi Ankarah',
        'inventory_list' => 'Orodha ya Hifadhi',
        'recent_orders' => 'Mauzo ya Hivi Punde',
        'recent_invoices' => 'Ankara za Hivi Punde',
        'customer' => 'Mteja',
        'item' => 'Kitu',
        'amount' => 'Kiasi',
        'status' => 'Hali',
        'notes' => 'Maelezo',
        'created' => 'Iliundwa',
        'product_added' => 'Bidhaa imehifadhiwa vizuri.',
        'payment_added' => 'Malipo yameandikwa kwa mafanikio.',
        'adjustment_applied' => 'Hali ya hisa imesasishwa kwa mafanikio.',
        'order_placed' => 'Agizo limerekodiwa kwa mafanikio.',
        'customer_added' => 'Mteja amehifadhiwa vizuri.',
        'supplier_added' => 'Mtoa huduma amehifadhiwa vizuri.',
        'purchase_added' => 'Agizo la mauzo limehifadhiwa vizuri.',
        'invoice_added' => 'Ankara imehifadhiwa vizuri.',
        'print_invoice' => 'Chapisha Ankarah',
        'no_records' => 'Hakuna rekodi bado.'
    ]
];

$language = $_SESSION['lang'] ?? ($_GET['lang'] ?? 'en');
if (isset($_POST['lang']) && $csrf_ok) {
    $language = $_POST['lang'];
    $_SESSION['lang'] = $language;
}
if (!isset($translations[$language])) {
    $language = 'en';
}
$_SESSION['lang'] = $language;

$labels = $translations[$language];
$loginRoles = [
    'admin' => ['label' => $labels['role_admin'], 'title' => $labels['login_admin_title'], 'description' => $labels['login_admin_description']],
    'staff' => ['label' => $labels['role_staff'], 'title' => $labels['login_staff_title'], 'description' => $labels['login_staff_description']],
    'manager' => ['label' => $labels['role_manager'], 'title' => $labels['login_manager_title'], 'description' => $labels['login_manager_description']],
];

require_once __DIR__ . '/db.php';
$pdo = mitas_db();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// Improve concurrency for SQLite: use WAL and set busy timeout
try {
    $pdo->exec("PRAGMA journal_mode = WAL;");
    $pdo->exec("PRAGMA synchronous = NORMAL;");
    $pdo->exec("PRAGMA busy_timeout = 5000;");
    $pdo->setAttribute(PDO::ATTR_TIMEOUT, 5);
} catch (Exception $e) {
    // ignore if driver doesn't support pragmas
}

// Audit helper
function audit_log(PDO $pdo, $action, $username = null, $details = null) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS audit_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            action TEXT NOT NULL,
            username TEXT,
            ip TEXT,
            details TEXT,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );");
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $stmt = $pdo->prepare('INSERT INTO audit_logs (action, username, ip, details) VALUES (?, ?, ?, ?)');
        $stmt->execute([$action, $username, $ip, $details]);
    } catch (Exception $e) {}
}

// Login attempts table
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT,
        ip TEXT,
        successful INTEGER DEFAULT 0,
        attempt_time TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );");
} catch (Exception $e) {}

function ensureColumn(PDO $pdo, string $table, string $column, string $definition): void {
    $stmt = $pdo->query("PRAGMA table_info($table)");
    $columns = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    foreach ($columns as $col) {
        if (($col['name'] ?? '') === $column) {
            return;
        }
    }
    $pdo->exec("ALTER TABLE $table ADD COLUMN $column $definition");
}

function calculateDefaultSellPrice(float $buyPrice): float {
    return max(0.0, round($buyPrice * 1.2, 0));
}

function calculateTotalAmount(float $unitPrice, int $quantity, float $discountPercent = 0.0): float {
    $quantity = max(0, $quantity);
    $unitPrice = max(0.0, $unitPrice);
    $discountPercent = max(0.0, min(100.0, $discountPercent));
    return round($unitPrice * $quantity * (1 - ($discountPercent / 100)), 0);
}

function findProduct(PDO $pdo, string $name): ?array {
    $stmt = $pdo->prepare('SELECT id, name, category, quantity, buy_price, sell_price FROM products WHERE LOWER(name) = LOWER(?) LIMIT 1');
    $stmt->execute([$name]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function generateInvoiceNumber(PDO $pdo): string {
    $prefix = 'INV' . date('Ymd');
    $check = $pdo->prepare('SELECT COUNT(*) FROM invoices WHERE invoice_number = ?');
    for ($i = 0; $i < 10; $i++) {
        $candidate = $prefix . '-' . str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $check->execute([$candidate]);
        if ((int)$check->fetchColumn() === 0) return $candidate;
    }
    // fallback to unique id
    return $prefix . '-' . uniqid();
}

function getInvoiceBalance(PDO $pdo, int $invoiceId): float {
    $stmt = $pdo->prepare('SELECT COALESCE(final_total, total_amount) AS due, COALESCE((SELECT SUM(amount) FROM payments p WHERE p.invoice_id = invoices.id), 0) AS paid FROM invoices WHERE id = ?');
    $stmt->execute([$invoiceId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return 0.0;
    }
    return max(0.0, (float)$row['due'] - (float)$row['paid']);
}

function getOrderBalance(PDO $pdo, int $orderId): float {
    $stmt = $pdo->prepare('SELECT COALESCE(final_total, amount) AS due, COALESCE((SELECT SUM(amount) FROM payments p WHERE p.order_id = orders.id), 0) AS paid FROM orders WHERE id = ?');
    $stmt->execute([$orderId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return 0.0;
    }
    return max(0.0, (float)$row['due'] - (float)$row['paid']);
}

function calculateOrderProfitFromPayments(PDO $pdo, int $orderId): float {
    $saleStmt = $pdo->prepare('SELECT COALESCE(amount,0) AS sale_amount FROM orders WHERE id = ?');
    $saleStmt->execute([$orderId]);
    $saleAmount = (float)$saleStmt->fetchColumn();
    $costStmt = $pdo->prepare('SELECT COALESCE(SUM(buy_price * quantity),0) FROM order_items WHERE order_id = ?');
    $costStmt->execute([$orderId]);
    $orderCost = (float)$costStmt->fetchColumn();

    return $saleAmount - $orderCost;
}

$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'admin',
    is_active INTEGER NOT NULL DEFAULT 1
);");
mitas_ensure_user_schema($pdo);
$pdo->exec("CREATE TABLE IF NOT EXISTS customers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    phone TEXT,
    email TEXT,
    address TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);");
$pdo->exec("CREATE TABLE IF NOT EXISTS suppliers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    phone TEXT,
    email TEXT,
    address TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);");
$pdo->exec("CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    category TEXT NOT NULL,
    quantity INTEGER NOT NULL DEFAULT 0,
    buy_price REAL NOT NULL DEFAULT 0,
    sell_price REAL NOT NULL DEFAULT 0,
    location TEXT NOT NULL DEFAULT 'Main Warehouse',
    manufacturer TEXT,
    grade TEXT,
    condition TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);");
$pdo->exec("CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer TEXT NOT NULL,
    item TEXT NOT NULL,
    category TEXT NOT NULL DEFAULT '',
    quantity INTEGER NOT NULL DEFAULT 0,
    amount REAL NOT NULL DEFAULT 0,
    vat_amount REAL NOT NULL DEFAULT 0,
    final_total REAL NOT NULL DEFAULT 0,
    discount_percent REAL NOT NULL DEFAULT 0,
    status TEXT NOT NULL DEFAULT 'Pending',
    manufacturer TEXT,
    grade TEXT,
    condition TEXT,
    product_id INTEGER,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);");
$pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL,
    item TEXT NOT NULL,
    category TEXT NOT NULL DEFAULT '',
    quantity INTEGER NOT NULL DEFAULT 0,
    unit_price REAL NOT NULL DEFAULT 0,
    discount_percent REAL NOT NULL DEFAULT 0,
    vat_percent REAL NOT NULL DEFAULT 0,
    vat_amount REAL NOT NULL DEFAULT 0,
    amount REAL NOT NULL DEFAULT 0,
    product_id INTEGER,
    manufacturer TEXT,
    grade TEXT,
    condition TEXT,
    buy_price REAL NOT NULL DEFAULT 0,
    profit REAL NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);");
$pdo->exec("CREATE TABLE IF NOT EXISTS invoices (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    invoice_number TEXT NOT NULL UNIQUE,
    customer_name TEXT NOT NULL,
    item TEXT NOT NULL,
    total_amount REAL NOT NULL DEFAULT 0,
    vat_amount REAL NOT NULL DEFAULT 0,
    final_total REAL NOT NULL DEFAULT 0,
    notes TEXT,
    status TEXT NOT NULL DEFAULT 'Pending',
    product_id INTEGER,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);");
$pdo->exec("CREATE TABLE IF NOT EXISTS invoice_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    invoice_id INTEGER NOT NULL,
    item TEXT NOT NULL,
    category TEXT NOT NULL DEFAULT '',
    quantity INTEGER NOT NULL DEFAULT 1,
    unit_price REAL NOT NULL DEFAULT 0,
    discount_percent REAL NOT NULL DEFAULT 0,
    vat_percent REAL NOT NULL DEFAULT 0,
    vat_amount REAL NOT NULL DEFAULT 0,
    amount REAL NOT NULL DEFAULT 0,
    product_id INTEGER,
    manufacturer TEXT,
    grade TEXT,
    condition TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);");
$pdo->exec("CREATE TABLE IF NOT EXISTS purchases (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    supplier_name TEXT NOT NULL,
    item TEXT NOT NULL,
    category TEXT NOT NULL DEFAULT '',
    quantity INTEGER NOT NULL DEFAULT 0,
    amount REAL NOT NULL DEFAULT 0,
    product_id INTEGER,
    status TEXT NOT NULL DEFAULT 'Pending',
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);");
$pdo->exec("CREATE TABLE IF NOT EXISTS payments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    invoice_id INTEGER,
    order_id INTEGER,
    purchase_id INTEGER,
    document_type TEXT NOT NULL DEFAULT 'invoice',
    customer_name TEXT,
    amount REAL NOT NULL DEFAULT 0,
    vat_amount REAL NOT NULL DEFAULT 0,
    final_amount REAL NOT NULL DEFAULT 0,
    method TEXT DEFAULT 'Cash',
    method_detail TEXT DEFAULT '',
    reference_number TEXT DEFAULT '',
    sender_name TEXT DEFAULT '',
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);");
$pdo->exec("CREATE TABLE IF NOT EXISTS stock_adjustments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER,
    reason TEXT,
    quantity_change INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);");

ensureColumn($pdo, 'orders', 'vat_amount', 'REAL NOT NULL DEFAULT 0');
ensureColumn($pdo, 'orders', 'final_total', 'REAL NOT NULL DEFAULT 0');
ensureColumn($pdo, 'orders', 'profit', 'REAL NOT NULL DEFAULT 0');
ensureColumn($pdo, 'orders', 'outstanding', 'REAL NOT NULL DEFAULT 0');
ensureColumn($pdo, 'orders', 'settled', 'INTEGER NOT NULL DEFAULT 0');
ensureColumn($pdo, 'order_items', 'vat_percent', 'REAL NOT NULL DEFAULT 0');
ensureColumn($pdo, 'order_items', 'vat_amount', 'REAL NOT NULL DEFAULT 0');
ensureColumn($pdo, 'order_items', 'buy_price', 'REAL NOT NULL DEFAULT 0');
ensureColumn($pdo, 'order_items', 'profit', 'REAL NOT NULL DEFAULT 0');
ensureColumn($pdo, 'invoices', 'vat_amount', 'REAL NOT NULL DEFAULT 0');
ensureColumn($pdo, 'invoices', 'final_total', 'REAL NOT NULL DEFAULT 0');
ensureColumn($pdo, 'invoices', 'outstanding', 'REAL NOT NULL DEFAULT 0');
ensureColumn($pdo, 'invoices', 'settled', 'INTEGER NOT NULL DEFAULT 0');
ensureColumn($pdo, 'purchases', 'status', 'TEXT NOT NULL DEFAULT "Pending"');
ensureColumn($pdo, 'payments', 'purchase_id', 'INTEGER');
ensureColumn($pdo, 'payments', 'method_detail', 'TEXT DEFAULT ""');
ensureColumn($pdo, 'payments', 'reference_number', 'TEXT DEFAULT ""');
ensureColumn($pdo, 'payments', 'sender_name', 'TEXT DEFAULT ""');
// ensure invoice number column exists for auto-generation and lookups
ensureColumn($pdo, 'invoices', 'invoice_number', 'TEXT');

$pdo->exec("CREATE TABLE IF NOT EXISTS layouts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    name TEXT,
    json TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);");
$pdo->exec("CREATE TABLE IF NOT EXISTS company_profile (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_name TEXT NOT NULL DEFAULT 'Mitas Business Solutions',
    tagline TEXT,
    address TEXT,
    pobox TEXT,
    phone TEXT,
    email TEXT,
    website TEXT,
    logo_path TEXT,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);");

$userCount = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
if ($userCount === 0) {
    $initialAdminPassword = (string)getenv('MITAS_INITIAL_ADMIN_PASSWORD');
    if (!mitas_password_is_strong($initialAdminPassword)) {
        mitas_render_setup_error('Initial Admin setup required', 'Set MITAS_INITIAL_ADMIN_PASSWORD in Vercel to a unique password with at least 12 characters, uppercase, lowercase, a number, and a symbol, then redeploy.');
    }
    $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, role, is_active, session_version, must_change_password) VALUES (?, ?, ?, 1, 0, 1)');
    $stmt->execute(['admin', password_hash($initialAdminPassword, PASSWORD_DEFAULT), 'admin']);
}

$companyProfile = $pdo->query('SELECT * FROM company_profile ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
if (!$companyProfile) {
    $pdo->exec("INSERT INTO company_profile (company_name, tagline, address, pobox, phone, email, website, logo_path) VALUES ('Mitas Business Solutions','Reliable business and inventory services','Dar es Salaam, Tanzania','P.O. Box 12345','+255 712 345 678','info@mitas.co.tz','www.mitas.co.tz','logo.svg')");
    $companyProfile = $pdo->query('SELECT * FROM company_profile ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
}

$currentUser = mitas_current_user($pdo);
$loggedIn = $currentUser !== null;
if ($loggedIn && !empty($_SESSION['must_change_password']) && !isset($_POST['logout'])) {
    header('Location: change_password.php?required=1');
    exit;
}
if (isset($_POST['login_submit']) && $csrf_ok) {
    $username = trim($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    // rate-limit: count failed attempts in last 15 minutes
    $stmtAttempts = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE username = ? AND successful = 0 AND attempt_time > datetime('now','-15 minutes')");
    $stmtAttempts->execute([$username]);
    $failedCount = (int)$stmtAttempts->fetchColumn();
    if ($failedCount >= 5) {
        $_SESSION['flash'] = 'Too many failed login attempts. Try again later.';
        audit_log($pdo, 'login_blocked', $username, 'Too many attempts from ' . ($ip ?? 'unknown'));
    } else {
        $stmt = $pdo->prepare('SELECT id, password_hash, LOWER(TRIM(role)) AS role, session_version, must_change_password FROM users WHERE username = ? AND is_active = 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && !array_key_exists($user['role'], $loginRoles)) {
            $user = false;
        }
        $success = 0;
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $csrf_token = $_SESSION['csrf_token'];
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['username'] = $username;
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_session_version'] = (int)$user['session_version'];
            $_SESSION['must_change_password'] = (int)$user['must_change_password'];
            $loggedIn = true;
            $success = 1;
            audit_log($pdo, 'login_success', $username, 'Logged in from ' . ($ip ?? 'unknown'));
            if (!empty($user['must_change_password'])) {
                header('Location: change_password.php?required=1');
                exit;
            }
        } else {
            $_SESSION['flash'] = 'Invalid username or password.';
            audit_log($pdo, 'login_failed', $username, 'Failed login from ' . ($ip ?? 'unknown'));
        }
        // record attempt
        try {
            $ins = $pdo->prepare('INSERT INTO login_attempts (username, ip, successful) VALUES (?, ?, ?)');
            $ins->execute([$username, $ip, $success]);
        } catch (Exception $e) {}
    }
}

if (isset($_POST['logout']) && $csrf_ok) {
    session_destroy();
    header('Location: index.php');
    exit;
}

if ($loggedIn) {
        $roleActionPermissions = [
            'trigger_backup' => 'backups.manage',
            'company_name' => 'company.manage',
            'product_name' => 'inventory.manage',
            'customer_name' => 'customers.manage',
            'supplier_name' => 'suppliers.manage',
            'purchase_item' => 'purchases.manage',
            'payment_amount' => 'payments.manage',
            'mark_settled' => 'payments.manage',
            'adjust_product_id' => 'inventory.manage',
            'invoice_number' => 'invoices.manage',
            'sale_customer' => 'sales.manage',
            'create_user' => 'users.manage',
            'update_user' => 'users.manage'
        ];
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $csrf_ok) {
            foreach ($roleActionPermissions as $field => $permission) {
                if (isset($_POST[$field]) && !mitas_can($permission)) {
                    http_response_code(403);
                    exit('Forbidden');
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user']) && $csrf_ok) {
            $newUsername = trim((string)($_POST['new_username'] ?? ''));
            $newPassword = (string)($_POST['new_password'] ?? '');
            $newRole = strtolower(trim((string)($_POST['new_role'] ?? '')));
            if ($newUsername === '' || strlen($newUsername) > 64 || !array_key_exists($newRole, $loginRoles)) {
                $_SESSION['flash'] = $labels['account_invalid'];
            } elseif (!mitas_password_is_strong($newPassword)) {
                $_SESSION['flash'] = $labels['password_too_short'];
            } else {
                try {
                    $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, role, is_active, must_change_password) VALUES (?, ?, ?, 1, 1)');
                    $stmt->execute([$newUsername, password_hash($newPassword, PASSWORD_DEFAULT), $newRole]);
                    audit_log($pdo, 'user_created', $_SESSION['username'] ?? null, $newUsername . ' (' . $newRole . ')');
                    $_SESSION['flash'] = $labels['account_created'];
                } catch (PDOException $e) {
                    $_SESSION['flash'] = $labels['account_exists'];
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user']) && $csrf_ok) {
            $targetId = (int)($_POST['user_id'] ?? 0);
            $targetRole = strtolower(trim((string)($_POST['account_role'] ?? '')));
            $targetActive = (($_POST['account_active'] ?? '0') === '1') ? 1 : 0;
            $resetPassword = (string)($_POST['reset_password'] ?? '');
            $stmt = $pdo->prepare('SELECT id, username, LOWER(TRIM(role)) AS role, is_active, session_version FROM users WHERE id = ?');
            $stmt->execute([$targetId]);
            $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$targetUser || !array_key_exists($targetRole, $loginRoles)) {
                $_SESSION['flash'] = $labels['account_invalid'];
            } elseif ($resetPassword !== '' && !mitas_password_is_strong($resetPassword)) {
                $_SESSION['flash'] = $labels['password_too_short'];
            } elseif ($targetId === (int)$_SESSION['user_id'] && ($targetRole !== 'admin' || $targetActive !== 1)) {
                $_SESSION['flash'] = $labels['account_self_lockout'];
            } else {
                $removesAdmin = $targetUser['role'] === 'admin'
                    && (int)$targetUser['is_active'] === 1
                    && ($targetRole !== 'admin' || $targetActive !== 1);
                $activeAdminCount = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE LOWER(TRIM(role)) = 'admin' AND is_active = 1")->fetchColumn();
                if ($removesAdmin && $activeAdminCount <= 1) {
                    $_SESSION['flash'] = $labels['last_admin_required'];
                } else {
                    $stmt = $pdo->prepare('UPDATE users SET role = ?, is_active = ? WHERE id = ?');
                    $stmt->execute([$targetRole, $targetActive, $targetId]);

                    if ($resetPassword !== '') {
                        $stmt = $pdo->prepare('UPDATE users SET password_hash = ?, session_version = session_version + 1, must_change_password = 1 WHERE id = ?');
                        $stmt->execute([password_hash($resetPassword, PASSWORD_DEFAULT), $targetId]);
                        if ($targetId === (int)$_SESSION['user_id']) {
                            $_SESSION['user_session_version'] = (int)$targetUser['session_version'] + 1;
                            $_SESSION['must_change_password'] = 1;
                        }
                    }

                    audit_log($pdo, 'user_updated', $_SESSION['username'] ?? null, $targetUser['username'] . ' (' . $targetRole . ')');
                    $_SESSION['flash'] = $labels['account_updated'];
                }
            }
        }

        // backup trigger
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['trigger_backup']) && $csrf_ok) {
            // call backup endpoint internally
            $ch = curl_init((isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . dirname($_SERVER['REQUEST_URI']) . '/backup.php');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, ['csrf_token' => $csrf_token]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $res = @curl_exec($ch);
            curl_close($ch);
            $_SESSION['flash'] = 'Backup triggered.';
            header('Location: index.php'); exit;
        }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['company_name']) && $csrf_ok) {
        $companyName = trim($_POST['company_name'] ?? '');
        $tagline = trim($_POST['tagline'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $pobox = trim($_POST['pobox'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $website = trim($_POST['website'] ?? '');
        $logoPath = trim($_POST['logo_path'] ?? 'logo.svg');
        if ($companyName !== '') {
            $stmt = $pdo->prepare('INSERT INTO company_profile (company_name, tagline, address, pobox, phone, email, website, logo_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$companyName, $tagline, $address, $pobox, $phone, $email, $website, $logoPath]);
            $companyProfile = $pdo->query('SELECT * FROM company_profile ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
            $_SESSION['flash'] = 'Company profile updated.';
            header('Location: index.php');
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_name']) && $csrf_ok) {
        $name = trim($_POST['product_name']);
        $category = trim($_POST['category']);
        $quantity = max(0, (int)$_POST['quantity']);
        $buyPrice = max(0, (float)$_POST['buy_price']);
        $sellPrice = max(0, (float)$_POST['sell_price']);
        $location = trim($_POST['location']) ?: 'Main Warehouse';
        $manufacturer = trim($_POST['manufacturer'] ?? '');
        $grade = trim($_POST['grade'] ?? '');
        $condition = trim($_POST['condition'] ?? '');

        if ($name !== '') {
            $stmt = $pdo->prepare('INSERT INTO products (name, category, quantity, buy_price, sell_price, location, manufacturer, grade, condition) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$name, $category, $quantity, $buyPrice, $sellPrice, $location, $manufacturer, $grade, $condition]);
            $_SESSION['flash'] = $labels['product_added'];
            header('Location: index.php');
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['customer_name']) && $csrf_ok) {
        $customerName = trim($_POST['customer_name']);
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($customerName !== '') {
            $stmt = $pdo->prepare('INSERT INTO customers (name, phone, email, address) VALUES (?, ?, ?, ?)');
            $stmt->execute([$customerName, $phone, $email, $address]);
            $_SESSION['flash'] = $labels['customer_added'];
            header('Location: index.php');
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supplier_name']) && $csrf_ok) {
        $supplierName = trim($_POST['supplier_name']);
        $phone = trim($_POST['supplier_phone'] ?? '');
        $email = trim($_POST['supplier_email'] ?? '');
        $address = trim($_POST['supplier_address'] ?? '');

        if ($supplierName !== '') {
            $stmt = $pdo->prepare('INSERT INTO suppliers (name, phone, email, address) VALUES (?, ?, ?, ?)');
            $stmt->execute([$supplierName, $phone, $email, $address]);
            $_SESSION['flash'] = $labels['supplier_added'];
            header('Location: index.php');
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purchase_item']) && $csrf_ok) {
        $supplierName = trim($_POST['purchase_supplier']);
        $item = trim($_POST['purchase_item']);
        $category = trim($_POST['purchase_category'] ?? '');
        $quantity = max(0, (int)$_POST['purchase_quantity']);
        $unitCost = max(0, (float)$_POST['purchase_amount']);
        $totalAmount = calculateTotalAmount($unitCost, $quantity);

        if ($supplierName !== '' && $item !== '' && $quantity > 0) {
            $product = findProduct($pdo, $item);
            $productId = null;
            if ($product) {
                $productId = (int)$product['id'];
                $newCategory = $category !== '' ? $category : ($product['category'] ?? '');
                $newBuyPrice = $unitCost > 0 ? $unitCost : (float)($product['buy_price'] ?? 0);
                $newSellPrice = ((float)($product['sell_price'] ?? 0) > 0) ? (float)$product['sell_price'] : calculateDefaultSellPrice($newBuyPrice);
                $newQuantity = (int)($product['quantity'] ?? 0) + $quantity;
                $stmt2 = $pdo->prepare('UPDATE products SET category = ?, quantity = ?, buy_price = ?, sell_price = ? WHERE id = ?');
                $stmt2->execute([$newCategory, $newQuantity, $newBuyPrice, $newSellPrice, $productId]);
            } else {
                $defaultSell = calculateDefaultSellPrice($unitCost);
                $stmt2 = $pdo->prepare('INSERT INTO products (name, category, quantity, buy_price, sell_price, location) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt2->execute([$item, $category !== '' ? $category : 'General', $quantity, $unitCost, $defaultSell, 'Main Warehouse']);
                $productId = (int)$pdo->lastInsertId();
            }

            $stmt = $pdo->prepare('INSERT INTO purchases (supplier_name, item, category, quantity, amount, product_id) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$supplierName, $item, $category, $quantity, $totalAmount, $productId]);

            $_SESSION['flash'] = $labels['purchase_added'];
            header('Location: index.php');
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_amount']) && $csrf_ok) {
        $invoiceId = (int)($_POST['payment_invoice_id'] ?? 0);
        $orderId = (int)($_POST['payment_order_id'] ?? 0);
        $documentType = trim($_POST['payment_document_type'] ?? 'invoice');
        $customerName = trim($_POST['payment_customer'] ?? '');
        $amount = max(0, (float)$_POST['payment_amount']);
        $applyVat = isset($_POST['payment_apply_vat']) && $_POST['payment_apply_vat'] == 1;
        $vatAmount = $applyVat ? ($amount * 0.18) : 0; // 18% VAT
        $finalAmount = $amount + $vatAmount;
        $method = trim($_POST['payment_method'] ?? 'Cash');
        $methodDetail = trim($_POST['payment_method_detail'] ?? '');
        $referenceNumber = trim($_POST['payment_reference'] ?? '');
        $senderName = trim($_POST['payment_sender_name'] ?? '');

        if ($invoiceId > 0 && empty($customerName)) {
            $inv = $pdo->prepare('SELECT customer_name FROM invoices WHERE id = ?');
            $inv->execute([$invoiceId]);
            $row = $inv->fetch(PDO::FETCH_ASSOC);
            if ($row) $customerName = $row['customer_name'];
        }
        if ($orderId > 0 && empty($customerName)) {
            $ord = $pdo->prepare('SELECT customer FROM orders WHERE id = ?');
            $ord->execute([$orderId]);
            $row = $ord->fetch(PDO::FETCH_ASSOC);
            if ($row) $customerName = $row['customer'];
        }

        if ($amount > 0) {
            if ($documentType === 'invoice' && $invoiceId > 0) {
                $invoiceDue = getInvoiceBalance($pdo, $invoiceId);
                $status = $amount >= $invoiceDue ? 'Paid' : 'Partially Paid';
                $pdo->prepare('UPDATE invoices SET status = ? WHERE id = ?')->execute([$status, $invoiceId]);
            } elseif ($documentType === 'order' && $orderId > 0) {
                $orderDue = getOrderBalance($pdo, $orderId);
                $status = $amount >= $orderDue ? 'Paid' : 'Partially Paid';
                $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, $orderId]);
            }

            $stmt = $pdo->prepare('INSERT INTO payments (invoice_id, order_id, document_type, customer_name, amount, vat_amount, final_amount, method, method_detail, reference_number, sender_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$invoiceId ?: null, $orderId ?: null, $documentType, $customerName, $amount, $vatAmount, $finalAmount, $method, $methodDetail, $referenceNumber, $senderName]);

            // Recalculate and persist outstanding + settled flags for related document
            // Outstanding is deducted: new_outstanding = final_total - payments_sum
            if ($invoiceId > 0) {
                $newDue = getInvoiceBalance($pdo, $invoiceId);
                $settledFlag = $newDue <= 0 ? 1 : 0;
                $status = $settledFlag ? 'Paid' : 'Partially Paid';
                // Deduct from outstanding by storing the new balance amount
                $upd = $pdo->prepare('UPDATE invoices SET outstanding = ?, settled = ?, status = ? WHERE id = ?');
                $upd->execute([$newDue, $settledFlag, $status, $invoiceId]);
            }
            if ($orderId > 0) {
                $newDue = getOrderBalance($pdo, $orderId);
                $settledFlag = $newDue <= 0 ? 1 : 0;
                $status = $settledFlag ? 'Paid' : 'Partially Paid';
                // Deduct from outstanding by storing the new balance amount
                if ($settledFlag) {
                    $orderProfit = calculateOrderProfitFromPayments($pdo, $orderId);
                    $upd2 = $pdo->prepare('UPDATE orders SET outstanding = ?, settled = ?, profit = ?, status = ? WHERE id = ?');
                    $upd2->execute([$newDue, $settledFlag, $orderProfit, $status, $orderId]);
                } else {
                    $upd2 = $pdo->prepare('UPDATE orders SET outstanding = ?, settled = ?, status = ? WHERE id = ?');
                    $upd2->execute([$newDue, $settledFlag, $status, $orderId]);
                }
            }

            $_SESSION['flash'] = $labels['payment_added'];
            header('Location: index.php');
            exit;
        }
    }

    // Manually mark a document as settled (Balance Profile checkbox)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_settled']) && $csrf_ok) {
        $docType = trim($_POST['mark_settled_type'] ?? '');
        $docId = (int)($_POST['mark_settled_id'] ?? 0);
        $confirm = isset($_POST['mark_settled_confirm']) && $_POST['mark_settled_confirm'] == 1;
        if ($docId > 0 && $confirm) {
            if ($docType === 'invoice') {
                $pdo->prepare('UPDATE invoices SET outstanding = 0, settled = 1, status = ? WHERE id = ?')->execute(['Paid', $docId]);
            } elseif ($docType === 'order') {
                $orderProfit = calculateOrderProfitFromPayments($pdo, $docId);
                $pdo->prepare('UPDATE orders SET outstanding = 0, settled = 1, status = ?, profit = ? WHERE id = ?')->execute(['Paid', $orderProfit, $docId]);
            }
            $_SESSION['flash'] = 'Marked as settled.';
        } else {
            $_SESSION['flash'] = 'Please confirm before marking settled.';
        }
        header('Location: index.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adjust_product_id']) && $csrf_ok) {
        $productId = (int)($_POST['adjust_product_id'] ?? 0);
        $reason = trim($_POST['adjust_reason'] ?? '');
        $change = (int)($_POST['adjust_change'] ?? 0);
        $buyPrice = max(0, (float)($_POST['adjust_buy_price'] ?? 0));
        $sellPrice = max(0, (float)($_POST['adjust_sell_price'] ?? 0));
        $category = trim($_POST['adjust_category'] ?? '');
        $manufacturer = trim($_POST['adjust_manufacturer'] ?? '');
        $grade = trim($_POST['adjust_grade'] ?? '');
        $condition = trim($_POST['adjust_condition'] ?? '');

        if ($productId > 0 && $change !== 0) {
            $stmt = $pdo->prepare('INSERT INTO stock_adjustments (product_id, reason, quantity_change) VALUES (?, ?, ?)');
            $stmt->execute([$productId, $reason, $change]);
            // update product quantity
            $stmt2 = $pdo->prepare('UPDATE products SET quantity = quantity + ?, buy_price = ?, sell_price = ?, category = ? WHERE id = ?');
            $stmt2->execute([$change, $buyPrice > 0 ? $buyPrice : null, $sellPrice > 0 ? $sellPrice : null, $category, $productId]);
            $_SESSION['flash'] = $labels['adjustment_applied'];
            header('Location: index.php');
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['invoice_number']) && $csrf_ok) {
        $invoiceNumber = trim($_POST['invoice_number']);
        $customerName = trim($_POST['invoice_customer']);
        $notes = trim($_POST['invoice_notes'] ?? '');
        $applyVat = isset($_POST['invoice_apply_vat']) && $_POST['invoice_apply_vat'] == 1;

        // support multiple invoice items
        $inv_items = $_POST['invoice_item'] ?? [];
        $inv_categories = $_POST['invoice_category'] ?? [];
        $inv_quantities = $_POST['invoice_quantity'] ?? [];
        $inv_unit_prices = $_POST['invoice_unit_price'] ?? [];
        $inv_discounts = $_POST['invoice_discount'] ?? [];
        $inv_vats = $_POST['invoice_vat'] ?? [];
        $inv_manufacturers = $_POST['invoice_manufacturer'] ?? [];
        $inv_grades = $_POST['invoice_grade'] ?? [];
        $inv_conditions = $_POST['invoice_condition'] ?? [];
        $invoiceOrderSource = (int)($_POST['invoice_order_source'] ?? 0);
        $invoiceDetectOrder = isset($_POST['invoice_detect_order']) && $_POST['invoice_detect_order'] == 1;

        $normalize = function($value) {
            return is_array($value) ? $value : [$value];
        };

        $inv_items = $normalize($inv_items);
        $inv_categories = $normalize($inv_categories);
        $inv_quantities = $normalize($inv_quantities);
        $inv_unit_prices = $normalize($inv_unit_prices);
        $inv_discounts = $normalize($inv_discounts);
        $inv_vats = $normalize($inv_vats);
        $inv_manufacturers = $normalize($inv_manufacturers);
        $inv_grades = $normalize($inv_grades);
        $inv_conditions = $normalize($inv_conditions);

        $hasManualInvoiceItems = false;
        foreach ($inv_items as $itemValue) {
            if (trim((string)$itemValue) !== '') {
                $hasManualInvoiceItems = true;
                break;
            }
        }

        if ($invoiceDetectOrder && $invoiceOrderSource > 0 && !$hasManualInvoiceItems) {
            $stmtOrder = $pdo->prepare('SELECT customer FROM orders WHERE id = ?');
            $stmtOrder->execute([$invoiceOrderSource]);
            $orderRow = $stmtOrder->fetch(PDO::FETCH_ASSOC);
            if ($orderRow) {
                if ($customerName === '') {
                    $customerName = trim($orderRow['customer']);
                }
                $stmtOrderItems = $pdo->prepare('SELECT id, order_id, item, category, quantity, unit_price, discount_percent, vat_percent, vat_amount, amount, product_id, manufacturer, grade, condition FROM order_items WHERE order_id = ? ORDER BY id ASC');
                $stmtOrderItems->execute([$invoiceOrderSource]);
                $orderItems = $stmtOrderItems->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($orderItems)) {
                    $inv_items = [];
                    $inv_categories = [];
                    $inv_quantities = [];
                    $inv_unit_prices = [];
                    $inv_discounts = [];
                    $inv_vats = [];
                    $inv_manufacturers = [];
                    $inv_grades = [];
                    $inv_conditions = [];
                    foreach ($orderItems as $row) {
                        $inv_items[] = $row['item'];
                        $inv_categories[] = $row['category'];
                        $inv_quantities[] = $row['quantity'];
                        $inv_unit_prices[] = $row['unit_price'];
                        $inv_discounts[] = $row['discount_percent'];
                        $inv_vats[] = $row['vat_percent'];
                        $inv_manufacturers[] = $row['manufacturer'];
                        $inv_grades[] = $row['grade'];
                        $inv_conditions[] = $row['condition'];
                    }
                }
            }
        }

        $invoiceLines = [];
        $invoiceTotal = 0;
        $invoiceVatTotal = 0;

        foreach ($inv_items as $i => $desc) {
            $desc = trim((string)$desc);
            if ($desc === '') continue;
            $qty = max(0, (int)($inv_quantities[$i] ?? 1));
            $unit = max(0, (float)($inv_unit_prices[$i] ?? 0));
            $discount = max(0, min(100, (float)($inv_discounts[$i] ?? 0)));
            // Only apply VAT if checkbox is checked
            $vat_percent = $applyVat ? max(0, min(100, (float)($inv_vats[$i] ?? 0))) : 0;
            $category = trim((string)($inv_categories[$i] ?? ''));
            $manufacturer = trim((string)($inv_manufacturers[$i] ?? ''));
            $grade = trim((string)($inv_grades[$i] ?? ''));
            $condition = trim((string)($inv_conditions[$i] ?? ''));
            if ($qty <= 0) continue;
            $subtotal = $unit * $qty * (1 - $discount / 100);
            $vat_amount = $applyVat ? ($subtotal * ($vat_percent / 100)) : 0;
            $amount = $subtotal + $vat_amount;
            $invoiceLines[] = [
                'description' => $desc,
                'category' => $category,
                'quantity' => $qty,
                'unit_price' => $unit,
                'discount_percent' => $discount,
                'vat_percent' => $vat_percent,
                'vat_amount' => $vat_amount,
                'amount' => $amount,
                'manufacturer' => $manufacturer,
                'grade' => $grade,
                'condition' => $condition,
            ];
            $invoiceTotal += $subtotal;
            $invoiceVatTotal += $vat_amount;
        }

        // if invoice number not supplied, auto-generate a unique one
        if ($invoiceNumber === '') {
            $invoiceNumber = generateInvoiceNumber($pdo);
        }

        if ($invoiceNumber !== '' && $customerName !== '' && !empty($invoiceLines) && $invoiceTotal > 0) {
            $finalTotal = $applyVat ? ($invoiceTotal + $invoiceVatTotal) : $invoiceTotal;
            $stmt = $pdo->prepare('INSERT INTO invoices (invoice_number, customer_name, item, total_amount, vat_amount, final_total, notes, status, product_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $summaryItem = count($invoiceLines) === 1 ? $invoiceLines[0]['description'] : 'Multiple items';
            $stmt->execute([$invoiceNumber, $customerName, $summaryItem, $invoiceTotal, $invoiceVatTotal, $finalTotal, $notes, 'Pending', null]);
            $newInvoiceId = (int)$pdo->lastInsertId();

            $stmtItem = $pdo->prepare('INSERT INTO invoice_items (invoice_id, item, category, quantity, unit_price, discount_percent, vat_percent, vat_amount, amount, product_id, manufacturer, grade, condition) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            foreach ($invoiceLines as $line) {
                $stmtItem->execute([
                    $newInvoiceId,
                    $line['description'],
                    $line['category'],
                    $line['quantity'],
                    $line['unit_price'],
                    $line['discount_percent'],
                    $line['vat_percent'],
                    $line['vat_amount'],
                    $line['amount'],
                    null,
                    $line['manufacturer'],
                    $line['grade'],
                    $line['condition'],
                ]);
            }

            $_SESSION['flash'] = $labels['invoice_added'];
            header('Location: print_invoice.php?invoice=' . $newInvoiceId);
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sale_customer']) && $csrf_ok) {
        $customer = trim($_POST['sale_customer']);
        $applyVat = isset($_POST['sale_apply_vat']) && $_POST['sale_apply_vat'] == 1;
        $items = $_POST['sale_item'] ?? [];
        $categories = $_POST['sale_category'] ?? [];
        $quantities = $_POST['sale_quantity'] ?? [];
        $unitPrices = $_POST['sale_amount'] ?? [];
        $discounts = $_POST['sale_discount'] ?? [];
        $vatPercents = $_POST['sale_vat'] ?? [];
        $manufacturers = $_POST['sale_manufacturer'] ?? [];
        $grades = $_POST['sale_grade'] ?? [];
        $conditions = $_POST['sale_condition'] ?? [];

        $normalize = function($value) {
            return is_array($value) ? $value : [$value];
        };

        $items = $normalize($items);
        $categories = $normalize($categories);
        $quantities = $normalize($quantities);
        $unitPrices = $normalize($unitPrices);
        $discounts = $normalize($discounts);
        $vatPercents = $normalize($vatPercents);
        $manufacturers = $normalize($manufacturers);
        $grades = $normalize($grades);
        $conditions = $normalize($conditions);

        $orderLines = [];
        $totalQuantity = 0;
        $totalAmount = 0;
        $totalVat = 0;

        foreach ($items as $index => $itemName) {
            $itemName = trim((string)$itemName);
            $qty = max(0, (int)($quantities[$index] ?? 0));
            $unitPrice = max(0, (float)($unitPrices[$index] ?? 0));
            $discountPercent = max(0, min(100, (float)($discounts[$index] ?? 0)));
            $category = trim((string)($categories[$index] ?? ''));
            $manufacturer = trim((string)($manufacturers[$index] ?? ''));
            $grade = trim((string)($grades[$index] ?? ''));
            $condition = trim((string)($conditions[$index] ?? ''));
            $vat_percent = $applyVat ? max(0, min(100, (float)($vatPercents[$index] ?? 18))) : 0;

            if ($itemName === '' || $qty <= 0) {
                continue;
            }

            $subtotal = calculateTotalAmount($unitPrice, $qty, $discountPercent);
            $vat_amount = $applyVat ? ($subtotal * ($vat_percent / 100)) : 0;
            $amount = $subtotal + $vat_amount;
            $orderLines[] = [
                'item_name' => $itemName,
                'category' => $category,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'discount_percent' => $discountPercent,
                'vat_percent' => $vat_percent,
                'vat_amount' => $vat_amount,
                'amount' => $amount,
                'manufacturer' => $manufacturer,
                'grade' => $grade,
                'condition' => $condition,
            ];
            $totalQuantity += $qty;
            $totalAmount += $subtotal;
            $totalVat += $vat_amount;
        }

        if ($customer !== '' && !empty($orderLines)) {
            $productLookups = [];
            foreach ($orderLines as $line) {
                $product = findProduct($pdo, $line['item_name']);
                if (!$product) {
                    $_SESSION['flash'] = 'Item "' . htmlspecialchars($line['item_name']) . '" is not available in stock.';
                    header('Location: index.php');
                    exit;
                }
                if ((int)$product['quantity'] < $line['quantity']) {
                    $_SESSION['flash'] = 'Requested quantity for "' . htmlspecialchars($line['item_name']) . '" exceeds available stock.';
                    header('Location: index.php');
                    exit;
                }
                $productLookups[] = $product;
            }

            foreach ($orderLines as $index => $line) {
                $product = $productLookups[$index];
                $newCategory = $line['category'] !== '' ? $line['category'] : ($product['category'] ?? '');
                $newQuantity = max(0, (int)$product['quantity'] - $line['quantity']);
                $stmt2 = $pdo->prepare('UPDATE products SET category = ?, quantity = ? WHERE id = ?');
                $stmt2->execute([$newCategory, $newQuantity, (int)$product['id']]);
                $orderLines[$index]['product_id'] = (int)$product['id'];
                // capture buy price to compute profit/loss
                $orderLines[$index]['buy_price'] = (float)($product['buy_price'] ?? 0);
                // profit per line = amount received for the line - (buy_price * quantity)
                $orderLines[$index]['profit'] = $orderLines[$index]['amount'] - ($orderLines[$index]['buy_price'] * $orderLines[$index]['quantity']);
            }
            // compute total profit for the order
            $orderProfit = 0;
            foreach ($orderLines as $line) {
                $orderProfit += (float)($line['profit'] ?? 0);
            }
            $orderVatTotal = $totalVat;
            $orderFinalTotal = $totalAmount + $orderVatTotal;

            $stmt = $pdo->prepare('INSERT INTO orders (customer, item, category, quantity, amount, vat_amount, final_total, profit, outstanding, status, product_id, manufacturer, grade, condition) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $summaryItem = count($orderLines) === 1 ? $orderLines[0]['item_name'] : 'Multiple IT items';
            $summaryCategory = count($orderLines) === 1 ? $orderLines[0]['category'] : 'Mixed';
            $summaryManufacturer = count($orderLines) === 1 ? $orderLines[0]['manufacturer'] : '';
            $summaryGrade = count($orderLines) === 1 ? $orderLines[0]['grade'] : '';
            $summaryCondition = count($orderLines) === 1 ? $orderLines[0]['condition'] : '';
            $stmt->execute([$customer, $summaryItem, $summaryCategory, $totalQuantity, $totalAmount, $orderVatTotal, $orderFinalTotal, 0, $orderFinalTotal, 'Sold', null, $summaryManufacturer, $summaryGrade, $summaryCondition]);
            $newOrderId = (int)$pdo->lastInsertId();

            $stmtItem = $pdo->prepare('INSERT INTO order_items (order_id, item, category, quantity, unit_price, discount_percent, vat_percent, vat_amount, amount, product_id, manufacturer, grade, condition, buy_price, profit) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            foreach ($orderLines as $line) {
                $stmtItem->execute([
                    $newOrderId,
                    $line['item_name'],
                    $line['category'],
                    $line['quantity'],
                    $line['unit_price'],
                    $line['discount_percent'],
                    $line['vat_percent'] ?? 0,
                    $line['vat_amount'] ?? 0,
                    $line['amount'],
                    $line['product_id'] ?? null,
                    $line['manufacturer'],
                    $line['grade'],
                    $line['condition'],
                    $line['buy_price'] ?? 0,
                    $line['profit'] ?? 0,
                ]);
            }

            $_SESSION['flash'] = $labels['order_placed'];
            header('Location: index.php');
            exit;
        }
    }
}

$products = $pdo->query('SELECT * FROM products ORDER BY created_at DESC LIMIT 8')->fetchAll(PDO::FETCH_ASSOC);
$customers = $pdo->query('SELECT * FROM customers ORDER BY created_at DESC LIMIT 8')->fetchAll(PDO::FETCH_ASSOC);
$documentQueue = [];
if (mitas_can('purchases.view')) {
foreach ($pdo->query('SELECT id, item, quantity, amount, created_at FROM purchases ORDER BY created_at DESC LIMIT 6')->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $documentQueue[] = [
        'type' => 'Purchase',
        'title' => htmlspecialchars($row['item']),
        'detail' => 'Qty ' . (int)$row['quantity'] . ' • TZS ' . number_format((float)$row['amount'], 0),
        'status' => 'Recorded',
        'created_at' => $row['created_at'],
        'print_url' => null,
    ];
}
}
foreach ($pdo->query('SELECT id, customer, item, quantity, amount, status, created_at FROM orders ORDER BY created_at DESC LIMIT 6')->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $documentQueue[] = [
        'type' => 'Order',
        'title' => 'Order #' . (int)$row['id'],
        'detail' => htmlspecialchars($row['customer']) . ' • ' . htmlspecialchars($row['item']) . ' • Qty ' . (int)$row['quantity'],
        'status' => $row['status'] ?? 'Pending',
        'created_at' => $row['created_at'],
        'print_url' => 'print_order.php?order=' . (int)$row['id'],
    ];
}
foreach ($pdo->query('SELECT id, invoice_number, customer_name, total_amount, status, created_at FROM invoices ORDER BY created_at DESC LIMIT 6')->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $documentQueue[] = [
        'type' => 'Invoice',
        'title' => 'Invoice #' . htmlspecialchars($row['invoice_number']),
        'detail' => htmlspecialchars($row['customer_name']) . ' • TZS ' . number_format((float)$row['total_amount'], 0),
        'status' => $row['status'] ?? 'Pending',
        'created_at' => $row['created_at'],
        'print_url' => 'print_invoice.php?invoice=' . (int)$row['id'],
    ];
}
usort($documentQueue, function ($a, $b) { return strcmp($b['created_at'], $a['created_at']); });
$documentQueue = array_slice($documentQueue, 0, 12);
$suppliers = $pdo->query('SELECT * FROM suppliers ORDER BY created_at DESC LIMIT 8')->fetchAll(PDO::FETCH_ASSOC);
$orders = $pdo->query('SELECT * FROM orders ORDER BY created_at DESC LIMIT 6')->fetchAll(PDO::FETCH_ASSOC);
$orderItemsByOrder = [];
$stmtOrderItems = $pdo->prepare('SELECT id, order_id, item, category, quantity, unit_price, discount_percent, vat_percent, vat_amount, amount, product_id, manufacturer, grade, condition FROM order_items WHERE order_id = ? ORDER BY id ASC');
foreach ($orders as $ord) {
    $stmtOrderItems->execute([(int)$ord['id']]);
    $orderItemsByOrder[(int)$ord['id']] = $stmtOrderItems->fetchAll(PDO::FETCH_ASSOC);
}
$invoices = $pdo->query('SELECT * FROM invoices ORDER BY created_at DESC LIMIT 6')->fetchAll(PDO::FETCH_ASSOC);
$purchases = $pdo->query('SELECT * FROM purchases ORDER BY created_at DESC LIMIT 6')->fetchAll(PDO::FETCH_ASSOC);
$payments = $pdo->query('SELECT * FROM payments ORDER BY created_at DESC LIMIT 6')->fetchAll(PDO::FETCH_ASSOC);
$adjustments = $pdo->query('SELECT * FROM stock_adjustments ORDER BY created_at DESC LIMIT 6')->fetchAll(PDO::FETCH_ASSOC);
$products_all = $pdo->query('SELECT id, name, quantity FROM products ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$totalItems = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$customerCount = (int)$pdo->query('SELECT COUNT(*) FROM customers')->fetchColumn();
$supplierCount = (int)$pdo->query('SELECT COUNT(*) FROM suppliers')->fetchColumn();
$invoiceCount = (int)$pdo->query('SELECT COUNT(*) FROM invoices')->fetchColumn();
$paymentCount = (int)$pdo->query('SELECT COUNT(*) FROM payments')->fetchColumn();
$adjustmentCount = (int)$pdo->query('SELECT COUNT(*) FROM stock_adjustments')->fetchColumn();
$lowStock = (int)$pdo->query('SELECT COUNT(*) FROM products WHERE quantity < 10')->fetchColumn();
$zeroStockCount = (int)$pdo->query('SELECT COUNT(*) FROM products WHERE quantity = 0')->fetchColumn();
$stockGraphData = $pdo->query('SELECT name, quantity FROM products ORDER BY quantity ASC LIMIT 8')->fetchAll(PDO::FETCH_ASSOC);
$totalValue = (float)$pdo->query('SELECT SUM(quantity * buy_price) FROM products')->fetchColumn();
$revenue = (float)$pdo->query('SELECT SUM(total_amount) FROM invoices')->fetchColumn();
$orderRevenue = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM orders WHERE status IN ('Sold','Paid')")->fetchColumn();
$purchaseSum = (float)$pdo->query('SELECT SUM(amount) FROM purchases')->fetchColumn();
$netProfit = $revenue + $orderRevenue - $purchaseSum;
// compute total outstanding across invoices and orders (only unsettled)
$totalOutstanding = 0.0;
$invoiceOutstanding = (float)$pdo->query("SELECT COALESCE(SUM(CASE WHEN COALESCE(final_total, total_amount) - COALESCE((SELECT SUM(amount) FROM payments p WHERE p.invoice_id = invoices.id), 0) > 0 THEN COALESCE(final_total, total_amount) - COALESCE((SELECT SUM(amount) FROM payments p WHERE p.invoice_id = invoices.id), 0) ELSE 0 END), 0) FROM invoices WHERE COALESCE(settled, 0) = 0")->fetchColumn();
$orderOutstanding = (float)$pdo->query("SELECT COALESCE(SUM(CASE WHEN COALESCE(final_total, amount) - COALESCE((SELECT SUM(amount) FROM payments p WHERE p.order_id = orders.id), 0) > 0 THEN COALESCE(final_total, amount) - COALESCE((SELECT SUM(amount) FROM payments p WHERE p.order_id = orders.id), 0) ELSE 0 END), 0) FROM orders WHERE COALESCE(settled, 0) = 0")->fetchColumn();
$totalOutstanding += $invoiceOutstanding + $orderOutstanding;
// compute total profit and loss only after orders are fully paid
$totalProfit = 0.0;
$totalLoss = 0.0;
$profitRows = $pdo->query("SELECT COALESCE(profit,0) AS profit FROM orders WHERE status = 'Paid'")->fetchAll(PDO::FETCH_ASSOC);
foreach ($profitRows as $r) {
    $p = (float)($r['profit'] ?? 0);
    if ($p >= 0) {
        $totalProfit += $p;
    } else {
        $totalLoss += abs($p);
    }
}
$liveActivity = [];
foreach ($pdo->query('SELECT id, customer_name, total_amount, created_at FROM invoices ORDER BY created_at DESC LIMIT 4')->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $liveActivity[] = [
        'type' => 'Invoice',
        'title' => 'Invoice #' . htmlspecialchars($row['id']),
        'detail' => htmlspecialchars($row['customer_name']) . ' • TZS ' . number_format((float)$row['total_amount'], 0),
        'time' => $row['created_at']
    ];
}
foreach ($pdo->query('SELECT id, customer, amount, created_at FROM orders ORDER BY created_at DESC LIMIT 4')->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $liveActivity[] = [
        'type' => 'Order',
        'title' => 'Order #' . htmlspecialchars($row['id']),
        'detail' => htmlspecialchars($row['customer']) . ' • TZS ' . number_format((float)$row['amount'], 0),
        'time' => $row['created_at']
    ];
}
foreach ($pdo->query('SELECT id, customer_name, amount, created_at FROM payments ORDER BY created_at DESC LIMIT 4')->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $liveActivity[] = [
        'type' => 'Payment',
        'title' => 'Payment #' . htmlspecialchars($row['id']),
        'detail' => htmlspecialchars($row['customer_name']) . ' • TZS ' . number_format((float)$row['amount'], 0),
        'time' => $row['created_at']
    ];
}
usort($liveActivity, function ($a, $b) { return strcmp($b['time'], $a['time']); });
$liveActivity = array_slice($liveActivity, 0, 6);
$managedUsers = [];
if ($loggedIn && mitas_can('users.manage')) {
    $managedUsers = $pdo->query('SELECT id, username, LOWER(TRIM(role)) AS role, is_active, session_version FROM users ORDER BY username')->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($language) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($labels['site_title']) ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="<?= $loggedIn ? 'app-page' : 'guest-page' ?>">
    <a class="skip-link" href="<?= $loggedIn ? '#app-main' : '#login-main' ?>"><?= htmlspecialchars($labels['skip_to_content']) ?></a>
    <?php if ($loggedIn): ?>
    <div class="top-tool">
        <div class="top-tool-left">
            <button class="add-layout-btn" id="addLayoutBtn">Add Layout</button>
        </div>
        <div class="top-tool-center">
            <strong><?= htmlspecialchars($labels['site_title']) ?></strong>
        </div>
        <div class="top-tool-right">
            <?php if (mitas_can('inventory.cost.view')): ?>
<div class="metric">
                <span class="metric-label"><?= htmlspecialchars($labels['inventory_value']) ?></span>
                <span class="metric-value" id="metric-inventory">TZS <?= number_format($totalValue, 0) ?></span>
            </div>
<?php endif; ?>
            <div class="metric">
                <span class="metric-label">Total Outstanding</span>
                <span class="metric-value negative" id="metric-outstanding">TZS <?= number_format($totalOutstanding, 0) ?></span>
            </div>
            <div class="metric">
                <span class="metric-label">Total Sell</span>
                <span class="metric-value positive" id="metric-total-sellings">TZS <?= number_format($revenue + $orderRevenue, 0) ?></span>
            </div>
            <?php if (mitas_can('reports.view')): ?>
<div class="metric">
                <span class="metric-label">Total Profit</span>
                <span class="metric-value positive" id="metric-total-profit">TZS <?= number_format($totalProfit, 0) ?></span>
            </div>
<?php endif; ?>
            <?php if (mitas_can('reports.view')): ?>
<div class="metric">
                <span class="metric-label">Total Loss</span>
                <span class="metric-value negative" id="metric-total-loss">TZS <?= number_format($totalLoss, 0) ?></span>
            </div>
<?php endif; ?>
        </div>
    </div>
    <header class="hero">
        <div>
            <p class="eyebrow">PHP Website System</p>
            <h1><?= htmlspecialchars($labels['site_title']) ?></h1>
            <p><?= htmlspecialchars($labels['subtitle']) ?></p>
        </div>
        <div class="hero-card live-overview" data-live-panel>
            <div class="hero-card-top">
                <h3><?= htmlspecialchars($labels['dashboard']) ?></h3>
                <button type="button" id="refreshLiveBtn" class="icon-btn">↻ Refresh</button>
            </div>
            <div class="hero-metrics">
                <div class="metric-card">
                    <span><?= htmlspecialchars($labels['total_products']) ?></span>
                    <strong id="metric-products"><?= $totalItems ?></strong>
                </div>
                <div class="metric-card">
                    <span><?= htmlspecialchars($labels['total_customers']) ?></span>
                    <strong id="metric-customers"><?= $customerCount ?></strong>
                </div>
                <div class="metric-card">
                    <span><?= htmlspecialchars($labels['low_stock']) ?></span>
                    <strong id="metric-low-stock"><?= $lowStock ?></strong>
                </div>
                <div class="metric-card">
                    <span><?= htmlspecialchars($labels['revenue']) ?></span>
                    <strong id="metric-revenue">TZS <?= number_format($revenue, 0) ?></strong>
                </div>
            </div>
            <?php if ($zeroStockCount > 0): ?>
            <div class="stock-alert warning"><span class="icon">⚠️</span> <?= $zeroStockCount ?> <?= $zeroStockCount === 1 ? 'product is' : 'products are' ?> out of stock. Please reorder inventory now.</div>
            <?php elseif ($lowStock > 0): ?>
            <div class="stock-alert warning"><span class="icon">⚠️</span> <?= $lowStock ?> <?= $lowStock === 1 ? 'product is' : 'products are' ?> low on stock. Review inventory levels.</div>
            <?php endif; ?>
            <div class="hero-footer">
                <div class="live-note">Live updates • <span id="lastUpdated">Just now</span></div>
                <div class="activity-list" id="activityList">
                    <?php if (empty($liveActivity)): ?>
                        <div class="activity-item empty-state">No recent activity.</div>
                    <?php else: ?>
                        <?php foreach ($liveActivity as $item): ?>
                                            <div class="activity-item">
                                                <div class="activity-title"><?= htmlspecialchars($item['title']) ?></div>
                                                <div class="activity-detail"><?= htmlspecialchars($item['detail']) ?></div>
                                                <div class="activity-time"><?= htmlspecialchars(date('M d, H:i', strtotime($item['time']))) ?></div>
                                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <section class="stock-graph-panel">
        <div class="stock-graph-header">
            <h2>Stock Management</h2>
            <p>Live inventory levels for the lowest-stock products.</p>
        </div>
        <canvas id="stockChartCanvas" width="860" height="300"></canvas>
    </section>

    <?php endif; ?>

    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="flash" role="alert" aria-live="assertive"><?= htmlspecialchars($_SESSION['flash']) ?></div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php if (!$loggedIn): ?>
        <main class="login-wrap" id="login-main">
            <section class="panel login-panel role-form-panel" data-panel="login">
                <h2><?= htmlspecialchars($labels['login_title']) ?></h2>
                <p class="login-intro"><?= htmlspecialchars($labels['welcome']) ?></p>
                <div class="panel-body">
                    <form method="post" class="stacked-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <label for="loginUsername"><?= htmlspecialchars($labels['username']) ?></label>
                        <input id="loginUsername" type="text" name="username" autocomplete="username" placeholder="<?= htmlspecialchars($labels['username']) ?>" required>
                        <label for="loginPassword"><?= htmlspecialchars($labels['password']) ?></label>
                        <input id="loginPassword" type="password" name="password" autocomplete="current-password" placeholder="<?= htmlspecialchars($labels['password']) ?>" required>
                        <button type="submit" name="login_submit" value="1"><?= htmlspecialchars($labels['login']) ?></button>
                    </form>
                    <p class="forgot-password-link"><a href="password_recovery.php"><?= htmlspecialchars($labels['forgot_password']) ?></a></p>
                </div>
            </section>
        </main>
    <?php else: ?>
        <section class="toolbar">
            <div class="toolbar-left">
                <strong><?= htmlspecialchars($labels['welcome']) ?>, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></strong>
            </div>
            <div class="toolbar-right">
                <a class="glow-btn" href="change_password.php"><?= htmlspecialchars($labels['change_password']) ?></a>
                <button type="button" id="helpToggleBtn" class="glow-btn" title="System Help">? Help</button>
                <button type="button" id="languageToggleBtn" class="glow-btn">
                    <?= htmlspecialchars($language === 'en' ? 'Switch to ' . $labels['swahili'] : 'Switch to ' . $labels['english']) ?>
                </button>
                <form method="post" class="inline-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <button type="submit" name="logout" value="1"><?= htmlspecialchars($labels['logout']) ?></button>
                </form>
            </div>
        </section>

        <!-- Help Modal -->
        <div class="modal" id="helpModal" aria-hidden="true">
            <div class="modal-content">
                <h3>System Help & Quick Guide</h3>
                <div class="help-content">
                    <h4>📊 Dashboard Overview</h4>
                    <p>Monitor all key metrics in real-time: Total Sell, Total Profit/Loss, Inventory Value, and Outstanding Balances.</p>
                    <h4>📝 Creating Invoices</h4>
                    <p>Leave the invoice number blank to auto-generate a unique number. Add multiple items, apply discounts as needed.</p>
                    <h4>💳 Recording Payments</h4>
                    <p>Select an invoice or order to auto-fill customer and balance. Choose payment method and enter control number for tracking.</p>
                    <h4>📦 Stock Adjustment</h4>
                    <p>Update stock levels with buying/selling prices, manufacturer, grade, and condition (New/Used/Refurbished).</p>
                    <h4>💰 Balance Profile</h4>
                    <p>View all outstanding invoices and orders. Balances automatically clear when fully paid.</p>
                    <h4>⚙️ Panel Settings</h4>
                    <p>Click the settings icon (⚙️) on any panel to hide, pin, or set auto-refresh intervals.</p>
                </div>
                <div class="modal-actions">
                    <button id="closeHelpModal">Close</button>
                </div>
            </div>
        </div>
    <script src="password-tools.js" defer></script>
    <script>
        // Add help button handler
        document.getElementById('helpToggleBtn')?.addEventListener('click', function(){
            const modal = document.getElementById('helpModal');
            modal.classList.add('open');
            modal.setAttribute('aria-hidden', 'false');
        });
        document.getElementById('closeHelpModal')?.addEventListener('click', function(){
            const modal = document.getElementById('helpModal');
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
        });

        // Auto language detection on first visit (uses GET to set session via PHP)
        (function(){
            var sessionHasLang = <?= isset($_SESSION['lang']) ? 'true' : 'false' ?>;
            var urlParams = new URLSearchParams(window.location.search);
            if (!sessionHasLang && !urlParams.has('lang')){
                try{
                    var nav = (navigator.language || navigator.userLanguage || 'en').toLowerCase();
                    var lang = nav.startsWith('sw') ? 'sw' : 'en';
                    // redirect with lang param so PHP picks it up
                    urlParams.set('lang', lang);
                    window.location.search = urlParams.toString();
                }catch(e){}
            }
        })();

        // Language toggle button
        document.getElementById('languageToggleBtn')?.addEventListener('click', function(){
            var nextLang = '<?= $language === 'en' ? 'sw' : 'en' ?>';
            var form = document.createElement('form');
            form.method = 'post';
            form.style.display = 'none';
            var token = document.createElement('input');
            token.name = 'csrf_token';
            token.value = '<?= htmlspecialchars($csrf_token) ?>';
            var langInput = document.createElement('input');
            langInput.name = 'lang';
            langInput.value = nextLang;
            form.appendChild(token);
            form.appendChild(langInput);
            document.body.appendChild(form);
            form.submit();
        });

        // Add Layout placeholder
        document.getElementById('addLayoutBtn')?.addEventListener('click', function(){
            var flash = document.createElement('div');
            flash.className = 'flash';
            flash.textContent = 'Add Layout clicked (placeholder)';
            document.body.insertBefore(flash, document.body.firstChild);
            setTimeout(()=>flash.remove(), 2500);
        });

        // Invoice items row controls
        var ORDER_ITEMS = <?= json_encode($orderItemsByOrder, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
        document.addEventListener('DOMContentLoaded', function(){
            var container = document.getElementById('invoiceItems');
            var addInvoiceBtn = document.getElementById('addInvoiceRow');
            var detectCheckbox = document.getElementById('invoiceDetectSaleOrder');
            var orderSourcePanel = document.getElementById('invoiceOrderSource');
            var orderSourceSelect = document.getElementById('invoiceOrderSourceSelect');
            var customerInput = document.querySelector('input[name="invoice_customer"]');

            function makeRow(values) {
                values = values || {};
                var div = document.createElement('div');
                div.className = 'invoice-row';
                div.innerHTML = '<input type="text" name="invoice_item[]" placeholder="Description" required> <input type="text" name="invoice_category[]" placeholder="Category"> <input type="number" name="invoice_quantity[]" min="1" value="1" placeholder="Qty"> <input type="number" step="1" name="invoice_unit_price[]" min="0" value="0" placeholder="Unit price (TZS)"> <input type="number" step="0.01" name="invoice_discount[]" min="0" max="100" value="0" placeholder="Discount %"> <input type="text" name="invoice_manufacturer[]" placeholder="Manufacturer"> <input type="text" name="invoice_grade[]" placeholder="Grade"> <input type="text" name="invoice_condition[]" placeholder="Condition"> <input type="number" step="0.01" name="invoice_vat[]" min="0" max="30" value="0" placeholder="VAT % (Optional)"> <button type="button" class="remove-invoice-row">Remove</button>';
                var descInput = div.querySelector('input[name="invoice_item[]"]');
                var categoryInput = div.querySelector('input[name="invoice_category[]"]');
                var quantityInput = div.querySelector('input[name="invoice_quantity[]"]');
                var unitPriceInput = div.querySelector('input[name="invoice_unit_price[]"]');
                var discountInput = div.querySelector('input[name="invoice_discount[]"]');
                var manufacturerInput = div.querySelector('input[name="invoice_manufacturer[]"]');
                var gradeInput = div.querySelector('input[name="invoice_grade[]"]');
                var conditionInput = div.querySelector('input[name="invoice_condition[]"]');
                var vatInput = div.querySelector('input[name="invoice_vat[]"]');
                if (descInput) descInput.value = values.description || '';
                if (categoryInput) categoryInput.value = values.category || '';
                if (quantityInput) quantityInput.value = values.quantity || 1;
                if (unitPriceInput) unitPriceInput.value = values.unit_price || 0;
                if (discountInput) discountInput.value = values.discount_percent || 0;
                if (manufacturerInput) manufacturerInput.value = values.manufacturer || '';
                if (gradeInput) gradeInput.value = values.grade || '';
                if (conditionInput) conditionInput.value = values.condition || '';
                if (vatInput) vatInput.value = values.vat_percent || 0;
                return div;
            }

            function clearInvoiceRows() {
                if (!container) return;
                container.innerHTML = '';
            }

            function populateInvoiceFromOrder(orderId) {
                if (!container || !orderId || !ORDER_ITEMS[orderId]) return;
                var items = ORDER_ITEMS[orderId];
                clearInvoiceRows();
                items.forEach(function(item) {
                    container.appendChild(makeRow({
                        description: item.item || '',
                        category: item.category || '',
                        quantity: item.quantity || 1,
                        unit_price: item.unit_price || 0,
                        discount_percent: item.discount_percent || 0,
                        vat_percent: item.vat_percent || 0,
                        manufacturer: item.manufacturer || '',
                        grade: item.grade || '',
                        condition: item.condition || ''
                    }));
                });
                if (customerInput && orderSourceSelect && orderSourceSelect.selectedOptions.length > 0) {
                    var customer = orderSourceSelect.selectedOptions[0].dataset.customer || '';
                    if (customer !== '' && customerInput.value.trim() === '') {
                        customerInput.value = customer;
                    }
                }
            }

            function updateOrderSourceVisibility() {
                if (!orderSourcePanel || !detectCheckbox) return;
                orderSourcePanel.style.display = detectCheckbox.checked ? 'block' : 'none';
            }

            if (detectCheckbox) {
                detectCheckbox.addEventListener('change', function() {
                    updateOrderSourceVisibility();
                });
                updateOrderSourceVisibility();
            }

            if (orderSourceSelect) {
                orderSourceSelect.addEventListener('change', function() {
                    if (detectCheckbox && detectCheckbox.checked) {
                        populateInvoiceFromOrder(this.value);
                    }
                });
            }

            if (addInvoiceBtn && container) {
                addInvoiceBtn.addEventListener('click', function(){
                    container.appendChild(makeRow());
                });
            }

            if (container) {
                container.addEventListener('click', function(e){
                    if (e.target && e.target.classList && e.target.classList.contains('remove-invoice-row')){
                        var row = e.target.closest('.invoice-row');
                        if (row) row.remove();
                    }
                });
            }
        });
        
    </script>

        <main class="grid" id="app-main">
            <?php if (mitas_can('users.manage')): ?>
            <section class="panel account-management-panel" data-panel="account_management">
                <h2><?= htmlspecialchars($labels['account_management']) ?></h2>
                <p class="login-intro">Create accounts or set a strong temporary password here. The user must change it at their next sign-in.</p>
                <div class="panel-body account-management-body">
                    <form method="post" class="stacked-form account-create-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <label for="newUsername"><?= htmlspecialchars($labels['username']) ?></label>
                        <input id="newUsername" type="text" name="new_username" autocomplete="off" maxlength="64" required>
                        <label for="newUserRole"><?= htmlspecialchars($labels['account_role']) ?></label>
                        <select id="newUserRole" name="new_role" required>
                            <?php foreach ($loginRoles as $roleKey => $role): ?>
                                <option value="<?= htmlspecialchars($roleKey) ?>"><?= htmlspecialchars($role['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label for="newUserPassword"><?= htmlspecialchars($labels['new_account_password']) ?></label>
                        <input id="newUserPassword" type="password" name="new_password" autocomplete="new-password" minlength="12" required>
                        <div class="password-tools">
                            <button type="button" data-generate-password="newUserPassword"><?= htmlspecialchars($labels['generate_password']) ?></button>
                            <button type="button" data-toggle-password="newUserPassword" aria-pressed="false"><?= htmlspecialchars($labels['show_password']) ?></button>
                        </div>
                        <button type="submit" name="create_user" value="1"><?= htmlspecialchars($labels['save_new_account']) ?></button>
                    </form>

                    <p id="passwordToolStatus" class="sr-only" aria-live="polite"></p>
                    <div class="account-list">
                        <?php foreach ($managedUsers as $managedUser): ?>
                            <?php $accountFormId = 'account-' . (int)$managedUser['id']; ?>
                            <form method="post" class="account-row">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="user_id" value="<?= (int)$managedUser['id'] ?>">
                                <div class="account-row-heading">
                                    <strong><?= htmlspecialchars($managedUser['username']) ?></strong>
                                    <span class="account-status"><?= (int)$managedUser['is_active'] === 1 ? htmlspecialchars($labels['active']) : htmlspecialchars($labels['inactive']) ?></span>
                                </div>
                                <label for="<?= $accountFormId ?>-role"><?= htmlspecialchars($labels['account_role']) ?></label>
                                <select id="<?= $accountFormId ?>-role" name="account_role" required>
                                    <?php foreach ($loginRoles as $roleKey => $role): ?>
                                        <option value="<?= htmlspecialchars($roleKey) ?>" <?= $managedUser['role'] === $roleKey ? 'selected' : '' ?>><?= htmlspecialchars($role['label']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="<?= $accountFormId ?>-status"><?= htmlspecialchars($labels['account_status']) ?></label>
                                <select id="<?= $accountFormId ?>-status" name="account_active" required>
                                    <option value="1" <?= (int)$managedUser['is_active'] === 1 ? 'selected' : '' ?>><?= htmlspecialchars($labels['active']) ?></option>
                                    <option value="0" <?= (int)$managedUser['is_active'] !== 1 ? 'selected' : '' ?>><?= htmlspecialchars($labels['inactive']) ?></option>
                                </select>
                                <label for="<?= $accountFormId ?>-password"><?= htmlspecialchars($labels['reset_password']) ?></label>
                                <input id="<?= $accountFormId ?>-password" type="password" name="reset_password" autocomplete="new-password" minlength="12">
                                <div class="password-tools">
                                    <button type="button" data-generate-password="<?= $accountFormId ?>-password"><?= htmlspecialchars($labels['generate_password']) ?></button>
                                    <button type="button" data-toggle-password="<?= $accountFormId ?>-password" aria-pressed="false"><?= htmlspecialchars($labels['show_password']) ?></button>
                                </div>
                                <button type="submit" name="update_user" value="1"><?= htmlspecialchars($labels['save_account']) ?></button>
                            </form>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <?php endif; ?>
            <?php if (mitas_can('inventory.manage')): ?>
<section class="panel" data-panel="add_product">
                <h2><?= htmlspecialchars($labels['add_product']) ?></h2>
                <div class="panel-body">
                <form method="post" class="stacked-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="text" name="product_name" placeholder="<?= htmlspecialchars($labels['product_name']) ?>" required>
                    <input type="text" name="category" placeholder="<?= htmlspecialchars($labels['category']) ?>" required>
                    <input type="number" name="quantity" placeholder="<?= htmlspecialchars($labels['quantity']) ?>" min="0" required>
                    <input type="number" step="1" min="0" name="buy_price" placeholder="<?= htmlspecialchars($labels['buy_price']) ?> (TZS)" required>
                    <input type="number" step="1" min="0" name="sell_price" placeholder="<?= htmlspecialchars($labels['sell_price']) ?> (TZS)" required>
                    <input type="text" name="location" placeholder="<?= htmlspecialchars($labels['location']) ?>" value="Main Warehouse">
                    <input type="text" name="manufacturer" placeholder="Manufacturer Name (Optional)" title="Manufacturer Name">
                    <select name="grade" title="Grade">
                        <option value="">Grade (Optional)</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="Standard">Standard</option>
                        <option value="Premium">Premium</option>
                        <option value="Economy">Economy</option>
                    </select>
                    <select name="condition" title="Condition">
                        <option value="">Condition (Optional)</option>
                        <option value="New">New</option>
                        <option value="Like New">Like New</option>
                        <option value="Used">Used</option>
                        <option value="Refurbished">Refurbished</option>
                    </select>
                    <button type="submit"><?= htmlspecialchars($labels['save_product']) ?></button>
                </form>
                </div>
            </section>
<?php endif; ?>

            <?php if (mitas_can('customers.manage')): ?>
<section class="panel" data-panel="add_customer">
                <h2><?= htmlspecialchars($labels['add_customer']) ?></h2>
                <div class="panel-body">
                <form method="post" class="stacked-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="text" name="customer_name" placeholder="<?= htmlspecialchars($labels['customer_name']) ?>" required>
                    <input type="text" name="phone" placeholder="<?= htmlspecialchars($labels['phone']) ?>">
                    <input type="email" name="email" placeholder="<?= htmlspecialchars($labels['email']) ?>">
                    <input type="text" name="address" placeholder="<?= htmlspecialchars($labels['address']) ?>">
                    <button type="submit"><?= htmlspecialchars($labels['save_customer']) ?></button>
                </form>
                </div>
            </section>
<?php endif; ?>

            <?php if (mitas_can('suppliers.manage')): ?>
<section class="panel" data-panel="add_supplier">
                <h2><?= htmlspecialchars($labels['add_supplier']) ?></h2>
                <div class="panel-body">
                <form method="post" class="stacked-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="text" name="supplier_name" placeholder="<?= htmlspecialchars($labels['supplier_name']) ?>" required>
                    <input type="text" name="supplier_phone" placeholder="<?= htmlspecialchars($labels['phone']) ?>">
                    <input type="email" name="supplier_email" placeholder="<?= htmlspecialchars($labels['email']) ?>">
                    <input type="text" name="supplier_address" placeholder="<?= htmlspecialchars($labels['address']) ?>">
                    <button type="submit"><?= htmlspecialchars($labels['save_supplier']) ?></button>
                </form>
                </div>
            </section>
<?php endif; ?>

            <?php if (mitas_can('company.manage')): ?>
<section class="panel" data-panel="company_profile">
                <h2>Company Profile</h2>
                <div class="panel-body">
                    <form method="post" class="stacked-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="text" name="company_name" placeholder="Company name" value="<?= htmlspecialchars($companyProfile['company_name'] ?? '') ?>" required>
                        <input type="text" name="tagline" placeholder="Tagline" value="<?= htmlspecialchars($companyProfile['tagline'] ?? '') ?>">
                        <input type="text" name="address" placeholder="Address" value="<?= htmlspecialchars($companyProfile['address'] ?? '') ?>">
                        <input type="text" name="pobox" placeholder="P.O. Box" value="<?= htmlspecialchars($companyProfile['pobox'] ?? '') ?>">
                        <input type="text" name="phone" placeholder="Phone" value="<?= htmlspecialchars($companyProfile['phone'] ?? '') ?>">
                        <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($companyProfile['email'] ?? '') ?>">
                        <input type="text" name="website" placeholder="Website" value="<?= htmlspecialchars($companyProfile['website'] ?? '') ?>">
                        <input type="text" name="logo_path" placeholder="Logo path (e.g. logo.svg)" value="<?= htmlspecialchars($companyProfile['logo_path'] ?? 'logo.svg') ?>">
                        <button type="submit">Save Company Profile</button>
                    </form>
                    <form method="post" style="margin-top:8px;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <button type="submit" name="trigger_backup" value="1">Run Backup Now</button>
                    </form>
                </div>
            </section>
<?php endif; ?>

            <?php if (mitas_can('sales.manage')): ?>
<section class="panel" data-panel="create_purchase">
                <h2><?= htmlspecialchars($labels['create_purchase']) ?></h2>
                <div class="panel-body">
                <form method="post" class="stacked-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="text" name="sale_customer" placeholder="<?= htmlspecialchars($labels['customer_name']) ?>" required>
                    <label style="display:flex; align-items:center; gap:8px; margin:10px 0;">
                        <input type="checkbox" name="sale_apply_vat" value="1">
                        <span>Apply VAT to this sale order</span>
                    </label>
                    <div id="orderItemsContainer">
                        <div class="order-item-row">
                            <input type="text" name="sale_item[]" placeholder="<?= htmlspecialchars($labels['purchase_item']) ?>" required>
                            <input type="text" name="sale_category[]" placeholder="<?= htmlspecialchars($labels['purchase_category']) ?>">
                            <input type="number" name="sale_quantity[]" placeholder="<?= htmlspecialchars($labels['purchase_quantity']) ?>" min="0" required>
                            <input type="number" step="1" min="0" name="sale_amount[]" placeholder="<?= htmlspecialchars($labels['purchase_amount']) ?>" required>
                            <input type="number" step="0.01" min="0" max="30" name="sale_vat[]" placeholder="VAT % (Optional)" value="18">
                            <input type="number" step="1" min="0" max="100" name="sale_discount[]" placeholder="<?= htmlspecialchars($labels['discount_percent']) ?>" value="0">
                            <input type="text" name="sale_manufacturer[]" placeholder="Manufacturer">
                            <input type="text" name="sale_grade[]" placeholder="Grade">
                            <input type="text" name="sale_condition[]" placeholder="Condition">
                            <button type="button" class="remove-order-row" style="margin-top:8px;">Remove</button>
                        </div>
                    </div>
                    <button type="button" id="addOrderRowBtn">Add another item</button>
                    <p class="form-note" style="margin:12px 0 12px;font-size:0.95rem;color:#2f855a;">Sales orders are recorded as sold immediately and inventory is reduced.</p>
                    <button type="submit"><?= htmlspecialchars($labels['save_purchase']) ?></button>
                </form>
                </div>
            </section>
<?php endif; ?>

            <?php if (mitas_can('invoices.manage')): ?>
<section class="panel" data-panel="create_invoice">
                <h2><?= htmlspecialchars($labels['create_invoice']) ?></h2>
                <div class="panel-body">
                    <form method="post" class="stacked-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="text" name="invoice_number" placeholder="<?= htmlspecialchars($labels['invoice_number']) ?> (leave blank to auto-generate)">
                        <input type="text" name="invoice_customer" placeholder="<?= htmlspecialchars($labels['customer_name']) ?>" required>
                        <label style="display:flex; align-items:center; gap:8px; margin:10px 0;">
                            <input type="checkbox" id="invoiceDetectSaleOrder" name="invoice_detect_order" value="1">
                            <span>Auto-detect sale order for this invoice</span>
                        </label>
                        <div id="invoiceOrderSource" style="display:none; margin-bottom:12px;">
                            <label style="display:block; margin-bottom:6px;">Import from sale order</label>
                            <select name="invoice_order_source" id="invoiceOrderSourceSelect">
                                <option value="">-- select sale order --</option>
                                <?php foreach ($orders as $ord): ?>
                                    <option value="<?= (int)$ord['id'] ?>" data-customer="<?= htmlspecialchars($ord['customer']) ?>">Order #<?= (int)$ord['id'] ?> - <?= htmlspecialchars($ord['customer']) ?> - <?= htmlspecialchars($ord['item']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <label style="display:flex; align-items:center; gap:8px; margin:10px 0;">
                            <input type="checkbox" name="invoice_apply_vat" value="1">
                            <span>Apply VAT to this invoice (Customer Request)</span>
                        </label>
                        <div id="invoiceItems">
                            <div class="invoice-row">
                                <input type="text" name="invoice_item[]" placeholder="Description" required>
                                <input type="text" name="invoice_category[]" placeholder="Category">
                                <input type="number" name="invoice_quantity[]" min="1" value="1" placeholder="Qty">
                                <input type="number" step="1" name="invoice_unit_price[]" min="0" value="0" placeholder="Unit price (TZS)">
                                <input type="number" step="0.01" name="invoice_discount[]" min="0" max="100" value="0" placeholder="Discount %">
                                <input type="number" step="0.01" name="invoice_vat[]" min="0" max="30" value="0" placeholder="VAT % (Optional)">
                                <input type="text" name="invoice_manufacturer[]" placeholder="Manufacturer (Optional)">
                                <input type="text" name="invoice_grade[]" placeholder="Grade (Optional)">
                                <input type="text" name="invoice_condition[]" placeholder="Condition (Optional)">
                                <button type="button" class="remove-invoice-row">Remove</button>
                            </div>
                        </div>
                        <button type="button" id="addInvoiceRow">Add item</button>
                        <textarea name="invoice_notes" rows="3" placeholder="<?= htmlspecialchars($labels['invoice_notes']) ?>"></textarea>
                        <button type="submit"><?= htmlspecialchars($labels['save_invoice']) ?></button>
                    </form>
                    </div>
            </section>
<?php endif; ?>

                <?php if (mitas_can('sales.manage')): ?>
<section class="panel" data-panel="recent_orders">
                    <h2><?= htmlspecialchars($labels['recent_orders']) ?></h2>
                    <div class="panel-body">
                    <form method="post" class="stacked-form">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="text" name="sale_customer" placeholder="<?= htmlspecialchars($labels['customer_name']) ?>" required>
                        <label style="display:flex; align-items:center; gap:8px; margin:10px 0;">
                            <input type="checkbox" name="sale_apply_vat" value="1">
                            <span>Apply VAT to this order (Customer Request)</span>
                        </label>
                        <input type="text" name="sale_item" placeholder="<?= htmlspecialchars($labels['item']) ?>" required>
                        <input type="text" name="sale_category" placeholder="<?= htmlspecialchars($labels['sale_category']) ?>" required>
                        <input type="number" name="sale_quantity" placeholder="<?= htmlspecialchars($labels['quantity']) ?>" min="0" required>
                        <input type="number" step="1" min="0" name="sale_amount" placeholder="<?= htmlspecialchars($labels['amount']) ?> (TZS)">
                        <input type="number" step="1" min="0" max="100" name="sale_discount" placeholder="<?= htmlspecialchars($labels['discount_percent']) ?>" value="0">
                        <input type="text" name="sale_manufacturer" placeholder="Manufacturer Name (Optional)">
                        <input type="text" name="sale_grade" placeholder="Grade (Optional)">
                        <input type="text" name="sale_condition" placeholder="Condition (Optional)">
                        <button type="submit">Save Sale</button>
                    </form>
                    </div>
                </section>
<?php endif; ?>

                <?php if (mitas_can('payments.manage')): ?>
<section class="panel" data-panel="record_payment">
                    <h2>Record Payment</h2>
                    <div class="panel-body">
                    <form method="post" class="stacked-form">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <label>Choose document type</label>
                        <select name="payment_document_type">
                            <option value="invoice">Invoice</option>
                            <option value="order">Order</option>
                        </select>
                        <label>Choose Invoice (optional)</label>
                        <select name="payment_invoice_id" id="paymentInvoiceSelect">
                            <option value="" data-customer="" data-balance="0">-- select invoice --</option>
                            <?php foreach ($invoices as $inv): ?>
                                <?php $invBalance = getInvoiceBalance($pdo, (int)$inv['id']); ?>
                                <option value="<?= (int)$inv['id'] ?>" data-customer="<?= htmlspecialchars($inv['customer_name']) ?>" data-balance="<?= number_format($invBalance, 0, '.', '') ?>"><?= htmlspecialchars($inv['invoice_number']) ?> - <?= htmlspecialchars($inv['customer_name']) ?> - Balance TZS <?= number_format($invBalance, 0) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label>Choose Order (optional)</label>
                        <select name="payment_order_id" id="paymentOrderSelect">
                            <option value="" data-customer="" data-balance="0">-- select order --</option>
                            <?php foreach ($orders as $ord): ?>
                                <?php $ordBalance = getOrderBalance($pdo, (int)$ord['id']); ?>
                                <option value="<?= (int)$ord['id'] ?>" data-customer="<?= htmlspecialchars($ord['customer']) ?>" data-balance="<?= number_format($ordBalance, 0, '.', '') ?>"><?= htmlspecialchars($ord['customer']) ?> - <?= htmlspecialchars($ord['item']) ?> - Balance TZS <?= number_format($ordBalance, 0) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label>Or choose Customer</label>
                        <select name="payment_customer" id="paymentCustomerSelect">
                            <option value="">-- select customer --</option>
                            <?php foreach ($customers as $c): ?>
                                <option value="<?= htmlspecialchars($c['name']) ?>"><?= htmlspecialchars($c['name']) ?><?= $c['phone'] ? ' (' . htmlspecialchars($c['phone']) . ')' : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" step="1" min="0" name="payment_amount" id="paymentAmount" placeholder="<?= htmlspecialchars($labels['amount']) ?> (TZS)" required>
                        <label style="display:flex; align-items:center; gap:8px; margin:10px 0;">
                            <input type="checkbox" name="payment_apply_vat" value="1">
                            <span>Apply VAT to this payment (Customer Request)</span>
                        </label>
                        <label>Payment method</label>
                        <select name="payment_method" id="paymentMethod">
                            <option value="Cash">Cash</option>
                            <option value="Mobile">Mobile</option>
                            <option value="Card">Card</option>
                            <option value="Other">Other</option>
                        </select>
                        <label id="paymentMethodDetailLabel">Bank / Mobile service</label>
                        <select name="payment_method_detail" id="paymentMethodDetail">
                            <option value="Cash">Cash</option>
                            <option value="MIX BY YAS">MIX BY YAS</option>
                            <option value="M-PESA">M-PESA</option>
                            <option value="HALOPESA">HALOPESA</option>
                            <option value="AIRTEL MONEY">AIRTEL MONEY</option>
                            <option value="NBC">NBC</option>
                            <option value="NMB">NMB</option>
                            <option value="CRDB">CRDB</option>
                            <option value="OTHERS">OTHERS</option>
                        </select>
                        <input type="text" name="payment_reference" id="paymentReference" placeholder="Control / reference number">
                        <input type="text" name="payment_sender_name" id="paymentSenderName" placeholder="Received from / Sender name">
                        <button type="submit"><?= htmlspecialchars($labels['save_invoice']) ?></button>
                    </form>
                    </div>
                </section>
<?php endif; ?>

                <?php if (mitas_can('inventory.manage')): ?>
<section class="panel" data-panel="stock_adjustment">
                    <h2>Stock Adjustment</h2>
                    <div class="panel-body">
                    <form method="post" class="stacked-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <label>Select Product</label>
                        <input type="text" id="adjust_product_search" placeholder="Search product by name" autocomplete="off">
                        <input type="hidden" name="adjust_product_id" id="adjust_product_id">
                        <div id="product_suggestions" class="suggestions" style="display:none"></div>
                        <input type="number" name="adjust_change" placeholder="Quantity change (+/-)" required>
                        <input type="text" name="adjust_reason" placeholder="Reason">
                        <label>Buying Price (TZS) - optional</label>
                        <input type="number" step="1" min="0" name="adjust_buy_price" placeholder="Buying price">
                        <label>Selling Price (TZS) - optional</label>
                        <input type="number" step="1" min="0" name="adjust_sell_price" placeholder="Selling price">
                        <input type="text" name="adjust_category" placeholder="Category">
                        <label>Manufacturer - optional</label>
                        <input type="text" name="adjust_manufacturer" placeholder="Manufacturer name">
                        <label>Grade of Item - optional</label>
                        <select name="adjust_grade">
                            <option value="">-- select grade --</option>
                            <option value="Grade A">Grade A</option>
                            <option value="Grade B">Grade B</option>
                            <option value="Grade C">Grade C</option>
                            <option value="Standard">Standard</option>
                            <option value="Premium">Premium</option>
                            <option value="Economy">Economy</option>
                        </select>
                        <label>Condition - optional</label>
                        <select name="adjust_condition">
                            <option value="">-- select condition --</option>
                            <option value="New">New</option>
                            <option value="Used">Used</option>
                            <option value="Refurbished">Refurbished</option>
                            <option value="Like New">Like New</option>
                        </select>
                        <button type="submit">Apply Adjustment</button>
                    </form>
                    </div>
                </section>
<?php endif; ?>
        </main>

        <?php if (mitas_can('payments.view')): ?>
<section class="panel table-panel" data-panel="balance_profile">
            <h2>Balance Profile - Outstanding Balances</h2>
            <div class="panel-search">
                <label class="search-label">🔍 Search:</label>
                <input type="text" class="search-box" placeholder="Search by customer or document..." data-table="balance_profile_table">
            </div>
            <div class="panel-body">
                <table id="balance_profile_table">
                    <thead>
                        <tr>
                            <th>Customer Name</th>
                            <th>Document Number</th>
                            <th>Details</th>
                            <th>Balance Amount (TZS)</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch outstanding invoices (not settled) - grouped by invoice ID to show each only once
                        $outstandingInvoices = $pdo->query("SELECT i.id, i.invoice_number, i.customer_name, i.total_amount, i.item, 'Invoice' AS type,
                            COALESCE(i.final_total, i.total_amount) - COALESCE((SELECT SUM(amount) FROM payments p WHERE p.invoice_id = i.id), 0) AS balance
                            FROM invoices i
                            WHERE COALESCE(i.settled, 0) = 0 AND COALESCE(i.final_total, i.total_amount) - COALESCE((SELECT SUM(amount) FROM payments p WHERE p.invoice_id = i.id), 0) > 0
                            GROUP BY i.id
                            ORDER BY i.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
                        // Fetch outstanding orders (not settled) - grouped by order ID to show each only once
                        $outstandingOrders = $pdo->query("SELECT o.id, o.customer, o.item, o.amount, 'Order' AS type,
                            COALESCE(o.final_total, o.amount) - COALESCE((SELECT SUM(amount) FROM payments p WHERE p.order_id = o.id), 0) AS balance,
                            o.created_at
                            FROM orders o
                            WHERE COALESCE(o.settled, 0) = 0 AND COALESCE(o.final_total, o.amount) - COALESCE((SELECT SUM(amount) FROM payments p WHERE p.order_id = o.id), 0) > 0
                            GROUP BY o.id
                            ORDER BY o.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

                        $allBalances = [];
                        foreach ($outstandingInvoices as $inv) {
                            $allBalances[] = [
                                'customer' => $inv['customer_name'],
                                'document' => 'INV-' . htmlspecialchars($inv['invoice_number']),
                                'details' => htmlspecialchars($inv['item']) . ' • Total: TZS ' . number_format((float)$inv['total_amount'], 0),
                                'balance' => (float)$inv['balance'],
                                'status' => (float)$inv['balance'] > 0 ? 'Pending' : 'Cleared',
                                'time' => $inv['id']
                            ];
                        }
                        foreach ($outstandingOrders as $ord) {
                            $allBalances[] = [
                                'customer' => $ord['customer'],
                                'document' => 'ORD-' . (int)$ord['id'],
                                'details' => htmlspecialchars($ord['item']) . ' • Total: TZS ' . number_format((float)$ord['amount'], 0),
                                'balance' => (float)$ord['balance'],
                                'status' => (float)$ord['balance'] > 0 ? 'Pending' : 'Cleared',
                                'time' => $ord['id']
                            ];
                        }
                        usort($allBalances, function($a, $b) { return $b['balance'] <=> $a['balance']; });
                        ?>
                        <?php if (empty($allBalances)): ?>
                            <tr>
                                <td colspan="5" style="text-align:center; padding:20px; color:#2f855a;">All balances cleared - no outstanding payments!</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($allBalances as $bal): 
                                $rowColor = $bal['balance'] > 50000 ? 'background-color:#fee2e2;' : ($bal['balance'] > 10000 ? 'background-color:#fef08a;' : 'background-color:#dcfce7;');
                            ?>
                                <tr style="<?= $rowColor ?>" data-search-text="<?= htmlspecialchars(strtolower($bal['customer'] . ' ' . $bal['document'])) ?>">>
                                    <td><?= htmlspecialchars($bal['customer']) ?></td>
                                    <td><?= $bal['document'] ?></td>
                                    <td><?= $bal['details'] ?></td>
                                    <td style="font-weight:600; color:<?= $bal['balance'] > 0 ? '#b91c1c' : '#2f855a' ?>">TZS <?= number_format($bal['balance'], 0) ?></td>
                                    <td><?= $bal['status'] ?></td>
                                    <td>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                            <input type="hidden" name="mark_settled_type" value="<?= strpos($bal['document'], 'INV-') === 0 ? 'invoice' : 'order' ?>">
                                            <input type="hidden" name="mark_settled_id" value="<?= (int)$bal['time'] ?>">
                                            <label style="font-size:0.9rem;">
                                                <input type="checkbox" name="mark_settled_confirm" value="1"> I confirm
                                            </label>
                                            <button type="submit" name="mark_settled" value="1">Mark Paid</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
<?php endif; ?>

        <?php if (mitas_can('invoices.view')): ?>
<section class="panel table-panel" data-panel="document_queue">
            <h2><?= htmlspecialchars($labels['document_queue']) ?></h2>
            <div class="panel-search">
                <label class="search-label">🔍 Search:</label>
                <input type="text" class="search-box" placeholder="Search by type, document, customer, or details..." data-table="document_queue_table">
            </div>
            <div class="panel-body">
                <table id="document_queue_table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Document</th>
                            <th>Status</th>
                            <th>Details</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentQueue as $doc): ?>
                            <tr data-search-text="<?= htmlspecialchars(strtolower($doc['type'] . ' ' . $doc['title'] . ' ' . $doc['status'] . ' ' . $doc['detail'])) ?>">
                                <td><?= htmlspecialchars($doc['type']) ?></td>
                                <td><?= htmlspecialchars($doc['title']) ?></td>
                                <td><?= htmlspecialchars($doc['status']) ?></td>
                                <td><?= htmlspecialchars($doc['detail']) ?></td>
                                <td>
                                    <?php if (!empty($doc['print_url'])): ?>
                                        <a href="<?= htmlspecialchars($doc['print_url']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($labels['print']) ?></a>
                                    <?php else: ?>
                                        <span>Stored</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
<?php endif; ?>

        <?php if (mitas_can('inventory.view')): ?>
<section class="panel table-panel" data-panel="inventory_list">
            <h2><?= htmlspecialchars($labels['inventory_list']) ?></h2>
            <div class="panel-search">
                <label class="search-label">🔍 Search:</label>
                <input type="text" class="search-box" placeholder="Search by product name, category, or location..." data-table="inventory_list_table">
            </div>
            <div class="panel-body">
            <table id="inventory_list_table">
                <thead>
                    <tr>
                        <th><?= htmlspecialchars($labels['product_name']) ?></th>
                        <th><?= htmlspecialchars($labels['category']) ?></th>
                        <th><?= htmlspecialchars($labels['quantity']) ?></th>
                        <th>Availability</th>
                        <?php if (mitas_can('inventory.cost.view')): ?><th><?= htmlspecialchars($labels['buy_price']) ?></th><?php endif; ?>
                        <th><?= htmlspecialchars($labels['sell_price']) ?></th>
                        <th><?= htmlspecialchars($labels['location']) ?></th>
                        <th>Manufacturer</th>
                        <th>Grade</th>
                        <th>Condition</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): 
                        $qty = (int)$product['quantity'];
                        $rowBg = '';
                        if ($qty === 0) {
                            $rowBg = 'background-color:#fee2e2;'; // Red - Out of stock
                        } elseif ($qty > 0 && $qty <= 5) {
                            $rowBg = 'background-color:#fef08a;'; // Yellow - Low stock
                        } elseif ($qty > 5 && $qty <= 15) {
                            $rowBg = 'background-color:#dcfce7;'; // Light green - Medium stock
                        } else {
                            $rowBg = 'background-color:#e0f2fe;'; // Light blue - High stock
                        }
                    ?>
                        <tr style="<?= $rowBg ?>" data-search-text="<?= htmlspecialchars(strtolower($product['name'] . ' ' . $product['category'] . ' ' . $product['location'] . ' ' . ($product['manufacturer'] ?? ''))) ?>">">
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= htmlspecialchars($product['category']) ?></td>
                            <td><?= $qty ?></td>
                            <td><?= $qty > 0 ? 'In Stock' : 'Out of Stock' ?></td>
                            <?php if (mitas_can('inventory.cost.view')): ?><td>TZS <?= number_format((float)$product['buy_price'], 0) ?></td><?php endif; ?>
                            <td>TZS <?= number_format((float)$product['sell_price'], 0) ?></td>
                            <td><?= htmlspecialchars($product['location']) ?></td>
                            <td><?= htmlspecialchars($product['manufacturer'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($product['grade'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($product['condition'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </section>
<?php endif; ?>

        <?php if (mitas_can('customers.view')): ?>
<section class="panel table-panel" data-panel="customers">
            <h2><?= htmlspecialchars($labels['customers']) ?></h2>
            <div class="panel-search">
                <label class="search-label">🔍 Search:</label>
                <input type="text" class="search-box" placeholder="Search by name, phone, or email..." data-table="customers_table">
            </div>
            <div class="panel-body">
            <table id="customers_table">
                <thead>
                    <tr>
                        <th><?= htmlspecialchars($labels['customer_name']) ?></th>
                        <th><?= htmlspecialchars($labels['phone']) ?></th>
                        <th><?= htmlspecialchars($labels['email']) ?></th>
                        <th><?= htmlspecialchars($labels['address']) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                        <tr data-search-text="<?= htmlspecialchars(strtolower($customer['name'] . ' ' . $customer['phone'] . ' ' . $customer['email'])) ?>">
                            <td><?= htmlspecialchars($customer['name']) ?></td>
                            <td><?= htmlspecialchars($customer['phone']) ?></td>
                            <td><?= htmlspecialchars($customer['email']) ?></td>
                            <td><?= htmlspecialchars($customer['address']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </section>
<?php endif; ?>

        <?php if (mitas_can('suppliers.view')): ?>
<section class="panel table-panel" data-panel="suppliers">
            <h2><?= htmlspecialchars($labels['suppliers']) ?></h2>
            <div class="panel-search">
                <label class="search-label">🔍 Search:</label>
                <input type="text" class="search-box" placeholder="Search by name, phone, or email..." data-table="suppliers_table">
            </div>
            <div class="panel-body">
            <table id="suppliers_table">
                <thead>
                    <tr>
                        <th><?= htmlspecialchars($labels['supplier_name']) ?></th>
                        <th><?= htmlspecialchars($labels['phone']) ?></th>
                        <th><?= htmlspecialchars($labels['email']) ?></th>
                        <th><?= htmlspecialchars($labels['address']) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suppliers as $supplier): ?>
                        <tr data-search-text="<?= htmlspecialchars(strtolower($supplier['name'] . ' ' . $supplier['phone'] . ' ' . $supplier['email'])) ?>">
                            <td><?= htmlspecialchars($supplier['name']) ?></td>
                            <td><?= htmlspecialchars($supplier['phone']) ?></td>
                            <td><?= htmlspecialchars($supplier['email']) ?></td>
                            <td><?= htmlspecialchars($supplier['address']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </section>
<?php endif; ?>

        <?php if (mitas_can('purchases.view')): ?>
<section class="panel table-panel" data-panel="purchases">
            <h2><?= htmlspecialchars($labels['purchases']) ?></h2>
            <div class="panel-search">
                <label class="search-label">🔍 Search:</label>
                <input type="text" class="search-box" placeholder="Search by customer, item, or status..." data-table="purchases_table">
            </div>
            <div class="panel-body">
            <table id="purchases_table">
                <thead>
                    <tr>
                        <th><?= htmlspecialchars($labels['customer']) ?></th>
                        <th><?= htmlspecialchars($labels['item']) ?></th>
                        <th><?= htmlspecialchars($labels['quantity']) ?></th>
                        <th><?= htmlspecialchars($labels['amount']) ?></th>
                        <?php if (mitas_can('reports.view')): ?><th>Profit/Loss</th><?php endif; ?>
                        <th><?= htmlspecialchars($labels['status']) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): 
                        $status = htmlspecialchars($order['status'] ?? 'Pending');
                        $statusColor = '';
                        if ($status === 'Sold' || $status === 'Paid') {
                            $statusColor = 'background-color:#dcfce7;'; // Green - Sold/Paid
                        } elseif ($status === 'Pending' || $status === 'Pending Payment') {
                            $statusColor = 'background-color:#fef08a;'; // Yellow - Pending
                        } else {
                            $statusColor = 'background-color:#e0f2fe;'; // Blue - Other
                        }
                        $profit = (float)($order['profit'] ?? 0);
                        $profitLabel = $profit > 0 ? 'Profit' : ($profit < 0 ? 'Loss' : 'Break-even');
                        $profitColor = $profit > 0 ? '#047857' : ($profit < 0 ? '#b91c1c' : '#1f2937');
                    ?>
                        <tr style="<?= $statusColor ?>" data-search-text="<?= htmlspecialchars(strtolower($order['customer'] . ' ' . $order['item'] . ' ' . $status . (mitas_can('reports.view') ? ' ' . ($order['profit'] ?? '') : ''))) ?>">
                            <td><?= htmlspecialchars($order['customer']) ?></td>
                            <td><?= htmlspecialchars($order['item']) ?></td>
                            <td><?= (int)$order['quantity'] ?></td>
                            <td>TZS <?= number_format((float)$order['amount'], 0) ?></td>
                            <?php if (mitas_can('reports.view')): ?><td style="color: <?= $profitColor ?>; font-weight: 600;"><?= $profitLabel ?><?= $profit !== 0 ? ' TZS ' . number_format(abs($profit), 0) : '' ?></td><?php endif; ?>
                            <td><?= $status ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </section>
<?php endif; ?>

        <?php if (mitas_can('payments.view')): ?>
<section class="panel table-panel" data-panel="payments">
            <h2>Recent Payments</h2>
            <div class="panel-search">
                <label class="search-label">🔍 Search:</label>
                <input type="text" class="search-box" placeholder="Search by invoice ID or customer name..." data-table="payments_table">
            </div>
            <div class="panel-body">
            <table id="payments_table">
                <thead>
                    <tr>
                        <th>Invoice ID</th>
                        <th><?= htmlspecialchars($labels['customer']) ?></th>
                        <th><?= htmlspecialchars($labels['amount']) ?></th>
                        <th>Method</th>
                        <th><?= htmlspecialchars($labels['created']) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $p): ?>
                        <tr data-search-text="<?= htmlspecialchars(strtolower($p['invoice_id'] . ' ' . $p['customer_name'])) ?>">
                            <td><?= htmlspecialchars($p['invoice_id']) ?></td>
                            <td><?= htmlspecialchars($p['customer_name']) ?></td>
                            <td>TZS <?= number_format((float)$p['amount'], 0) ?></td>
                            <td><?= htmlspecialchars($p['method']) ?></td>
                            <td><?= htmlspecialchars(substr($p['created_at'], 0, 10)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </section>
<?php endif; ?>

        <?php if (mitas_can('inventory.manage')): ?>
<section class="panel table-panel" data-panel="adjustments">
            <h2>Stock Adjustments</h2>
            <div class="panel-search">
                <label class="search-label">🔍 Search:</label>
                <input type="text" class="search-box" placeholder="Search by reason or product ID..." data-table="adjustments_table">
            </div>
            <div class="panel-body">
            <table id="adjustments_table">
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Change</th>
                        <th>Reason</th>
                        <th><?= htmlspecialchars($labels['created']) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($adjustments as $a): ?>
                        <tr data-search-text="<?= htmlspecialchars(strtolower($a['reason'])) ?>">
                            <td><?= (int)$a['product_id'] ?></td>
                            <td><?= (int)$a['quantity_change'] ?></td>
                            <td><?= htmlspecialchars($a['reason']) ?></td>
                            <td><?= htmlspecialchars(substr($a['created_at'], 0, 10)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </section>
<?php endif; ?>
    <?php endif; ?>
    <script>
        const PRODUCTS = <?= json_encode($products_all, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_QUOT|JSON_HEX_APOS) ?>;
        const MITAS_CSRF = <?= json_encode($csrf_token) ?>;
        (function(){
            const input = document.getElementById('adjust_product_search');
            const hidden = document.getElementById('adjust_product_id');
            const box = document.getElementById('product_suggestions');
            if (!input) return;
            input.addEventListener('input', function(){
                const q = this.value.trim().toLowerCase();
                box.innerHTML = '';
                if (!q) { box.style.display='none'; hidden.value=''; return; }
                const matches = PRODUCTS.filter(p => p.name.toLowerCase().includes(q)).slice(0,8);
                if (matches.length === 0) { box.style.display='none'; return; }
                for (const m of matches) {
                    const el = document.createElement('div');
                    el.className = 'suggestion';
                    el.textContent = m.name + ' (ID:'+m.id+') - Qty:'+m.quantity;
                    el.dataset.id = m.id;
                    el.addEventListener('mousedown', function(e){
                        e.preventDefault();
                        input.value = m.name;
                        hidden.value = m.id;
                        box.style.display='none';
                    });
                    box.appendChild(el);
                }
                box.style.display = 'block';
            });
            document.addEventListener('click', function(e){ if (!box.contains(e.target) && e.target !== input) box.style.display='none'; });
        })();
    </script>
    <script>
    (function(){
        const container = document.getElementById('orderItemsContainer');
        const addBtn = document.getElementById('addOrderRowBtn');

        function createRow() {
            const row = document.createElement('div');
            row.className = 'order-item-row';
            row.innerHTML = `
                <input type="text" name="sale_item[]" placeholder="<?= htmlspecialchars($labels['purchase_item']) ?>" required>
                <input type="text" name="sale_category[]" placeholder="<?= htmlspecialchars($labels['purchase_category']) ?>">
                <input type="number" name="sale_quantity[]" placeholder="<?= htmlspecialchars($labels['purchase_quantity']) ?>" min="0" required>
                <input type="number" step="1" min="0" name="sale_amount[]" placeholder="<?= htmlspecialchars($labels['purchase_amount']) ?>" required>
                <input type="number" step="0.01" min="0" max="30" name="sale_vat[]" placeholder="VAT % (Optional)" value="18">
                <input type="number" step="1" min="0" max="100" name="sale_discount[]" placeholder="<?= htmlspecialchars($labels['discount_percent']) ?>" value="0">
                <input type="text" name="sale_manufacturer[]" placeholder="Manufacturer">
                <input type="text" name="sale_grade[]" placeholder="Grade">
                <input type="text" name="sale_condition[]" placeholder="Condition">
                <button type="button" class="remove-order-row" style="margin-top:8px;">Remove</button>
            `;
            const removeBtn = row.querySelector('.remove-order-row');
            removeBtn.addEventListener('click', function(){
                if (container.children.length > 1) {
                    row.remove();
                }
            });
            return row;
        }

        if (addBtn && container) {
            addBtn.addEventListener('click', function(){
                container.appendChild(createRow());
            });
        }

        // Attach remove listener to initial row
        const initialRemove = container.querySelector('.remove-order-row');
        if (initialRemove) {
            initialRemove.addEventListener('click', function(){
                const row = this.closest('.order-item-row');
                if (container.children.length > 1 && row) {
                    row.remove();
                }
            });
        }
    })();
    </script>
    <script>
    (function(){
        const searchBoxes = document.querySelectorAll('.search-box');
        if (!searchBoxes || searchBoxes.length === 0) return;
        function updateTableFilter(input) {
            const query = input.value.trim().toLowerCase();
            const tableId = input.dataset.table;
            if (!tableId) return;
            const table = document.getElementById(tableId);
            if (!table) return;
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const text = (row.dataset.searchText || '').toLowerCase();
                row.style.display = query === '' || text.includes(query) ? '' : 'none';
            });
        }
        searchBoxes.forEach(input => {
            input.addEventListener('input', function(){ updateTableFilter(this); });
        });
    })();
    </script>
    <div class="edge-toggle">
        <button id="edgeToggleBtn" title="Open tools">☰</button>
    </div>
    <div class="edge-panel" id="edgePanel" aria-hidden="true">
        <div class="edge-item" id="edgeShowAll">🔍 Show all panels</div>
        <div class="edge-item" id="edgeHideAll">🙈 Hide all panels</div>
        <div class="edge-item" id="edgeExportLayout">📤 Export layout</div>
        <div class="edge-item" id="edgeImportLayout">📥 Import layout</div>
        <div class="edge-item"><a href="reports.php">📄 Reports</a></div>
        <div class="edge-item"><a href="print_invoice.php">🖨️ Print Invoice</a></div>
    </div>

    <!-- Layout modal -->
    <div class="modal" id="layoutModal" aria-hidden="true">
        <div class="modal-content">
            <h3 id="layoutModalTitle">Manage Layouts</h3>
            <div id="layoutModalBody">
                <div style="margin-bottom:12px;">
                    <label>Layout name (when saving):</label>
                    <input id="layoutNameInput" type="text" style="width:60%; padding:6px;" />
                    <button id="saveLayoutBtn">Save to server</button>
                </div>
                <div id="layoutsList"></div>
            </div>
            <div class="modal-actions">
                <button id="closeLayoutModal">Close</button>
            </div>
        </div>
    </div>

    <script>
    (function(){
        const refreshBtn = document.getElementById('refreshLiveBtn');
        const activityListEl = document.getElementById('activityList');
        const lastUpdatedEl = document.getElementById('lastUpdated');
        const stockChartCanvas = document.getElementById('stockChartCanvas');
        const STOCK_GRAPH_DATA = <?= json_encode($stockGraphData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const metrics = {
            products: document.getElementById('metric-products'),
            customers: document.getElementById('metric-customers'),
            lowStock: document.getElementById('metric-low-stock'),
            revenue: document.getElementById('metric-revenue'),
            totalSellings: document.getElementById('metric-total-sellings'),
            outstanding: document.getElementById('metric-outstanding'),
            totalProfit: document.getElementById('metric-total-profit'),
            totalLoss: document.getElementById('metric-total-loss')
        };

        function renderActivity(items){
            if (!activityListEl) return;
            if (!items || items.length === 0) {
                activityListEl.innerHTML = '<div class="activity-item empty-state">No recent activity.</div>';
                return;
            }
            activityListEl.innerHTML = items.map(item => `
                <div class="activity-item">
                    <div class="activity-title">${item.title} ${item.type === 'Order' ? '<a class="view-link" href="print_order.php?order='+item.id+'" target="_blank">View</a>' : ''}</div>
                    <div class="activity-detail">${item.detail}</div>
                    <div class="activity-time">${item.timeLabel}</div>
                    ${typeof item.profit !== 'undefined' ? `<div class="activity-profit ${item.profit>=0 ? 'positive' : 'negative'}">${item.profit>=0? 'Profit':'Loss'}: TZS ${Number(item.profit).toLocaleString()}</div>` : ''}
                </div>
            `).join('');
        }

        function renderStockChart(data){
            if (!stockChartCanvas || !stockChartCanvas.getContext) return;
            const ctx = stockChartCanvas.getContext('2d');
            const width = stockChartCanvas.width;
            const height = stockChartCanvas.height;
            const padding = 40;
            ctx.clearRect(0, 0, width, height);
            if (!data || data.length === 0) {
                ctx.fillStyle = '#64748b';
                ctx.font = '18px Arial';
                ctx.fillText('No stock data available', padding, height / 2);
                return;
            }
            const names = data.map(item => item.name);
            const values = data.map(item => Number(item.quantity));
            const maxValue = Math.max(...values, 1);
            const barWidth = (width - padding * 2) / values.length * 0.7;
            ctx.fillStyle = '#e2e8f0';
            ctx.fillRect(padding, padding, width - padding * 2, height - padding * 2);
            values.forEach((value, idx) => {
                const x = padding + idx * ((width - padding * 2) / values.length) + ((width - padding * 2) / values.length - barWidth) / 2;
                const barHeight = ((height - padding * 2 - 20) * value) / maxValue;
                ctx.fillStyle = value <= 5 ? '#dc2626' : '#2563eb';
                ctx.fillRect(x, height - padding - barHeight, barWidth, barHeight);
                ctx.fillStyle = '#0f172a';
                ctx.font = '12px Arial';
                ctx.fillText(value.toString(), x + 4, height - padding - barHeight - 8);
                ctx.save();
                ctx.translate(x + barWidth / 2, height - padding + 14);
                ctx.rotate(-Math.PI / 4);
                ctx.fillText(names[idx].slice(0, 16), 0, 0);
                ctx.restore();
            });
            ctx.strokeStyle = '#94a3b8';
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(padding, height - padding);
            ctx.lineTo(width - padding, height - padding);
            ctx.stroke();
        }

        function updateLiveOverview(){
            fetch('dashboard_api.php')
                .then(response => response.json())
                .then(data => {
                    if (metrics.products) metrics.products.textContent = data.counts.products;
                    if (metrics.customers) metrics.customers.textContent = data.counts.customers;
                    if (metrics.lowStock) metrics.lowStock.textContent = data.counts.low_stock;
                    if (metrics.revenue) metrics.revenue.textContent = 'TZS ' + Number(data.counts.revenue || 0).toLocaleString(undefined, { maximumFractionDigits: 0 });
                    if (metrics.totalSellings) metrics.totalSellings.textContent = 'TZS ' + Number(data.counts.total_sell || (data.counts.revenue || 0)).toLocaleString(undefined, { maximumFractionDigits: 0 });
                    if (metrics.totalProfit) metrics.totalProfit.textContent = 'TZS ' + Number(data.counts.total_profit || 0).toLocaleString(undefined, { maximumFractionDigits: 0 });
                    if (metrics.totalLoss) metrics.totalLoss.textContent = 'TZS ' + Number(data.counts.total_loss || 0).toLocaleString(undefined, { maximumFractionDigits: 0 });
                    if (metrics.outstanding) metrics.outstanding.textContent = 'TZS ' + Number(data.counts.total_outstanding || 0).toLocaleString(undefined, { maximumFractionDigits: 0 });
                    renderStockChart(data.counts.stock_graph || STOCK_GRAPH_DATA);
                    const activity = (data.activity || []).map(item => ({...item, timeLabel: new Date(item.time.replace(' ', 'T')).toLocaleString([], { month:'short', day:'numeric', hour:'numeric', minute:'2-digit' }) }));
                    renderActivity(activity);
                    if (lastUpdatedEl) lastUpdatedEl.textContent = 'Updated ' + new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
                })
                .catch(() => {
                    if (lastUpdatedEl) lastUpdatedEl.textContent = 'Refresh failed';
                });
        }

        if (refreshBtn) refreshBtn.addEventListener('click', updateLiveOverview);
        // Auto-refresh every 10 seconds for real-time updates
        window.setInterval(updateLiveOverview, 10000);
        updateLiveOverview();

        // Auto-refresh entire page every 30 minutes to clear memory
        window.setInterval(() => { location.reload(); }, 1800000);

        function setupPaymentAutoFill() {
            const invoiceSelect = document.getElementById('paymentInvoiceSelect');
            const orderSelect = document.getElementById('paymentOrderSelect');
            const customerSelect = document.getElementById('paymentCustomerSelect');
            const amountInput = document.getElementById('paymentAmount');
            const methodSelect = document.getElementById('paymentMethod');
            const methodDetailSelect = document.getElementById('paymentMethodDetail');
            const methodDetailLabel = document.getElementById('paymentMethodDetailLabel');

            function clearOtherSelections(active) {
                if (active === 'invoice' && orderSelect) orderSelect.value = '';
                if (active === 'invoice' && customerSelect) customerSelect.selectedIndex = 0;
                if (active === 'order' && invoiceSelect) invoiceSelect.value = '';
                if (active === 'order' && customerSelect) customerSelect.selectedIndex = 0;
            }

            function updateMethodDetailOptions() {
                if (!methodSelect || !methodDetailSelect || !methodDetailLabel) return;
                const method = methodSelect.value;
                const options = [];
                if (method === 'Mobile') {
                    options.push('MIX BY YAS', 'M-PESA', 'HALOPESA', 'AIRTEL MONEY', 'OTHERS');
                    methodDetailLabel.textContent = 'Mobile service';
                } else if (method === 'Card') {
                    options.push('NBC', 'NMB', 'CRDB', 'OTHERS');
                    methodDetailLabel.textContent = 'Bank name';
                } else if (method === 'Other') {
                    options.push('OTHERS');
                    methodDetailLabel.textContent = 'Payment channel';
                } else {
                    options.push('Cash');
                    methodDetailLabel.textContent = 'Payment channel';
                }
                methodDetailSelect.innerHTML = '';
                options.forEach(val => {
                    const opt = document.createElement('option');
                    opt.value = val;
                    opt.textContent = val;
                    methodDetailSelect.appendChild(opt);
                });
            }

            function applyInvoiceSelection() {
                if (!invoiceSelect) return;
                const option = invoiceSelect.selectedOptions[0];
                if (!option) return;
                const customer = option.dataset.customer || '';
                const balance = option.dataset.balance || '';
                clearOtherSelections('invoice');
                if (customerSelect && customer) {
                    customerSelect.value = customer;
                }
                if (amountInput && balance) {
                    amountInput.value = balance;
                }
            }

            function applyOrderSelection() {
                if (!orderSelect) return;
                const option = orderSelect.selectedOptions[0];
                if (!option) return;
                const customer = option.dataset.customer || '';
                const balance = option.dataset.balance || '';
                clearOtherSelections('order');
                if (customerSelect && customer) {
                    customerSelect.value = customer;
                }
                if (amountInput && balance) {
                    amountInput.value = balance;
                }
            }

            if (invoiceSelect) {
                invoiceSelect.addEventListener('change', applyInvoiceSelection);
            }
            if (orderSelect) {
                orderSelect.addEventListener('change', applyOrderSelection);
            }
            if (customerSelect) {
                customerSelect.addEventListener('change', function(){
                    if (this.value && invoiceSelect) invoiceSelect.value = '';
                    if (this.value && orderSelect) orderSelect.value = '';
                });
            }
            if (methodSelect) {
                methodSelect.addEventListener('change', updateMethodDetailOptions);
            }
            updateMethodDetailOptions();
        }

        setupPaymentAutoFill();

        // Form validation helpers
        function validateForm(formElement) {
            const inputs = formElement.querySelectorAll('[required]');
            let isValid = true;
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.style.borderColor = '#dc2626';
                    isValid = false;
                } else {
                    input.style.borderColor = '';
                }
            });
            return isValid;
        }

        // Add validation to all forms before submission
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!validateForm(this)) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                }
            });
        });

        // Add help tooltips on focus
        document.querySelectorAll('input[placeholder], select').forEach(el => {
            el.addEventListener('focus', function() {
                if (this.placeholder) {
                    this.title = this.placeholder;
                }
            });
        });

        // Enable Edge panel toggle
        const edgeBtn = document.getElementById('edgeToggleBtn');
        const edgePanel = document.getElementById('edgePanel');
        const OPEN_KEY = 'mitas_edge_open';
        function setEdgeOpen(open){
            if(open){ edgePanel.classList.add('open'); edgePanel.setAttribute('aria-hidden','false'); }
            else { edgePanel.classList.remove('open'); edgePanel.setAttribute('aria-hidden','true'); }
            localStorage.setItem(OPEN_KEY, open ? '1' : '0');
        }
        edgeBtn.addEventListener('click', function(){ setEdgeOpen(!edgePanel.classList.contains('open')); });
        if(localStorage.getItem(OPEN_KEY) === '1') setEdgeOpen(true);

        // Panel settings injection and hide/show with pin and refresh interval
        const panels = Array.from(document.querySelectorAll('.panel'));
        const HIDDEN_KEY = 'mitas_hidden_panels_v1';
        const PINNED_KEY = 'mitas_pinned_panels_v1';
        const INTERVALS_KEY = 'mitas_panel_intervals_v1';
        let hidden = JSON.parse(localStorage.getItem(HIDDEN_KEY) || '[]');
        let pinned = JSON.parse(localStorage.getItem(PINNED_KEY) || '[]');
        let intervals = JSON.parse(localStorage.getItem(INTERVALS_KEY) || '{}');

        function saveHidden(){ localStorage.setItem(HIDDEN_KEY, JSON.stringify(hidden)); }
        function savePinned(){ localStorage.setItem(PINNED_KEY, JSON.stringify(pinned)); }
        function saveIntervals(){ localStorage.setItem(INTERVALS_KEY, JSON.stringify(intervals)); setupAutoRefresh(); }

        function setupAutoRefresh(){
            if (window._mitas_refresh_timer) { clearInterval(window._mitas_refresh_timer); window._mitas_refresh_timer = null; }
            const secs = Object.values(intervals).map(v => parseInt(v,10)).filter(n=>n>0);
            if (secs.length === 0) return;
            const min = Math.min(...secs);
            // refresh whole page at the minimum interval set (seconds)
            window._mitas_refresh_timer = setInterval(()=>{ location.reload(); }, min * 1000);
        }

        panels.forEach((panel, idx) => {
            const h2 = panel.querySelector('h2');
            if(!h2) return;
            // create settings button
            const btn = document.createElement('button');
            btn.className = 'settings-btn';
            btn.type = 'button';
            btn.title = 'Panel settings';
            btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06A2 2 0 0 1 2.34 18.9l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09c.7 0 1.3-.39 1.51-1a1.65 1.65 0 0 0-.33-1.82L4.3 4.34A2 2 0 0 1 6.64 1.5l.06.06a1.65 1.65 0 0 0 1.82.33h.09c.61-.25 1.28-.25 1.89 0h.09a1.65 1.65 0 0 0 1.82-.33l.06-.06A2 2 0 0 1 17.7 4.34l-.06.06a1.65 1.65 0 0 0-.33 1.82c.18.61.18 1.28 0 1.89v.09c.25.61.25 1.28 0 1.89z"></path></svg>';
            btn.style.marginLeft = '8px';
            h2.appendChild(btn);

            // pinned badge
            const pinnedBadge = document.createElement('span');
            pinnedBadge.className = 'pinned-badge';
            pinnedBadge.textContent = 'Pinned';
            if (pinned.includes(idx)) h2.appendChild(pinnedBadge);

            // menu
            const menu = document.createElement('div');
            menu.className = 'settings-menu';
            const hideBtn = document.createElement('button'); hideBtn.textContent = 'Hide panel';
            const showBtn = document.createElement('button'); showBtn.textContent = 'Show panel';
            const pinBtn = document.createElement('button'); pinBtn.textContent = pinned.includes(idx) ? 'Unpin panel' : 'Pin panel';
            const refreshBtn = document.createElement('button'); refreshBtn.textContent = 'Refresh panel';
            const intervalLabel = document.createElement('div'); intervalLabel.textContent = 'Auto-refresh (sec):';
            const intervalInput = document.createElement('input'); intervalInput.type = 'number'; intervalInput.min = '0'; intervalInput.style.width = '80px';
            intervalInput.value = intervals[idx] || '';
            const saveIntervalBtn = document.createElement('button'); saveIntervalBtn.textContent = 'Set interval';
            const clearIntervalBtn = document.createElement('button'); clearIntervalBtn.textContent = 'Clear interval';
            const closeBtn = document.createElement('button'); closeBtn.textContent = 'Close';
            menu.appendChild(hideBtn); menu.appendChild(showBtn); menu.appendChild(pinBtn); menu.appendChild(refreshBtn);
            menu.appendChild(intervalLabel); menu.appendChild(intervalInput); menu.appendChild(saveIntervalBtn); menu.appendChild(clearIntervalBtn); menu.appendChild(closeBtn);
            panel.appendChild(menu);

            btn.addEventListener('click', function(e){ e.stopPropagation(); menu.classList.toggle('open'); });
            hideBtn.addEventListener('click', function(){
                if (pinned.includes(idx)) { alert('This panel is pinned and cannot be hidden. Unpin first.'); return; }
                panel.classList.add('hidden-panel'); if(!hidden.includes(idx)) hidden.push(idx); saveHidden(); menu.classList.remove('open');
            });
            showBtn.addEventListener('click', function(){ panel.classList.remove('hidden-panel'); hidden = hidden.filter(i=>i!==idx); saveHidden(); menu.classList.remove('open'); });
            pinBtn.addEventListener('click', function(){
                if (pinned.includes(idx)) {
                    pinned = pinned.filter(i=>i!==idx);
                    pinnedBadge.remove();
                    pinBtn.textContent = 'Pin panel';
                } else {
                    pinned.push(idx);
                    h2.appendChild(pinnedBadge);
                    pinBtn.textContent = 'Unpin panel';
                }
                savePinned();
            });
            refreshBtn.addEventListener('click', function(){
                const panelName = panel.getAttribute('data-panel') || '';
                if (!panelName) { alert('No panel id set for refresh.'); return; }
                fetch('ajax_panel.php?panel='+encodeURIComponent(panelName))
                    .then(r => r.text())
                    .then(html => {
                        try {
                            const wrapper = document.createElement('div'); wrapper.innerHTML = html;
                            const frag = wrapper.querySelector('.panel-fragment');
                            if (frag) {
                                const body = panel.querySelector('.panel-body');
                                if (body) body.innerHTML = frag.innerHTML;
                            } else {
                                alert('No fragment returned');
                            }
                        } catch (err) { alert('Error parsing response'); }
                        menu.classList.remove('open');
                    }).catch(err=>{ alert('Refresh failed'); menu.classList.remove('open'); });
            });
            saveIntervalBtn.addEventListener('click', function(){ const v = parseInt(intervalInput.value,10); if (isNaN(v) || v <= 0) { alert('Enter seconds > 0'); return; } intervals[idx]=v; saveIntervals(); menu.classList.remove('open'); });
            clearIntervalBtn.addEventListener('click', function(){ delete intervals[idx]; saveIntervals(); intervalInput.value = ''; menu.classList.remove('open'); });
            closeBtn.addEventListener('click', function(){ menu.classList.remove('open'); });
        });

        // apply hidden from storage, but never hide pinned panels
        hidden.forEach(i => { if(panels[i] && !pinned.includes(i)) panels[i].classList.add('hidden-panel'); });

        document.addEventListener('click', function(){ document.querySelectorAll('.settings-menu.open').forEach(m=>m.classList.remove('open')); });

        // edge panel actions
        const edgeShowAll = document.getElementById('edgeShowAll');
        const edgeHideAll = document.getElementById('edgeHideAll');
        const edgeExportLayout = document.getElementById('edgeExportLayout');
        const edgeImportLayout = document.getElementById('edgeImportLayout');
        edgeShowAll.addEventListener('click', function(){ panels.forEach((p, i)=>{ p.classList.remove('hidden-panel'); }); hidden = []; saveHidden(); setEdgeOpen(false); });
        edgeHideAll.addEventListener('click', function(){ panels.forEach((p, i)=>{ if (pinned.includes(i)) return; p.classList.add('hidden-panel'); if(!hidden.includes(i)) hidden.push(i); }); saveHidden(); setEdgeOpen(false); });

        edgeExportLayout.addEventListener('click', function(){
            const state = {
                hidden: JSON.parse(localStorage.getItem(HIDDEN_KEY) || '[]'),
                pinned: JSON.parse(localStorage.getItem(PINNED_KEY) || '[]'),
                intervals: JSON.parse(localStorage.getItem(INTERVALS_KEY) || '{}'),
                edge_open: localStorage.getItem(OPEN_KEY) || '0'
            };
            const json = JSON.stringify(state);
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(json).then(()=>{ alert('Layout copied to clipboard'); }).catch(()=>{ prompt('Copy layout JSON', json); });
            } else {
                prompt('Copy layout JSON', json);
            }
            // also offer to save to server if logged in
            // open modal and pre-fill name
            openLayoutModal();
            document.getElementById('layoutNameInput').value = '';
            document.getElementById('layoutNameInput').dataset.pendingJson = json;
            setEdgeOpen(false);
        });

        edgeImportLayout.addEventListener('click', function(){
            openLayoutModal(true);
            setEdgeOpen(false);
        });

        // Modal functions
        const layoutModal = document.getElementById('layoutModal');
        const layoutsList = document.getElementById('layoutsList');
        function openLayoutModal(forImportOnly){
            layoutModal.classList.add('open');
            layoutModal.setAttribute('aria-hidden','false');
            document.getElementById('layoutModalTitle').textContent = forImportOnly ? 'Import / Manage Layouts' : 'Save / Manage Layouts';
            document.getElementById('layoutNameInput').style.display = forImportOnly ? 'none' : 'inline-block';
            document.getElementById('saveLayoutBtn').style.display = forImportOnly ? 'none' : 'inline-block';
            fetch('layouts.php').then(r=>r.json()).then(j=>{
                layoutsList.innerHTML = '';
                if (!j.ok || !j.layouts || j.layouts.length === 0) { layoutsList.textContent = 'No saved layouts'; return; }
                for (const l of j.layouts) {
                    const row = document.createElement('div'); row.className = 'layout-row';
                    const name = document.createElement('div'); name.className = 'layout-name'; name.textContent = l.name + ' ('+l.created_at+')';
                    const loadBtn = document.createElement('button'); loadBtn.textContent = 'Load';
                    const delBtn = document.createElement('button'); delBtn.textContent = 'Delete'; delBtn.style.background='#b91c1c';
                    const exportBtn = document.createElement('button'); exportBtn.textContent = 'Export'; exportBtn.style.background='#0f172a';
                    row.appendChild(name); row.appendChild(loadBtn); row.appendChild(exportBtn); row.appendChild(delBtn);
                    layoutsList.appendChild(row);
                    loadBtn.addEventListener('click', function(){ fetch('layouts.php?id='+encodeURIComponent(l.id)).then(r=>r.json()).then(res=>{ if (!res.ok) { alert('Load failed'); return; } const obj = JSON.parse(res.layout.json); if (obj.hidden) localStorage.setItem(HIDDEN_KEY, JSON.stringify(obj.hidden)); if (obj.pinned) localStorage.setItem(PINNED_KEY, JSON.stringify(obj.pinned)); if (obj.intervals) localStorage.setItem(INTERVALS_KEY, JSON.stringify(obj.intervals)); if (typeof obj.edge_open !== 'undefined') localStorage.setItem(OPEN_KEY, obj.edge_open); alert('Layout loaded — reloading'); location.reload(); }); });
                    delBtn.addEventListener('click', function(){ if (!confirm('Delete layout "'+l.name+'"?')) return; fetch('layouts.php?action=delete', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ id: l.id, csrf: MITAS_CSRF })}).then(r=>r.json()).then(res=>{ if (res.ok) { row.remove(); alert('Deleted'); } else alert('Delete failed'); }); });
                    exportBtn.addEventListener('click', function(){ fetch('layouts.php?id='+encodeURIComponent(l.id)).then(r=>r.json()).then(res=>{ if (!res.ok) { alert('Failed'); return; } const blob = new Blob([res.layout.json], {type:'application/json'}); const url = URL.createObjectURL(blob); const a = document.createElement('a'); a.href = url; a.download = 'layout_'+l.id+'.json'; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url); }); });
                }
            });
        }

        document.getElementById('closeLayoutModal').addEventListener('click', function(){ layoutModal.classList.remove('open'); layoutModal.setAttribute('aria-hidden','true'); });
        document.getElementById('saveLayoutBtn').addEventListener('click', function(){ const name = document.getElementById('layoutNameInput').value.trim(); const pending = document.getElementById('layoutNameInput').dataset.pendingJson || null; const json = pending || JSON.stringify({ hidden: JSON.parse(localStorage.getItem(HIDDEN_KEY)||'[]'), pinned: JSON.parse(localStorage.getItem(PINNED_KEY)||'[]'), intervals: JSON.parse(localStorage.getItem(INTERVALS_KEY)||'{}'), edge_open: localStorage.getItem(OPEN_KEY) || '0' }); if (!name) { alert('Enter a name'); return; } fetch('layouts.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ action:'save', name: name, json: json, csrf: MITAS_CSRF }) }).then(r=>r.json()).then(j=>{ if (j.ok) { alert('Saved'); openLayoutModal(); document.getElementById('layoutNameInput').dataset.pendingJson = ''; } else alert('Save failed'); }); });

        // init auto-refresh
        setupAutoRefresh();

    })();
    </script>
</body>
</html>
