<?php
if ($auth!='auth') {die();};

define("DB_SERVER", "localhost");
define("DB_USER", "");
define("DB_PASS", "");
define("DB_NAME", "boxshop");
define("NEWUSERAUTH", "2348");
define("TBL_USERS", "users");
define("ADMIN_NAME", "admin");
define("GUEST_NAME", "Guest");
define("ADMIN_LEVEL", 9);
define("TRUSTED_LEVEL", 2);
define("USER_LEVEL",  1);
define("GUEST_LEVEL", 0);
define("TRACK_VISITORS", true);

/**
 * Timeout Constants - these constants refer to
 * the maximum amount of time (in minutes) after
 * their last page fresh that a user and guest
 * are still considered active visitors.
 */
define("USER_TIMEOUT", 60*48);
define("GUEST_TIMEOUT", 5);
define("COOKIE_EXPIRE", 60*60*24*100);  //100 days by default
define("COOKIE_PATH", "/");  //Avaible in whole domain

/**
 * Email Constants - these specify what goes in
 * the from field in the emails that the script
 * sends to users, and whether to send a
 * welcome email to newly registered users.
 */
define("EMAIL_FROM_NAME", "Boxshop Alarm");
define("EMAIL_FROM_ADDR", "waywardengineer@gmail.com");
define("EMAIL_WELCOME", true);

/**
 * This constant forces all users to have
 * lowercase usernames, capital letters are
 * converted automatically.
 */
define("ALL_LOWERCASE", false);
?>
