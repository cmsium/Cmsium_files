<?php
namespace App;
use DateTime;

class LinksManager {
    public $table;

    public $expiration_date = null;

    public function __construct($table = null) {
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
            $link = new Link($link_data, $this->table);
            $link->cleanExpired($expire);
            unset($link);
        }

    }

    public function cleanDeletedFilesLinks() {
        $this->swooleCleanDeletedFilesLinks();
        $this->dbCleanDeletedFilesLinks();
    }

    public function swooleCleanDeletedFilesLinks() {
        $this->swooleDeleteLinks($this->dbGetDeletedFilesLinks());
    }

    public function swooleDeleteLinks($links_ids) {
        foreach ($links_ids as $link_data){
            $link = new Link($link_data, $this->table);
            $link->swooleDelete();
            unset($link);
        }
    }

    public function dbGetDeletedFilesLinks() {
        $db = new \DB\SwooleMysqlConnection();
        $result = $db->select("SELECT hash from links INNER JOIN files ON links.file = files.file_id WHERE files.is_delete = 1;");
        unset($db);
        return $result;
    }

    public function dbCleanDeletedFilesLinks() {
        $db = new \DB\SwooleMysqlConnection();
        $result = $db->delete("DELETE links from links INNER JOIN files ON links.file = files.file_id WHERE files.is_delete = 1;");
        unset($db);
        return $result;
    }

}