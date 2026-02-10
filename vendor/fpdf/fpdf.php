<?php
/**
 * Simple FPDF implementation for thermal receipt printing
 * Simplified version for ChardyMart POS
 */

class FPDF {
    protected $page;
    protected $y;
    protected $x;
    protected $lines;
    protected $fontSize;
    protected $fontFamily;
    
    public function __construct() {
        $this->page = 1;
        $this->y = 10;
        $this->x = 5;
        $this->lines = [];
        $this->fontSize = 10;
        $this->fontFamily = 'Courier';
    }
    
    public function AddPage() {
        // Start new page
    }
    
    public function SetFont($family, $style = '', $size = 10) {
        $this->fontFamily = $family;
        $this->fontSize = $size;
    }
    
    public function Cell($w, $h, $txt = '', $border = 0, $ln = 0, $align = 'L') {
        if ($align == 'C') {
            $txt = str_pad($txt, 40, ' ', STR_PAD_BOTH);
        } elseif ($align == 'R') {
            $txt = str_pad($txt, 40, ' ', STR_PAD_LEFT);
        }
        
        $this->lines[] = $txt;
        
        if ($ln == 1) {
            $this->Ln();
        }
    }
    
    public function Ln($h = 5) {
        $this->y += $h;
    }
    
    public function Output($dest = '', $name = '') {
        $content = implode("\n", $this->lines);
        
        if ($dest == 'D') {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $name . '"');
            header('Content-Length: ' . strlen($content));
            echo $content;
            exit;
        } elseif ($dest == 'I') {
            header('Content-Type: text/plain');
            echo $content;
            exit;
        }
        
        return $content;
    }
}
