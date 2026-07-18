<?php
/**
 * Simple Native PHP XLSX Reader
 * Parses basic Excel sheets (.xlsx) using ZipArchive + SimpleXML.
 * Falls back gracefully if the zip PHP extension is not enabled.
 */

class SimpleXlsxReader
{
    private $filename;
    private $sharedStrings = [];
    private $sheets        = [];

    public function __construct($filename)
    {
        $this->filename = $filename;
        $this->parse();
    }

    private function parse()
    {
        // ── Friendly check: zip extension must be enabled ──────────────────
        if (!class_exists('ZipArchive')) {
            throw new Exception(
                'The PHP <strong>zip</strong> extension is not enabled on this server. ' .
                'Please ask your administrator to enable <code>extension=zip</code> in ' .
                '<code>php.ini</code> and restart Apache.'
            );
        }

        $zip = new ZipArchive();
        if ($zip->open($this->filename) !== true) {
            throw new Exception(
                'Unable to open the uploaded Excel file. ' .
                'Make sure the file is a valid <strong>.xlsx</strong> spreadsheet and is not corrupted.'
            );
        }

        // ── 1. Load shared strings (text values stored by reference in xlsx) ──
        if ($sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml')) {
            $xml = simplexml_load_string($sharedStringsXml);
            if ($xml !== false) {
                foreach ($xml->si as $val) {
                    if (isset($val->t)) {
                        $this->sharedStrings[] = (string) $val->t;
                    } else {
                        $text = '';
                        if (isset($val->r)) {
                            foreach ($val->r as $r) {
                                $text .= isset($r->t) ? (string) $r->t : '';
                            }
                        }
                        $this->sharedStrings[] = $text;
                    }
                }
            }
        }

        // ── 2. Load worksheet rows ─────────────────────────────────────────
        $sheetIndex = 1;
        while ($sheetXml = $zip->getFromName("xl/worksheets/sheet{$sheetIndex}.xml")) {
            $xml = simplexml_load_string($sheetXml);
            if ($xml === false) {
                $sheetIndex++;
                continue;
            }

            $rows = [];
            foreach ($xml->sheetData->row as $row) {
                $rowIndex = (int) $row['r'];
                $rowData  = [];

                foreach ($row->c as $cell) {
                    $cellRef = (string) ($cell['r'] ?? '');
                    if (empty($cellRef)) continue;

                    preg_match('/^[A-Z]+/', $cellRef, $matches);
                    if (empty($matches)) continue;

                    $col      = $matches[0];
                    $colIndex = self::columnLetterToIndex($col);

                    $val  = isset($cell->v) ? (string) $cell->v : '';
                    $type = isset($cell['t']) ? (string) $cell['t'] : '';

                    // Resolve shared string reference
                    if ($type === 's') {
                        $idx = (int) $val;
                        $val = $this->sharedStrings[$idx] ?? '';
                    }

                    $rowData[$colIndex] = $val;
                }

                if (!empty($rowData)) {
                    $maxIndex = max(array_keys($rowData));
                    for ($i = 0; $i <= $maxIndex; $i++) {
                        if (!isset($rowData[$i])) {
                            $rowData[$i] = ''; // fill gaps with empty string
                        }
                    }
                    ksort($rowData);
                    $rows[$rowIndex] = $rowData;
                }
            }

            $this->sheets["Sheet{$sheetIndex}"] = $rows;
            $sheetIndex++;
        }

        $zip->close();

        if (empty($this->sheets)) {
            throw new Exception(
                'No readable sheets found in the uploaded Excel file. ' .
                'Make sure the file has at least one data sheet with content.'
            );
        }
    }

    public function getRows($sheetName = 'Sheet1')
    {
        return $this->sheets[$sheetName]
            ?? (count($this->sheets) > 0 ? array_values($this->sheets)[0] : []);
    }

    private static function columnLetterToIndex($letter)
    {
        $index = 0;
        $len   = strlen($letter);
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($letter[$i]) - 64);
        }
        return $index - 1;
    }
}
?>
