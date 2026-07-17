<?php
/**
 * Simple Native PHP XLSX Reader
 * Parses basic Excel sheets (.xlsx) using standard PHP extensions (ZipArchive, SimpleXML).
 */

class SimpleXlsxReader
{
    private $filename;
    private $sharedStrings = [];
    private $sheets = [];

    public function __construct($filename)
    {
        $this->filename = $filename;
        $this->parse();
    }

    private function parse()
    {
        $zip = new ZipArchive();
        if ($zip->open($this->filename) !== true) {
            throw new Exception("Unable to open XLSX file.");
        }

        // 1. Load shared strings
        if ($sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml')) {
            $xml = new SimpleXMLElement($sharedStringsXml);
            foreach ($xml->si as $val) {
                // If there's direct text element
                if (isset($val->t)) {
                    $this->sharedStrings[] = (string)$val->t;
                } else {
                    // Handle rich text elements
                    $text = "";
                    if (isset($val->r)) {
                        foreach ($val->r as $r) {
                            $text .= (string)$r->t;
                        }
                    }
                    $this->sharedStrings[] = $text;
                }
            }
        }

        // 2. Load sheets
        $sheetIndex = 1;
        while ($sheetXml = $zip->getFromName("xl/worksheets/sheet{$sheetIndex}.xml")) {
            $xml = new SimpleXMLElement($sheetXml);
            $rows = [];

            foreach ($xml->sheetData->row as $row) {
                $rowIndex = (int)$row['r'];
                $rowData = [];

                foreach ($row->c as $cell) {
                    $cellRef = (string)$cell['r'];
                    // Get column name (A, B, C...)
                    preg_match('/^[A-Z]+/', $cellRef, $matches);
                    $col = $matches[0];
                    $colIndex = self::columnLetterToIndex($col);

                    $val = isset($cell->v) ? (string)$cell->v : '';
                    $type = isset($cell['t']) ? (string)$cell['t'] : '';

                    if ($type === 's' && isset($this->sharedStrings[(int)$val])) {
                        $val = $this->sharedStrings[(int)$val];
                    }

                    $rowData[$colIndex] = $val;
                }

                // Fill empty columns
                if (!empty($rowData)) {
                    $maxIndex = max(array_keys($rowData));
                    for ($i = 0; $i <= $maxIndex; $i++) {
                        if (!isset($rowData[$i])) {
                            $rowData[$i] = '';
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
    }

    public function getRows($sheetName = "Sheet1")
    {
        return $this->sheets[$sheetName] ?? array_values($this->sheets)[0] ?? [];
    }

    private static function columnLetterToIndex($letter)
    {
        $len = strlen($letter);
        $index = 0;
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($letter[$i]) - 64);
        }
        return $index - 1;
    }
}
?>
