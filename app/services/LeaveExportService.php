<?php

namespace app\services;

use app\database\Database;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

/**
 * Appends newly applied leaves to a shared Excel report.
 * Meant to be run on a schedule (every 5 minutes) rather than per-request,
 * so a slow/locked file write never blocks the leave application flow.
 */
class LeaveExportService
{
    private $exportDir;
    private $filePath;
    private $statePath;
    private $lockPath;

    public function __construct()
    {
        $this->exportDir = __DIR__ . '/../../storage/exports';
        $this->filePath  = $this->exportDir . '/leave_applications.xlsx';
        $this->statePath = $this->exportDir . '/.last_export_id';
        $this->lockPath  = $this->exportDir . '/.export.lock';
    }

    public function run()
    {
        if (!is_dir($this->exportDir)) {
            mkdir($this->exportDir, 0775, true);
        }

        $lockHandle = fopen($this->lockPath, 'c');
        if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
            echo "Export already running. Skipping this run.\n";
            return;
        }

        try {
            $lastId = $this->getLastExportedId();
            $rows = $this->fetchNewLeaves($lastId);

            if (empty($rows)) {
                echo "No new leave applications since id {$lastId}.\n";
                return;
            }

            $spreadsheet = $this->loadOrCreateSpreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $nextRow = $sheet->getHighestDataRow() + 1;

            $maxId = $lastId;
            foreach ($rows as $row) {
                $leaveDays = $this->calculateLeaveDays($row['from_date'], $row['to_date']);
                $applicantName = trim($row['first_name'] . ' ' . $row['last_name']);

                $sheet->setCellValue("A{$nextRow}", $applicantName);
                $sheet->setCellValue("B{$nextRow}", $row['department_name'] ?? 'N/A');
                $sheet->setCellValue("C{$nextRow}", $row['description'] ?? '');
                $sheet->setCellValue("D{$nextRow}", $leaveDays);
                $sheet->setCellValue("E{$nextRow}", date('Y-m-d H:i:s', strtotime($row['created_at'])));

                $nextRow++;
                $maxId = max($maxId, (int) $row['id']);
            }

            $this->autoSizeColumns($sheet);
            $this->saveSpreadsheetAtomically($spreadsheet);
            $this->setLastExportedId($maxId);

            echo "Exported " . count($rows) . " leave application(s) up to id {$maxId}.\n";
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }

    private function getLastExportedId()
    {
        if (!file_exists($this->statePath)) {
            return 0;
        }
        return (int) trim(file_get_contents($this->statePath));
    }

    private function setLastExportedId($id)
    {
        file_put_contents($this->statePath, (string) $id, LOCK_EX);
    }

    private function fetchNewLeaves($lastId)
    {
        $db = (new Database())->getConnection();

        $stmt = $db->prepare(
            "SELECT al.id, al.description, al.from_date, al.to_date, al.created_at,
                    u.first_name, u.last_name, d.name AS department_name
             FROM appliedleaves al
             JOIN users u ON u.id = al.applied_by
             LEFT JOIN departments d ON d.id = u.department_id
             WHERE al.id > ?
             ORDER BY al.id ASC"
        );
        $stmt->execute([$lastId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function calculateLeaveDays($fromDate, $toDate)
    {
        $from = new \DateTime($fromDate);
        $to = new \DateTime($toDate);
        return $from->diff($to)->days + 1;
    }

    private function loadOrCreateSpreadsheet()
    {
        if (file_exists($this->filePath)) {
            $reader = new XlsxReader();
            return $reader->load($this->filePath);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Leave Applications');
        $sheet->fromArray(
            ['Applicant Name', 'Department', 'Reason for Leave', 'Leave Days', 'Date Applied'],
            null,
            'A1'
        );
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);

        return $spreadsheet;
    }

    private function autoSizeColumns($sheet)
    {
        foreach (range('A', 'E') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    private function saveSpreadsheetAtomically(Spreadsheet $spreadsheet)
    {
        $tmpPath = $this->filePath . '.tmp';
        $writer = new XlsxWriter($spreadsheet);
        $writer->save($tmpPath);
        rename($tmpPath, $this->filePath);
    }
}
