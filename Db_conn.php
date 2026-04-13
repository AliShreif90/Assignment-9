cat > ~/public_html/assignment9/classes/Db_conn.php << 'ENDOFFILE' <?php
class Db_conn
{
    private static $conn = null;

    public static function getConnection()
    {
        if (self::$conn === null) {
            $host = 'localhost';
            $db = 'ashreif';
            $user = 'ashreif';
            $pass = 'uxMJ7r6ZKjo8WLw';
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$conn = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                throw new PDOException($e->getMessage(), (int) $e->getCode());
            }
        }
        return self::$conn;
    }
}
ENDOFFILE