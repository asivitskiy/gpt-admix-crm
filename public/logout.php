<?php
declare(strict_types=1);

require __DIR__ . '/../app/inc/bootstrap.php';

use App\Core\Auth;

Auth::logout();
header('Location: login.php');
exit;
