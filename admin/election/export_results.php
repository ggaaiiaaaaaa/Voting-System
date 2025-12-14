<?php
session_start();
require_once __DIR__ . "/../../classes/election.php";
require_once __DIR__ . "/../../vendor/tcpdf/tcpdf.php";

// ✅ FIXED: Set timezone to Philippine Time
date_default_timezone_set('Asia/Manila');

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

// Get current election info
$currentElection = $electionObj->fetchCurrentElection();
$electionName = $currentElection['name'] ?? 'Official Election';
$electionStartDate = isset($currentElection['start_date']) ? date('F j, Y', strtotime($currentElection['start_date'])) : 'N/A';
$electionEndDate = isset($currentElection['end_date']) ? date('F j, Y', strtotime($currentElection['end_date'])) : 'N/A';

// Get voter statistics
$stats = $electionObj->getVoterStats();
$totalVotes = $electionObj->countTotalVotes();

// ✅ FIXED: Format date and time properly
$exportDate = date('F j, Y');  // e.g., December 14, 2025
$exportTime = date('h:i A');   // e.g., 02:30 PM
$exportDateTime = date('Y-m-d_His'); // For filename

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('iElect Voting System');
$pdf->SetTitle("Election Results - {$electionName}");
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

// Logo/Header section (optional - add your logo here)
// $pdf->Image('path/to/logo.png', 15, 10, 30, '', 'PNG');

// Title
$pdf->SetFont('helvetica', 'B', 22);
$pdf->SetTextColor(124, 58, 237); // Purple to match admin dashboard theme
$pdf->Cell(0, 10, 'iElect', 0, 1, 'C');
$pdf->SetFont('helvetica', 'B', 18);
$pdf->Cell(0, 8, 'Election Results', 0, 1, 'C');
$pdf->Ln(2);

// Election name
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(60, 60, 60);
$pdf->Cell(0, 6, $electionName, 0, 1, 'C');
$pdf->Ln(1);

// Election period
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 5, "Election Period: {$electionStartDate} to {$electionEndDate}", 0, 1, 'C');
$pdf->Ln(1);

// ✅ FIXED: Export date and time with timezone
$pdf->SetFont('helvetica', 'I', 9);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 5, "Generated on: {$exportDate} at {$exportTime} (Philippine Time)", 0, 1, 'C');
$pdf->Ln(8);

// Summary statistics box
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetFillColor(240, 240, 240);
$pdf->SetTextColor(124, 58, 237); // Purple to match admin dashboard theme
$pdf->Cell(0, 7, 'Election Statistics', 0, 1, 'L', true);

$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(60, 60, 60);
$pdf->Cell(90, 5, 'Total Registered Students:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(0, 5, number_format($stats['total_students']), 0, 1, 'L');

$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(90, 5, 'Students Who Voted:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(0, 5, number_format($stats['voted']), 0, 1, 'L');

$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(90, 5, 'Voter Turnout:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(0, 5, $stats['turnout'] . '%', 0, 1, 'L');

$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(90, 5, 'Total Votes Cast:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(0, 5, number_format($totalVotes), 0, 1, 'L');
$pdf->Ln(8);

// Reset text color for content
$pdf->SetTextColor(0, 0, 0);

if (!empty($uniqueResults)) {
    foreach ($uniqueResults as $pos => $cands) {
        // Position header
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetFillColor(124, 58, 237); // Purple to match admin dashboard theme
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 8, $pos, 0, 1, 'L', true);
        $pdf->Ln(2);

        // Table header
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(245, 243, 255); // Light purple background
        $pdf->SetTextColor(124, 58, 237); // Purple to match admin dashboard theme
        
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
            $pdf->Cell(40, 6, number_format($r['votes']), 1, 0, 'C');
            
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
    
    // Winners Summary section
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(124, 58, 237); // Purple to match admin dashboard theme
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
            $pdf->Cell(0, 6, $winner['candidate_name'] . ' (' . number_format($winner['votes']) . ' votes)', 0, 1, 'L');
        }
    }
    
} else {
    $pdf->SetFont('helvetica', '', 12);
    $pdf->SetTextColor(150, 150, 150);
    $pdf->Cell(0, 10, 'No results available.', 0, 1, 'C');
}

// ✅ FIXED: Filename with proper timestamp
$filename = "Election_Results_{$exportDateTime}.pdf";
$pdf->Output($filename, 'D'); // 'D' = force download
exit;
?>