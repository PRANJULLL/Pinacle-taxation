<?php
/**
 * TEMPORARY diagnostic script — delete this file after you're done debugging.
 *
 * Upload this to your live server and visit it TWICE in a row in your browser:
 *   1st visit: it sets a counter in the session and shows you info.
 *   2nd visit: if the counter went from 1 to 2, sessions are working.
 *              if it's stuck at 1 forever, sessions are NOT persisting.
 */

$sessionPath = __DIR__ . '/.sessions';
if (!is_dir($sessionPath)) {
    @mkdir($sessionPath, 0700, true);
}
if (is_dir($sessionPath) && is_writable($sessionPath)) {
    session_save_path($sessionPath);
}
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

$_SESSION['hits'] = ($_SESSION['hits'] ?? 0) + 1;

header('Content-Type: text/plain');
echo "Session ID: " . session_id() . "\n";
echo "Hits this session (refresh the page, this should go up each time): " . $_SESSION['hits'] . "\n";
echo "Configured session.save_path: " . session_save_path() . "\n";
echo "Is that path writable?: " . (is_writable(session_save_path()) ? 'YES' : 'NO') . "\n";
echo "Cookies received by server: " . json_encode($_COOKIE) . "\n";
echo "HTTPS?: " . (isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'not set') . "\n";
echo "\nIf 'Hits' goes up on every refresh -> sessions are working, your login should work now.\n";
echo "If 'Hits' is always 1 -> sessions still aren't persisting, see notes below.\n";
echo "If 'Cookies received by server' is empty/{} on the 2nd request -> the browser isn't sending the cookie back (check domain/HTTPS mismatch).\n";
