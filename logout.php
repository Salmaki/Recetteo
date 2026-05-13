<?php
/**
 * logout.php
 * تسجيل الخروج وإنهاء الجلسة
 */
session_start();
session_unset();
session_destroy();

header('Location: index.php');
exit;
