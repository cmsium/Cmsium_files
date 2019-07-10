<?php
namespace App;
use DateTime;

class LinksManager {
    public $db;
    public $conn;
    public $dbTableName = "links";

    public $table;

    public $expiration_date = null;

    public function __construct($db = null, $table = null) {
        if ($db){
            $this->db = $db;
        }
        if ($table){
            $this->table = $table;
        }
    }

    public function setExpirationDate(DateTime $date) {
        $this->expiration_date = $date;
    }

    public function cleanExpiredLinks() {
        foreach ($this->table as $link_data){
            $expire = $this->expiration_date ?? new DateTime();
            $link = new Link($link_data, $this->table, $this->db);
            $link->cleanExpired($expire);
            unset($link);
        }

    }

}