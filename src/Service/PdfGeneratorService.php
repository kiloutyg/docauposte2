<?php

namespace App\Service;

use TCPDF;
use Picqer\Barcode\BarcodeGeneratorPNG;

use TomasVotruba\BarcodeBundle\Base2DBarcode;

class PdfGeneratorService
{



    public function generateOperatorPdf($operator)
    {
        // Create a new PDF document
        $pdf = new TCPDF();

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('OPmobility Exterior Langres');
        $pdf->SetTitle('Operator Details');
        $pdf->SetSubject('Operator Information');

        // Set default header and footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', 'B', 8);

        // Add operator Name
        $pdf->Cell(0, 1, '' . ($operator->getName() ? $operator->getName() : 'N/A'), 0, 1, 'C', false, '', 0, false, 'T', 'T');

        // Generate Code128 barcode
        $generator = new BarcodeGeneratorPNG();
        $barcode = $generator->getBarcode($operator->getCode(), $generator::TYPE_CODE_128);
        $barcodePath = sys_get_temp_dir() . '/barcode.png';
        file_put_contents($barcodePath, $barcode);

        // Add barcode to PDF
        $pdf->Image($barcodePath, 90, 14, 20, 8, '', '', 'M', false, 300, '', false, false, 0, false, false, false);

        // Generate Data Matrix barcode
        $dataMatrix = new Base2DBarcode();
        $dataMatrix->savePath = sys_get_temp_dir() . '/';
        $dataMatrixPath = $dataMatrix->getBarcodePNGPath($operator->getCode(), 'DATAMATRIX', 10, 10);

        // Add Data Matrix barcode to PDF
        $pdf->Image($dataMatrixPath, 112, 14, 8, 8, '', '', 'N', false, 300, '', false, false, 0, false, false, false);

        // Add operator code
        $pdf->Cell(0, 1, '' . ($operator->getCode() ? $operator->getCode() : 'N/A'), 0, 1, 'C', false, '', 0, false, 'T', 'T');

        // Output the PDF
        return $pdf->Output('OpeInfo_' . $operator->getName() . '.pdf', 'I');
    }
}
