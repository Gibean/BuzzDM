<?php
require_once __DIR__ . '/../Repository/LogRepository.php';

class LogService {
    private $repository;

    public function __construct() {
        $this->repository = new LogRepository();
    }

    public function getSystemLogs($filters, $page, $limit) {
        return $this->repository->getLogs($filters, $page, $limit);
    }
}
