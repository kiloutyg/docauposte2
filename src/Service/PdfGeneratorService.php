<?php

namespace App\Service;

use TCPDF;
use Picqer\Barcode\BarcodeGeneratorPNG;
// use Picqer\Barcode\BarcodeGenerator;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

use TomasVotruba\BarcodeBundle\Base2DBarcode;
// use TomasVotruba\BarcodeBundle\Generator\BarcodeGenerator;

class PdfGeneratorService
{



    public function generateOperatorPdf($operator)
    {
        // Create a new PDF document
        $pdf = new TCPDF();

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Your Company');
        $pdf->SetTitle('Operator Details');
        $pdf->SetSubject('Operator Information');

        // Set default header and footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', 'B', 16);

        // Add operator details
        $pdf->Cell(0, 10, 'Full Name: ' . $operator->getName(), 0, 1);
        $pdf->Cell(0, 10, 'Code: ' . $operator->getCode(), 0, 1);
        $pdf->Cell(0, 10, 'UAP: ' . ($operator->getUap() ? $operator->getUap()->getName() : 'N/A'), 0, 1);
        $pdf->Cell(0, 10, 'Team: ' . ($operator->getTeam() ? $operator->getTeam()->getName() : 'N/A'), 0, 1);

        // Generate Code128 barcode
        $generator = new BarcodeGeneratorPNG();
        $barcode = $generator->getBarcode($operator->getCode(), $generator::TYPE_CODE_128);
        $barcodePath = sys_get_temp_dir() . '/barcode.png';
        file_put_contents($barcodePath, $barcode);

        // Add barcode to PDF
        $pdf->Image($barcodePath, 10, 50, 100, 20);

        // Generate Data Matrix barcode using endroid/qr-code
        // $qrCode = new QrCode($operator->getCode());
        // $writer = new PngWriter();
        // $dataMatrix = $writer->write($qrCode);
        // $dataMatrixPath = sys_get_temp_dir() . '/datamatrix.png';
        // file_put_contents($dataMatrixPath, $dataMatrix->getString());

        // Generate Data Matrix barcode
        $dataMatrix = new Base2DBarcode();

        $dataMatrix->savePath = sys_get_temp_dir() . '/';


        $dataMatrixPath = $dataMatrix->getBarcodePNGPath($operator->getCode(), 'DATAMATRIX', 10, 10);

        // Add Data Matrix barcode to PDF
        $pdf->Image($dataMatrixPath, 10, 80, 100, 100);

        // Output the PDF
        return $pdf->Output('S');
    }
}
