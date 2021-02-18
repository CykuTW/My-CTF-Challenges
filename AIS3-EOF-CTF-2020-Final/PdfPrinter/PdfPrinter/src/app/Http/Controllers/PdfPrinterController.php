<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;

class PdfPrinterController extends Controller
{
    public function generate(Request $request) 
    {
        $url = $request->input('url');
        if ($this->isHttpURL($url)) {
            $html = file_get_contents($url);
            $pdf = PDF::loadHTML($html);
            $pdf->download('document.pdf');
        } else {
            return "<h3>Please provide a valid URL.</h3>";
        }
        
    }

    private function isHttpURL($url)
    {
        return $this->strStartsWith($url, 'http://') 
                || $this->strStartsWith($url, 'https://');
    }

    private function strStartsWith($string, $startString) 
    { 
        $len = strlen($startString); 
        return (substr($string, 0, $len) === $startString); 
    }
}
