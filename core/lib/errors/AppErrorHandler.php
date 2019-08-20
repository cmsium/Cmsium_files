<?php
namespace Errors;


class AppErrorHandler extends ErrorHandler {
    protected $pageBuilder;
    protected $defaultErrorTemplate;

    public function __construct($pageBuilder, $defaultErrorTemplate, $logger = null){
        $this->logger = $logger;
        $this->pageBuilder = $pageBuilder;
        $this->defaultErrorTemplate = $defaultErrorTemplate;
    }

    public function formatPage(\Exception $e) {
        $template = $e->template ?? $this->defaultErrorTemplate;
        $page = $this->pageBuilder->build($template);
        return $page->with(['error' => $this->get($e)])->render();
    }
}