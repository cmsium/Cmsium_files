<?php
namespace App\Controllers;


use App\Status;

/**
 * @description Actions with file servers (get status ...)
 */
class StatusController {
    use \Router\Routable;

    /**
     * @summary" Get server status.
     * @description" Get server status: get availability, free space and  workload,
     */
    public function getStatus() {
        $status = new Status(SERVER_STATUS, STORAGE_PATH);
        return $status->get();
    }

}