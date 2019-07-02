<?php
namespace App\Controllers;


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
        $status = true;
        $space = disk_free_space(ROOTDIR . '/storage');
        $connections = app()->connections;
        return ['status' => $status, 'space' => $space, 'workload' => $connections];
    }

}