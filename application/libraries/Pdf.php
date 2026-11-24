<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PDF Library
 * Simple PDF generator menggunakan HTML dengan print CSS
 */
class Pdf {
    
    private $html = '';
    private $orientation = 'P';
    private $paper = 'A4';
    
    public function __construct() {
        // Constructor
    }
    
    public function load_html($html) {
        $this->html = $html;
    }
    
    public function set_paper($size, $orientation = 'portrait') {
        $this->paper = $size;
        $this->orientation = $orientation === 'portrait' ? 'P' : 'L';
    }
    
    public function render() {
        // HTML sudah di-set, siap untuk output
    }
    
    public function stream($filename, $options = array()) {
        $attachment = isset($options['Attachment']) ? $options['Attachment'] : 0;
        
        // Output HTML dengan print CSS untuk browser print/save as PDF
        $html_output = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($filename) . '</title>
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 1.5cm;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            margin: 0;
            padding: 20px;
            line-height: 1.5;
        }
        .print-button {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 1000;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .print-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Cetak / Simpan sebagai PDF
    </button>
' . $this->html . '
</body>
</html>';
        
        // Set header untuk HTML (bukan PDF, karena kita gunakan browser print)
        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Disposition: inline; filename="' . str_replace('.pdf', '.html', $filename) . '"');
        
        echo $html_output;
        exit;
    }
}

