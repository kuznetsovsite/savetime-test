<?php
/**
 * Created by PhpStorm.
 * User: sk
 * Date: 1/9/18
 * Time: 4:14 PM
 */

namespace FileUpload\Repository;


class MysqlConnect
{
    protected $host;
    protected $user;
    protected $password;
    protected $db;
    protected $conn;

    public function __construct($host, $user, $password, $db)
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->db = $db;
    }


    /**
     * @return MysqlConnect
     * @throws \Exception
     */
    public function connect()
    {
        $mysqli = new \mysqli($this->host, $this->user, $this->password, $this->db);

        if ($mysqli->connect_error) {
            throw new \Exception("Db connect error:". $mysqli->connect_errno ." Message: ". $mysqli->connect_error);
        }

        $this->conn = $mysqli;

        return $this;
    }

    /**
     *
     * @return bool|null
     */
    public function close()
    {
        if (!empty($this->conn) && $this->conn instanceof \mysqli) {
            return $this->conn->close();
        }

        return null;
    }

    public function getDB()
    {
        return $this->db;
    }

    /**
     * @return \mysqli
     */
    public function getConnect()
    {
        return $this->conn;
    }
}