<?php
declare(strict_types=1);

require __DIR__.'/../../vendor/autoload.php';

use App\Core\Helpers;
use App\Core\Config;
use App\Core\Db;

Helpers::loadEnv(__DIR__.'/../../.env');
$config = Config::load();
$pdo = Db::pdo($config['db']);

$pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(190) NOT NULL,
  applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uk_migrations_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

$files = glob(__DIR__.'/*.sql');
sort($files);

foreach ($files as $file) {
    $name = basename($file);

    $st = $pdo->prepare("SELECT 1 FROM migrations WHERE name=? LIMIT 1");
    $st->execute([$name]);
    if ($st->fetchColumn()) {
        echo "[skip] $name\n";
        continue;
    }

    echo "[run ] $name\n";
    $sql = file_get_contents($file);

    $pdo->beginTransaction();
    try {
        $pdo->exec($sql);
        $ins = $pdo->prepare("INSERT INTO migrations(name) VALUES(?)");
        $ins->execute([$name]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        echo "[FAIL] $name: ".$e->getMessage()."\n";
        exit(1);
    }
}

echo "Done.\n";
