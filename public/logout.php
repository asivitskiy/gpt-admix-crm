<?php
declare(strict_types=1);

require __DIR__ . '/../inc/bootstrap.php';

use App\Core\Auth;

Auth::logout();
header('Location: login.php');
exit;
