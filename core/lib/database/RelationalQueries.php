<?php

namespace DB;

use DB\Exceptions\RunQueryException;

trait RelationalQueries {

    public function select($query, array $params = null) {
        $result = $params 
            ? $this->performPreparedQueryFetchAll($query, $params)
            : $this->performQueryFetchAll($query);
        
        if ($result === false) {
            throw new RunQueryException;
        }
        
        return $result;
    }

    public function selectFirst($query) {
        $result = $this->performQueryFetch($query);
        
        if ($result === false) {
            throw new RunQueryException;
        }
        
        return $result;
    }

    public function insert($query, array $params = null) {
        $result = $params
            ? $this->performPreparedQuery($query, $params)
            : $this->performQuery($query);

        return $result;
    }

    public function update($query, array $params = null) {
        $result = $params
            ? $this->performPreparedQuery($query, $params)
            : $this->performQuery($query);

        return $result;
    }

    public function delete($query) {
        $result = $this->performQuery($query);

        return $result;
    }

    public function statement($query) {
        $result = $this->performQuery($query);

        return $result;
    }

}