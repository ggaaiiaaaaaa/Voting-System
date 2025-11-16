<?php
session_start();
require_once __DIR__ . "/../../classes/election.php";
require_once __DIR__ . "/../../vendor/tcpdf/tcpdf.php";

$electionObj = new Election();

// Check election status
$election_status = $electionObj->getAdminControlledStatus();
if ($election_status !== 'Ended') {
    $_SESSION['error'] = "Election results are not available for export. Current status: $election_status.";
    header("Location: view_results.php");
    exit;
}

// Fetch results
$results = $electionObj->fetchResults();

// Deduplicate results by position & candidate
$uniqueResults = [];
foreach ($results as $r) {
    $pos = $r['position_name'];
    $cand = $r['candidate_name'];
    if (!isset($uniqueResults[$pos][$cand])) {
        $uniqueResults[$pos][$cand] = $r;
    }
}

// Determine winners per position
foreach ($uniqueResults as $pos => &$cands) {
    $votes = array_column($cands, 'votes');
    $maxVotes = max($votes);

    foreach ($cands as &$r) {
        $r['status'] = ($r['votes'] == $maxVotes) ? 'Winner' : 'Loser';
    }
}
unset($cands, $r);

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Election System');
$pdf->SetAuthor('Admin');
$pdf->SetTitle('Election Results');
$pdf->SetSubject('Final Election Results');

// Set margins
$pdf->SetMargins(15, 20, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 15);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 10);

// Title
$pdf->SetFont('helvetica', 'B', 20);
$pdf->SetTextColor(208, 44, 77); // #D02C4D
$pdf->Cell(0, 10, 'Election Results', 0, 1, 'C');
$pdf->Ln(3);

// Subtitle
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 5, 'Final votes per position and winner summary', 0, 1, 'C');
$pdf->Ln(2);

// Export date
$pdf->SetFont('helvetica', 'I', 9);
$pdf->Cell(0, 5, 'Generated on: ' . date('F j, Y g:i A'), 0, 1, 'C');
$pdf->Ln(8);

// Reset text color for content
$pdf->SetTextColor(0, 0, 0);

if (!empty($uniqueResults)) {
    foreach ($uniqueResults as $pos => $cands) {
        // Position header
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetFillColor(208, 44, 77); // #D02C4D
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 8, $pos, 0, 1, 'L', true);
        $pdf->Ln(2);
        
        // Table header
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(254, 234, 234); // #FEEAEA
        $pdf->SetTextColor(208, 44, 77); // #D02C4D
        
        $pdf->Cell(90, 7, 'Candidate', 1, 0, 'L', true);
        $pdf->Cell(40, 7, 'Votes', 1, 0, 'C', true);
        $pdf->Cell(50, 7, 'Status', 1, 1, 'C', true);
        
        // Table content
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(60, 60, 60);
        
        foreach ($cands as $r) {
            // Candidate name
            $pdf->Cell(90, 6, $r['candidate_name'], 1, 0, 'L');
            
            // Votes
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(40, 6, $r['votes'], 1, 0, 'C');
            
            // Status with color
            $pdf->SetFont('helvetica', 'B', 9);
            if ($r['status'] === 'Winner') {
                $pdf->SetFillColor(220, 252, 231); // Light green
                $pdf->SetTextColor(22, 101, 52); // Dark green
            } else {
                $pdf->SetFillColor(243, 244, 246); // Light gray
                $pdf->SetTextColor(107, 114, 128); // Dark gray
            }
            $pdf->Cell(50, 6, $r['status'], 1, 1, 'C', true);
            
            // Reset colors
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor(60, 60, 60);
        }
        
        $pdf->Ln(6);
    }
    
    // Summary section
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(208, 44, 77);
    $pdf->Cell(0, 8, 'Winners Summary', 0, 1, 'L');
    $pdf->Ln(2);
    
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    
    foreach ($uniqueResults as $pos => $cands) {
        $winners = array_filter($cands, fn($r) => $r['status'] === 'Winner');
        foreach ($winners as $winner) {
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(80, 6, $pos . ':', 0, 0, 'L');
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 6, $winner['candidate_name'] . ' (' . $winner['votes'] . ' votes)', 0, 1, 'L');
        }
    }
    
} else {
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'No results available.', 0, 1, 'C');
}

// Output PDF
$filename = 'Election_Results_' . date('Y-m-d_His') . '.pdf';
$pdf->Output($filename, 'D'); // 'D' = force download
exit;
?>