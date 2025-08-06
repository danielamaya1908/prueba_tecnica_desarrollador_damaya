<?php

class Conexion extends PDO
{
    private $nombre_de_base = 'railway';
    private $usuario = 'root';
    private $contrasena = 'EZMyZzGIlAZwsilQXTJknTHdWifnjaSN';
    private $host = 'turntable.proxy.rlwy.net'; // Usa este si estás trabajando desde tu máquina local
    private $puerto = '41358';
    private $con;

    public function __construct(){
        try {
            $dsn = "mysql:host={$this->host};port={$this->puerto};dbname={$this->nombre_de_base}";
            $this->con = new PDO($dsn, $this->usuario, $this->contrasena);
            $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e){
            echo "❌ Error de conexión: " . $e->getMessage();
        }
    }

    public function insert_simple($tabla, $values, $params, $data){
        $stmt = $this->con->prepare("INSERT INTO ".$tabla." ( ".$values." ) VALUES ( ".$params." )");
        if($stmt->execute($data)) {
            return $this->con->lastInsertId();
        } else {
            return '0';
        }
    }

    public function udpdate_simple($tabla, $values, $data){
        $sql = "UPDATE ".$tabla." SET ".$values." WHERE id = :id ";
        $stmt = $this->con->prepare($sql);
        return $stmt->execute($data);
    }

    public function udpdate_where($tabla, $values, $where, $data){
        $sql = "UPDATE " . $tabla . " SET " . $values . " WHERE " . $where;
        $stmt = $this->con->prepare($sql);
        return $stmt->execute($data);
    }

    public function select_where_simple($tabla, $where){
        try {
            $stmt = $this->con->query("SELECT * FROM " . $tabla . " WHERE " . $where);
            $data = $stmt->fetchAll();
            return $data;
        } catch (\Throwable $th) {
            return [];
        }
    }

    public function select_where_simple_prueba($tabla, $where){
        try {
            $stmt = $this->con->query("SELECT * FROM " . $tabla . " WHERE " . $where);
            return "SELECT * FROM " . $tabla . " WHERE " . $where;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}

// Instancia para probar conexión directamente
$conexion = new Conexion();
