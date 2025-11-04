<?php
// election/export_results.php
session_start();
require_once __DIR__ . "/../../classes/election.php";
require_once __DIR__ . "/../../vendor/autoload.php"; // Optional if using Composer for FPDF

$electionObj = new Election();
$results = $electionObj->fetchResults();

if (empty($results)) {
    $_SESSION['error'] = "No results available to export.";
    header("Location: view_results.php");
    exit;
}

require_once __DIR__ . "/../../lib/fpdf/fpdf.php"; // If FPDF is locally included

class PDF extends FPDF {
    function Header() {
        // Title
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'Election Results Report', 0, 1, 'C');
        $this->Ln(5);
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, 'Generated on: ' . date('F j, Y, g:i A'), 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    function TableHeader() {
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(46, 125, 50);
        $this->SetTextColor(255);
        $this->Cell(60, 10, 'Position', 1, 0, 'C', true);
        $this->Cell(70, 10, 'Candidate', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Votes', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Status', 1, 1, 'C', true);
    }

    function TableBody($results) {
        $this->SetFont('Arial', '', 11);
        $this->SetTextColor(0);

        $currentPosition = '';
        foreach ($results as $r) {
            // Group by position with a separator
            if ($r['position_name'] !== $currentPosition) {
                $this->Ln(4);
                $this->SetFont('Arial', 'B', 12);
                $this->Cell(0, 8, strtoupper($r['position_name']), 0, 1, 'L');
                $this->SetFont('Arial', '', 11);
                $currentPosition = $r['position_name'];
            }

            $this->Cell(60, 10, $r['position_name'], 1);
            $this->Cell(70, 10, $r['candidate_name'], 1);
            $this->Cell(30, 10, $r['votes'], 1, 0, 'C');
            $statusColor = ($r['status'] === 'Winner') ? 'Winner' : 'Not Winner';
            $this->Cell(30, 10, $statusColor, 1, 1, 'C');
        }
    }
}

// Generate PDF
$pdf = new PDF();
$pdf->AddPage();
$pdf->TableHeader();
$pdf->TableBody($results);

// Output
$pdf->Output('D', 'Election_Results_' . date('Y-m-d') . '.pdf');
exit;
?>
