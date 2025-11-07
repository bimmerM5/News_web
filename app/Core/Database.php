<?php
namespace App\Core;

class Database
{
    private static ?\PDO $pdo = null;

    public static function getConnection(): \PDO
    {
        if (self::$pdo === null) {
            $config = require __DIR__ . '/../Config/config.php';
            $db = $config['db'];
            $dsn = 'mysql:host=' . $db['host'] . ';port=' . $db['port'] . ';dbname=' . $db['name'] . ';charset=' . $db['charset'];
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ];
            try {
                self::$pdo = new \PDO($dsn, $db['user'], $db['pass'], $options);
            } catch (\PDOException $e) {
                $errorCode = $e->getCode();
                if ($errorCode == 2002 || strpos($e->getMessage(), 'refused') !== false) {
                    die('
                    <div style="font-family: Arial; padding: 20px; max-width: 600px; margin: 50px auto; border: 2px solid #dc3545; border-radius: 8px; background: #fff;">
                        <h2 style="color: #dc3545; margin-top: 0;">❌ Lỗi kết nối Database</h2>
                        <p><strong>MySQL chưa được khởi động!</strong></p>
                        <p>Vui lòng làm theo các bước sau:</p>
                        <ol>
                            <li>Mở <strong>XAMPP Control Panel</strong></li>
                            <li>Nhấn nút <strong>"Start"</strong> cho service <strong>MySQL</strong></li>
                            <li>Đợi cho đến khi MySQL hiển thị màu xanh (đang chạy)</li>
                            <li>Làm mới trang web này</li>
                        </ol>
                        <p style="color: #666; font-size: 14px; margin-top: 20px;">
                            <strong>Chi tiết lỗi:</strong><br>
                            <code style="background: #f5f5f5; padding: 5px; display: inline-block; margin-top: 5px;">' . htmlspecialchars($e->getMessage()) . '</code>
                        </p>
                    </div>
                    ');
                }
                throw $e;
            }
        }
        return self::$pdo;
    }
}
