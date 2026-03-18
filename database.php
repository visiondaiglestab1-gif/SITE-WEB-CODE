<?php
// config/database.php
class Database {
    private $host = 'sql103.infinityfree.com';
    private $db_name = 'if0_41372313_visiondaigles';
    private $username = 'if0_41372313';
    private $password = 'OnctionProph';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            echo "Erreur de connexion: " . $e->getMessage();
        }
        return $this->conn;
    }
}

// Fonction de débogage pour l'upload
function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);
    
    echo "<script>console.log('Debug: " . $output . "' );</script>";
}
?>