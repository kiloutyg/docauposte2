<?php

namespace App\Service;

use TCPDF;
use Picqer\Barcode\BarcodeGeneratorPNG;

use TomasVotruba\BarcodeBundle\Base2DBarcode;

class PdfGeneratorService
{

    /**
     * Generates a PDF document containing operator information with barcodes.
     *
     * This function creates a PDF with the operator's name, a Code128 barcode,
     * and a Data Matrix barcode representing the operator's code.
     *
     * @param object $operator The operator object containing name and code information
     *                         Must have getName() and getCode() methods
     *
     * @return string The generated PDF document as a string, sent to the browser
     *                with filename 'OpeInfo_[operator name].pdf'
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

        // // Generate Code128 barcode
        // $generator = new BarcodeGeneratorPNG();
        // $barcode = $generator->getBarcode(barcode: $operator->getCode(), type: $generator::TYPE_CODE_128);
        // $barcode128Path = sys_get_temp_dir() . '/barcode128.png';
        // file_put_contents(filename: $barcode128Path, data: $barcode);

        // // Generate Code128A barcode
        // $generator = new BarcodeGeneratorPNG();
        // $barcode = $generator->getBarcode(barcode: $operator->getCode(), type: $generator::TYPE_CODE_128_A);
        // $barcode128APath = sys_get_temp_dir() . '/barcode128A.png';
        // file_put_contents(filename: $barcode128APath, data: $barcode);

        // // Generate Code128B barcode
        // $generator = new BarcodeGeneratorPNG();
        // $barcode = $generator->getBarcode(barcode: $operator->getCode(), type: $generator::TYPE_CODE_128_B);
        // $barcode128BPath = sys_get_temp_dir() . '/barcode128B.png';
        // file_put_contents(filename: $barcode128BPath, data: $barcode);

        // // Generate Code128C barcode
        // $generator = new BarcodeGeneratorPNG();
        // $code = '0' . $operator->getCode();
        // $barcode = $generator->getBarcode(barcode: $code, type: $generator::TYPE_CODE_128_C);
        // $barcode128CPath = sys_get_temp_dir() . '/barcode128C.png';
        // file_put_contents(filename: $barcode128CPath, data: $barcode);

        // Add barcode to PDF
        $pdf->Image(file: $barcodePath, x: 90, y: 14, w: 20, h: 8, type: '', link: '', align: 'M', resize: false, dpi: 300, palign: '', ismask: false, imgmask: false, border: 0, fitbox: false, hidden: false, fitonpage: false);
        // // Set initial X position (left margin)
        // $x = 10;

        // // Set Y positions for each image
        // $y1 = 50;  // Y position for first image
        // $y2 = 100; // Y position for second image
        // $y3 = 150; // Y position for third image
        // $y4 = 200; // Y position for fourth image

        // // Set image width and height
        // $width = 50; // Width of each barcode image in mm
        // $height = 20; // Height of each barcode image in mm

        // // Add barcode images to PDF in a single column at different heights
        // $pdf->Image(file: $barcode128Path, x: $x, y: $y1, w: $width, h: $height);
        // $pdf->Image(file: $barcode128APath, x: $x, y: $y2, w: $width, h: $height);
        // $pdf->Image(file: $barcode128BPath, x: $x, y: $y3, w: $width, h: $height);
        // $pdf->Image(file: $barcode128CPath, x: $x, y: $y4, w: $width, h: $height);


        // Generate Data Matrix barcode
        $dataMatrix = new Base2DBarcode();
        $dataMatrix->savePath = sys_get_temp_dir() . '/';
        $dataMatrixPath = $dataMatrix->getBarcodePNGPath($operator->getCode(), 'DATAMATRIX', 10, 10);

        // Add Data Matrix barcode to PDF
        $pdf->Image(file: $dataMatrixPath, x: 112, y: 14, w: 8, h: 8, type: '', link: '', align: 'N', resize: false, dpi: 300, palign: '', ismask: false, imgmask: false, border: 0, fitbox: false, hidden: false, fitonpage: false);
        // $pdf->Image(file: $dataMatrixPath);

        // Add operator code
        // $pdf->Cell(0, 1, '' . ($operator->getCode() ? $operator->getCode() : 'N/A'), 0, 1, 'C', false, '', 0, false, 'T', 'T');
        $pdf->Cell(w: 0, h: 1, txt: '' . ($operator->getCode() ? $operator->getCode() : 'N/A'), border: 0, ln: 1, align: 'C', fill: false, link: '', stretch: 0, ignore_min_height: false, calign: 'T', valign:'T');

        // Output the PDF
        return $pdf->Output('OpeInfo_' . $operator->getName() . '.pdf', 'I');
    }
}