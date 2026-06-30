<?php
// includes/invoice_generator.php
require_once __DIR__ . '/../libs/fpdf/fpdf.php';

class InvoicePDF extends FPDF {
    // Custom footer if needed (currently simple)
    function Footer() {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, 'This is a computer generated invoice and does not require signature.', 0, 0, 'C');
    }
}

/**
 * Generate Invoice PDF using FPDF
 * @param array $data Invoice details
 * @return string Filepath to generated PDF
 */
function generateInvoicePDF($data) {
    $invoicesDir = __DIR__ . '/../invoices';
    if (!file_exists($invoicesDir)) {
        mkdir($invoicesDir, 0755, true);
    }
    
    $fileName = $data['invoiceNumber'] . '.pdf';
    $filePath = $invoicesDir . '/' . $fileName;

    // Bank Details from constants
    $BANK_DETAILS = [
        'bankName' => 'Bank of Baroda',
        'accountName' => 'PINNACLE ACCOUNTING SERVICES',
        'accountType' => 'CURRENT ACCOUNT',
        'accountNumber' => '21200200000335',
        'ifsc' => 'BARB0TRDJIW',
        'upiId' => '9467362705@ptaxis',
        'payeeName' => 'Pinnacle Accounting Services',
    ];

    // Build UPI URL for QR
    $upiUrl = "upi://pay?pa=" . $BANK_DETAILS['upiId'] . 
             "&pn=" . urlencode($BANK_DETAILS['payeeName']) . 
             "&am=" . $data['amount'] . 
             "&cu=INR" . 
             "&tn=" . urlencode("Invoice " . $data['invoiceNumber']);

    // Fetch QR Code image from API to a local temp file
    $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($upiUrl);
    $tempQrFile = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
    
    // Download QR Code with timeout to prevent hanging
    $ctx = stream_context_create(['http' => ['timeout' => 5]]);
    $qrData = @file_get_contents($qrUrl, false, $ctx);
    if ($qrData) {
        file_put_contents($tempQrFile, $qrData);
    } else {
        $tempQrFile = '';
    }

    // Initialize FPDF: A4 size, Portrait, Millimeters
    $pdf = new InvoicePDF('P', 'mm', 'A4');
    $pdf->SetMargins(15, 15, 15);
    $pdf->AddPage();

    // Color Palette Definition
    $primaryNavy = [15, 76, 129];      // #0f4c81
    $secondarySteel = [30, 136, 229];  // #1e88e5
    $darkCharcoal = [44, 62, 80];      // #2c3e50
    $mutedSlate = [127, 140, 141];     // #7f8c8d
    $lightGrey = [248, 250, 252];      // #f8fafc
    $borderGrey = [226, 232, 240];     // #e2e8f0

    // Set Default Line Width & Draw Color
    $pdf->SetDrawColor($borderGrey[0], $borderGrey[1], $borderGrey[2]);
    $pdf->SetLineWidth(0.3);

    // 1. Logo & Header Layout
    $logoPath = __DIR__ . '/../assets/logo.png';
    if (file_exists($logoPath)) {
        $pdf->Image($logoPath, 15, 15, 22, 22);
    } else {
        $pdf->SetDrawColor($primaryNavy[0], $primaryNavy[1], $primaryNavy[2]);
        $pdf->SetLineWidth(1);
        $pdf->Rect(15, 15, 22, 22, 'D');
        $pdf->SetLineWidth(0.3);
        $pdf->SetDrawColor($borderGrey[0], $borderGrey[1], $borderGrey[2]);
    }

    // Brand Name and Subtitle (Single Line)
    $pdf->SetXY(41, 17);
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetTextColor($primaryNavy[0], $primaryNavy[1], $primaryNavy[2]);
    $pdf->Cell(120, 7, 'Pinnacle Accounting & Taxation', 0, 1, 'L');
    $pdf->SetX(41);
    $pdf->SetFont('Arial', 'I', 8.5);
    $pdf->SetTextColor($mutedSlate[0], $mutedSlate[1], $mutedSlate[2]);
    $pdf->Cell(120, 4.5, 'Your trust our commitment', 0, 0, 'L');

    // Right-Aligned Company Address/Contact Info
    $pdf->SetXY(110, 18);
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetTextColor($mutedSlate[0], $mutedSlate[1], $mutedSlate[2]);
    $pdf->Cell(85, 4, 'Email: FileYourITR007@gmail.com', 0, 1, 'R');
    $pdf->SetX(110);
    $pdf->Cell(85, 4, 'Phone: +91 94673-62705', 0, 1, 'R');

    // Header Divider Line
    $pdf->SetDrawColor($primaryNavy[0], $primaryNavy[1], $primaryNavy[2]);
    $pdf->SetLineWidth(0.8);
    $pdf->Line(15, 42, 195, 42);
    $pdf->SetDrawColor($borderGrey[0], $borderGrey[1], $borderGrey[2]);
    $pdf->SetLineWidth(0.3);

    // 2. Billing details vs Invoice info (Side-by-Side)
    // Left: Bill To
    $pdf->SetXY(15, 48);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor($mutedSlate[0], $mutedSlate[1], $mutedSlate[2]);
    $pdf->Cell(100, 4, 'BILL TO', 0, 1, 'L');
    
    $pdf->SetX(15);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetTextColor($primaryNavy[0], $primaryNavy[1], $primaryNavy[2]);
    $pdf->Cell(100, 6, strtoupper($data['customerName']), 0, 1, 'L');
    
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor($darkCharcoal[0], $darkCharcoal[1], $darkCharcoal[2]);
    
    if (!empty($data['pan'])) {
        $pdf->SetX(15);
        $pdf->Cell(100, 4.5, 'PAN Card: ' . strtoupper($data['pan']), 0, 1, 'L');
    }
    if (!empty($data['phone'])) {
        $pdf->SetX(15);
        $pdf->Cell(100, 4.5, 'Phone: ' . $data['phone'], 0, 1, 'L');
    }
    if (!empty($data['email'])) {
        $pdf->SetX(15);
        $pdf->Cell(100, 4.5, 'Email: ' . $data['email'], 0, 1, 'L');
    }
    if (!empty($data['client'])) {
        $pdf->SetX(15);
        $pdf->Cell(100, 4.5, 'Client Org: ' . $data['client'], 0, 1, 'L');
    }

    // Right: Invoice Info
    $pdf->SetXY(120, 47);
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->SetTextColor($primaryNavy[0], $primaryNavy[1], $primaryNavy[2]);
    $pdf->Cell(75, 7, 'INVOICE', 0, 1, 'R');

    $pdf->SetTextColor($darkCharcoal[0], $darkCharcoal[1], $darkCharcoal[2]);
    $pdf->SetXY(120, 56);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(45, 5, 'Invoice No:', 0, 0, 'R');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(30, 5, ' ' . $data['invoiceNumber'], 0, 1, 'L');

    $pdf->SetXY(120, 61);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(45, 5, 'Date:', 0, 0, 'R');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(30, 5, ' ' . date('d/m/Y', strtotime($data['date'])), 0, 1, 'L');

    if (!empty($data['orderId'])) {
        $pdf->SetXY(120, 66);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(45, 5, 'Order ID:', 0, 0, 'R');
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(30, 5, ' ' . $data['orderId'], 0, 1, 'L');
    }

    // 3. Charges Table
    $tableTop = 78;
    $col1Width = 15;   // SR.
    $col2Width = 120;  // Description
    $col3Width = 45;   // Amount

    // Table Header
    $pdf->SetXY(15, $tableTop);
    $pdf->SetFillColor($primaryNavy[0], $primaryNavy[1], $primaryNavy[2]);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 9.5);
    
    // Draw cells
    $pdf->Cell($col1Width, 8, 'SR.', 1, 0, 'C', true);
    $pdf->Cell($col2Width, 8, 'Description', 1, 0, 'L', true);
    $pdf->Cell($col3Width, 8, 'Amount (INR)', 1, 1, 'R', true);

    // Table Body rows
    $rowHeight = 50;
    $contentY = $pdf->GetY();

    // Reset text color
    $pdf->SetTextColor($darkCharcoal[0], $darkCharcoal[1], $darkCharcoal[2]);

    // Bounding vertical grid lines
    $pdf->Rect(15, $contentY, $col1Width, $rowHeight);
    $pdf->Rect(15 + $col1Width, $contentY, $col2Width, $rowHeight);
    $pdf->Rect(15 + $col1Width + $col2Width, $contentY, $col3Width, $rowHeight);

    // SR.
    $pdf->SetXY(15, $contentY + 4);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($col1Width, 6, '1.', 0, 0, 'C');

    // Description text
    $pdf->SetXY(15 + $col1Width + 4, $contentY + 4);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor($primaryNavy[0], $primaryNavy[1], $primaryNavy[2]);
    $pdf->Cell(0, 6, 'Towards Professional Charges for -', 0, 1, 'L');
    
    $pdf->SetX(15 + $col1Width + 4);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetTextColor($darkCharcoal[0], $darkCharcoal[1], $darkCharcoal[2]);
    $pdf->Cell(0, 6, $data['plan'], 0, 1, 'L');

    $pdf->SetFont('Arial', '', 9.5);
    $pdf->SetTextColor($mutedSlate[0], $mutedSlate[1], $mutedSlate[2]);
    
    $pdf->SetX(15 + $col1Width + 4);
    $pdf->Cell(0, 5, 'PAN No: ' . ($data['pan'] ?: 'NA'), 0, 1, 'L');
    $pdf->SetX(15 + $col1Width + 4);
    $pdf->Cell(0, 5, 'Phone: ' . ($data['phone'] ?: 'NA'), 0, 1, 'L');
    $pdf->SetX(15 + $col1Width + 4);
    $pdf->Cell(0, 5, 'Email: ' . ($data['email'] ?: 'NA'), 0, 1, 'L');

    // Amount Value
    $pdf->SetXY(15 + $col1Width + $col2Width, $contentY + 4);
    $pdf->SetFont('Arial', 'B', 10.5);
    $pdf->SetTextColor($darkCharcoal[0], $darkCharcoal[1], $darkCharcoal[2]);
    $pdf->Cell($col3Width, 6, number_format($data['amount'], 2) . '/-', 0, 0, 'R');

    // Table Footer Row
    $totalY = $contentY + $rowHeight;
    $pdf->SetXY(15, $totalY);
    $pdf->SetFillColor($lightGrey[0], $lightGrey[1], $lightGrey[2]);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor($darkCharcoal[0], $darkCharcoal[1], $darkCharcoal[2]);
    
    $pdf->Cell($col1Width + $col2Width, 8, 'Total Amount Due (INR)', 1, 0, 'R', true);
    $pdf->SetTextColor($primaryNavy[0], $primaryNavy[1], $primaryNavy[2]);
    $pdf->Cell($col3Width, 8, 'INR ' . number_format($data['amount'], 2) . '/-', 1, 1, 'R', true);

    // 4. Payment Details Card
    $cardTop = $totalY + 14;
    $cardHeight = 48;

    // Outer Filled Card Box
    $pdf->SetFillColor($lightGrey[0], $lightGrey[1], $lightGrey[2]);
    $pdf->Rect(15, $cardTop, 180, $cardHeight, 'DF');

    // Header bar inside box
    $pdf->SetFillColor($primaryNavy[0], $primaryNavy[1], $primaryNavy[2]);
    $pdf->Rect(15, $cardTop, 180, 8, 'F');
    
    // Header text
    $pdf->SetXY(20, $cardTop + 2);
    $pdf->SetFont('Arial', 'B', 8.5);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(170, 4, 'PAYMENT DETAILS / BANK TRANSFER', 0, 1, 'L');

    // Bank Details text
    $pdf->SetTextColor($darkCharcoal[0], $darkCharcoal[1], $darkCharcoal[2]);
    $bankTextY = $cardTop + 10;
    
    $labels = [
        'BANK:' => strtoupper($BANK_DETAILS['bankName']),
        'ACCOUNT NAME:' => strtoupper($BANK_DETAILS['accountName']),
        'ACCOUNT TYPE:' => strtoupper($BANK_DETAILS['accountType']),
        'ACCOUNT NO:' => $BANK_DETAILS['accountNumber'],
        'IFSC CODE:' => $BANK_DETAILS['ifsc'],
    ];

    $currentY = $bankTextY;
    foreach ($labels as $lbl => $val) {
        $pdf->SetXY(20, $currentY);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor($mutedSlate[0], $mutedSlate[1], $mutedSlate[2]);
        $pdf->Cell(32, 5, $lbl, 0, 0, 'L');
        
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor($darkCharcoal[0], $darkCharcoal[1], $darkCharcoal[2]);
        if ($lbl === 'ACCOUNT TYPE:') {
            $pdf->SetFont('Arial', 'BI', 8);
        }
        $pdf->Cell(90, 5, $val, 0, 1, 'L');
        $currentY += 5.2;
    }

    // Divider vertical line inside payment box
    $pdf->SetDrawColor($borderGrey[0], $borderGrey[1], $borderGrey[2]);
    $pdf->Line(148, $cardTop + 8, 148, $cardTop + $cardHeight);

    // Embed QR code image if downloaded
    if ($tempQrFile && file_exists($tempQrFile)) {
        $pdf->Image($tempQrFile, 158, $cardTop + 10, 24, 24);
        unlink($tempQrFile); // Clean up temp file
    } else {
        // Draw elegant placeholder box
        $pdf->SetDrawColor(226, 232, 240);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect(158, $cardTop + 10, 24, 24, 'DF');
        $pdf->SetTextColor(127, 140, 141);
        $pdf->SetFont('Arial', 'I', 6.5);
        $pdf->SetXY(158, $cardTop + 18);
        $pdf->Cell(24, 3, 'QR Code', 0, 1, 'C');
        $pdf->SetXY(158, $cardTop + 21);
        $pdf->Cell(24, 3, 'Unavailable', 0, 1, 'C');
    }

    // QR Label
    $pdf->SetXY(148, $cardTop + 35);
    $pdf->SetFont('Arial', 'B', 7.5);
    $pdf->SetTextColor($primaryNavy[0], $primaryNavy[1], $primaryNavy[2]);
    $pdf->Cell(47, 4, 'Scan to Pay via UPI', 0, 1, 'C');
    
    $pdf->SetXY(148, $cardTop + 39);
    $pdf->SetFont('Arial', '', 7);
    $pdf->SetTextColor($mutedSlate[0], $mutedSlate[1], $mutedSlate[2]);
    $pdf->Cell(47, 3, 'UPI ID: ' . $BANK_DETAILS['upiId'], 0, 1, 'C');

    $pdf->Output('F', $filePath);
    return $filePath;
}
