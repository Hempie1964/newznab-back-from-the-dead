<?php
// Check if config.php exists and include it
if (is_file("config.php")) {
    require_once("config.php");
} else {
    if (is_dir("install")) {
        $protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

        $location = sprintf("location: %s%s%s/install", $protocol, $host, $uri);
        header($location);
        exit();
    }
}

// Re-check and include config.php if it was not already included
require_once("config.php");

// Start the session if it hasn't been started already
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize page and users objects
require_once(WWW_DIR."/lib/page.php");
require_once(WWW_DIR."/lib/users.php");

$page = new Page;
// === DEBUG: Track all included templates ===
$included_templates = [];
$page->smarty->registerPlugin('function', 'include', function($params, $smarty) use (&$included_templates) {
    if (isset($params['file'])) {
        $included_templates[] = $params['file'];
    }
    return call_user_func_array([$smarty, 'include'], func_get_args());
}, true);

$page->smarty->assign('included_templates', $included_templates);
$users = new Users;

// Initialize role ID globally
$roleID = isset($_SESSION['role.ID']) ? $_SESSION['role.ID'] : null;

// Assign roleID to Smarty if it's available
if (isset($page->smarty)) {
    $page->smarty->assign('roleID', $roleID);
}

// Debugging: Check session data and roleID
//echo '<pre>';
//print_r($_SESSION); // This will output the contents of the $_SESSION array
//echo 'Role ID: ' . htmlspecialchars($roleID); // Display roleID for debugging
//echo '</pre>';

// Load the page content
if ($page->template != "default" && file_exists(WWW_DIR.'pages/'.$page->template."/".$page->page.'.php')) {
    include(WWW_DIR.'pages/'.$page->template."/".$page->page.'.php');
} elseif (file_exists(WWW_DIR.'pages/'.$page->page.'.php')) {
    include(WWW_DIR.'pages/'.$page->page.'.php');
} else {
    $page->show404();
}
