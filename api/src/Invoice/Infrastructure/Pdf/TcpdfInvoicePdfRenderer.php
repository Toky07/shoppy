<?php

declare(strict_types=1);

namespace App\Invoice\Infrastructure\Pdf;

use App\Invoice\Application\Port\InvoicePdfRenderer;
use App\Invoice\Domain\Entity\Invoice;
use App\Invoice\Domain\ValueObject\InvoiceLine;
use TCPDF;

final readonly class TcpdfInvoicePdfRenderer implements InvoicePdfRenderer
{
    public function render(Invoice $invoice, ?string $logoBinary): string
    {
        if (!defined('TCPDF_SILENCE_DEPRECATION')) {
            define('TCPDF_SILENCE_DEPRECATION', true);
        }

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setCompression(false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->AddPage();
        $pdf->SetTitle('Facture '.$invoice->number()->value());
        $pdf->SetFont('helvetica', '', 10);

        $top = 15.0;
        if ($logoBinary !== null && $logoBinary !== '') {
            $pdf->Image('@'.$logoBinary, 15, 12, 28, 0, '', '', '', false, 300);
            $top = 32.0;
        }

        $pdf->SetXY(15, $top);
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 9, 'FACTURE', 0, 1, 'R');
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 7, $invoice->number()->value(), 0, 1, 'R');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, 'Date : '.$invoice->issuedAt()->format('d/m/Y'), 0, 1, 'R');
        $pdf->Cell(0, 6, 'Commande : '.$invoice->orderId()->value(), 0, 1, 'R');
        $pdf->Ln(8);

        $this->writeTableHeader($pdf);
        foreach ($invoice->lines() as $line) {
            $this->writeLine($pdf, $line);
        }

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(130, 8, 'Total', 'T', 0, 'R');
        $pdf->Cell(50, 8, $this->euro($invoice->amount()->cents()), 'T', 1, 'R');

        return $pdf->Output($invoice->number()->value().'.pdf', 'S');
    }

    private function writeTableHeader(TCPDF $pdf): void
    {
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(245, 245, 245);
        $pdf->Cell(80, 8, 'Description', 1, 0, 'L', true);
        $pdf->Cell(20, 8, 'Qte', 1, 0, 'R', true);
        $pdf->Cell(40, 8, 'Prix unitaire', 1, 0, 'R', true);
        $pdf->Cell(40, 8, 'Total', 1, 1, 'R', true);
    }

    private function writeLine(TCPDF $pdf, InvoiceLine $line): void
    {
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(80, 7, $line->description(), 1, 0, 'L');
        $pdf->Cell(20, 7, (string) $line->quantity(), 1, 0, 'R');
        $pdf->Cell(40, 7, $this->euro($line->unitPriceCents()), 1, 0, 'R');
        $pdf->Cell(40, 7, $this->euro($line->lineTotalCents()), 1, 1, 'R');
    }

    private function euro(int $cents): string
    {
        return sprintf('%d,%02d EUR', intdiv($cents, 100), $cents % 100);
    }
}
