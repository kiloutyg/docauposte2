<?php

namespace App\Service\Operator;

use TCPDF;
use Picqer\Barcode\BarcodeGeneratorPNG;

use TomasVotruba\BarcodeBundle\Base2DBarcode;

class PdfGeneratorService
{


    /**
     * Generates a PDF document containing operator details and barcodes.
     *
     * @param object $operator The operator object containing the necessary information.
     *
     * @return string The generated PDF content.
     */
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
        $pdf->SetFont(family: 'helvetica', style: 'B', size: 8);

        // Add operator Name
        $pdf->Cell(0, 1, '' . ($operator->getName() ? $operator->getName() : 'N/A'), 0, 1, 'C', false, '', 0, false, 'T', 'T');

        // Generate Code128 barcode
        $generator = new BarcodeGeneratorPNG();
        $barcode = $generator->getBarcode(barcode: $operator->getCode(), type: $generator::TYPE_CODE_128);
        $barcodePath = sys_get_temp_dir() . '/barcode.png';
        file_put_contents(filename: $barcodePath, data: $barcode);

        // Add barcode to PDF
        $pdf->Image(file: $barcodePath, x: 90, y: 14, w: 20, h: 8, type: '', link: '', align: 'M', resize: false, dpi: 300, palign: '', ismask: false, imgmask: false, border: 0, fitbox: false, hidden: false, fitonpage: false);

        // Generate Data Matrix barcode
        $dataMatrix = new Base2DBarcode();
        $dataMatrix->savePath = sys_get_temp_dir() . '/';
        $dataMatrixPath = $dataMatrix->getBarcodePNGPath($operator->getCode(), 'DATAMATRIX', 10, 10);

        // Add Data Matrix barcode to PDF
        $pdf->Image(file: $dataMatrixPath, x: 112, y: 14, w: 8, h: 8, type: '', link: '', align: 'N', resize: false, dpi: 300, palign: '', ismask: false, imgmask: false, border: 0, fitbox: false, hidden: false, fitonpage: false);

        // Add operator code
        $pdf->Cell(w: 0, h: 1, txt: '' . ($operator->getCode() ? $operator->getCode() : 'N/A'), border: 0, ln: 1, align: 'C', fill: false, link: '', stretch: 0, ignore_min_height: false, calign: 'T', valign: 'T');

        // Output the PDF
        return $pdf->Output('OpeInfo_' . $operator->getName() . '.pdf', 'I');
    }
}
