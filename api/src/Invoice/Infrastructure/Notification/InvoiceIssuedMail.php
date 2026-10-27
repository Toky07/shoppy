<?php

declare(strict_types=1);

namespace App\Invoice\Infrastructure\Notification;

use App\Email\Domain\ValueObject\EmailBody;
use App\Email\Domain\ValueObject\EmailSubject;
use App\Invoice\Domain\Entity\Invoice;
use App\Invoice\Domain\ValueObject\InvoiceLine;
use App\Shared\Application\Event\InvoiceIssued;

final readonly class InvoiceIssuedMail
{
    private const INK = '#1C1E27';
    private const ACCENT = '#C6EF4C';
    private const CANVAS = '#F4F5F7';
    private const SURFACE = '#FFFFFF';
    private const BODY = '#5C6070';
    private const MUTED = '#8A8E99';
    private const LINE = '#E6E7EC';
    private const SOFT = '#F7FBE8';

    public function __construct(private string $storefrontBaseUrl)
    {
    }

    public function customerSubject(string $number): EmailSubject
    {
        return EmailSubject::fromString('Votre facture '.$number);
    }

    public function adminSubject(string $number): EmailSubject
    {
        return EmailSubject::fromString('Nouvelle facture '.$number);
    }

    public function customerBody(InvoiceIssued $event, ?Invoice $invoice): EmailBody
    {
        $text = $this->customerText($event, $invoice);
        $html = $this->document(
            preheader: sprintf('Votre facture %s — total %s. Le PDF est en pièce jointe.', $event->number, self::euro($event->amountCents)),
            kicker: 'Facture',
            title: 'Votre facture est prête',
            badge: $event->number,
            intro: 'Merci pour votre commande. Retrouvez le détail ci-dessous&nbsp;; le PDF officiel est joint à cet email.',
            invoice: $invoice,
            event: $event,
            extraMeta: [],
            ctaLabel: 'Voir ma commande',
            ctaPath: '/orders/'.$event->orderId,
            signoff: 'Merci pour votre confiance.<br>L’équipe Shoppy',
        );

        return EmailBody::fromText($text, $html);
    }

    public function adminBody(InvoiceIssued $event, ?Invoice $invoice, ?string $customerEmail): EmailBody
    {
        $text = $this->adminText($event, $invoice, $customerEmail);
        $html = $this->document(
            preheader: sprintf('Facture %s émise — total %s.', $event->number, self::euro($event->amountCents)),
            kicker: 'Administration',
            title: 'Une facture a été émise',
            badge: $event->number,
            intro: 'Une nouvelle facture vient d’être générée après un paiement. Le PDF est joint à cet email.',
            invoice: $invoice,
            event: $event,
            extraMeta: [
                'Client' => $customerEmail ?? 'inconnu',
            ],
            ctaLabel: 'Ouvrir la commande',
            ctaPath: '/admin/orders/'.$event->orderId,
            signoff: 'Notification automatique Shoppy',
        );

        return EmailBody::fromText($text, $html);
    }

    private function customerText(InvoiceIssued $event, ?Invoice $invoice): string
    {
        $lines = [
            'Bonjour,',
            '',
            sprintf('Votre facture %s est prête. Vous la trouverez en pièce jointe de cet email.', $event->number),
        ];

        $this->appendLineItemsText($lines, $invoice);

        $lines[] = '';
        $lines[] = 'Total : '.self::euro($event->amountCents);
        $lines[] = '';
        $lines[] = 'Merci pour votre confiance.';
        $lines[] = 'L’équipe Shoppy';

        return implode("\n", $lines);
    }

    private function adminText(InvoiceIssued $event, ?Invoice $invoice, ?string $customerEmail): string
    {
        $lines = [
            'Une nouvelle facture a été émise.',
            '',
            'Facture : '.$event->number,
            'Client : '.($customerEmail ?? 'inconnu'),
            'Commande : '.$event->orderId,
        ];

        if ($invoice !== null) {
            $lines[] = 'Date : '.$invoice->issuedAt()->format('d/m/Y');
        }

        $this->appendLineItemsText($lines, $invoice);

        $lines[] = '';
        $lines[] = 'Total : '.self::euro($event->amountCents);
        $lines[] = '';
        $lines[] = 'Le PDF est en pièce jointe.';

        return implode("\n", $lines);
    }

    /**
     * @param list<string> $lines
     */
    private function appendLineItemsText(array &$lines, ?Invoice $invoice): void
    {
        if ($invoice === null || $invoice->lines() === []) {
            return;
        }

        $lines[] = '';

        foreach ($invoice->lines() as $line) {
            $lines[] = sprintf(
                '%s × %d — %s',
                $line->description(),
                $line->quantity(),
                self::euro($line->lineTotalCents()),
            );
        }
    }

    /**
     * @param array<string, string> $extraMeta
     */
    private function document(
        string $preheader,
        string $kicker,
        string $title,
        string $badge,
        string $intro,
        ?Invoice $invoice,
        InvoiceIssued $event,
        array $extraMeta,
        string $ctaLabel,
        string $ctaPath,
        string $signoff,
    ): string {
        $meta = $extraMeta;
        if ($invoice !== null) {
            $meta['Date'] = $invoice->issuedAt()->format('d/m/Y');
        }
        $meta['Commande'] = $event->orderId;

        return '<!DOCTYPE html>
<html lang="fr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>'.$this->e($badge).'</title>
</head>
<body style="margin:0;padding:0;background-color:'.self::CANVAS.';">
<div style="display:none;max-height:0;overflow:hidden;color:'.self::CANVAS.';font-size:1px;line-height:1px;">'.$this->e($preheader).'</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:'.self::CANVAS.';padding:32px 12px;">
<tr>
<td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background-color:'.self::SURFACE.';border:1px solid '.self::LINE.';border-radius:20px;overflow:hidden;">
<tr><td style="height:6px;background-color:'.self::ACCENT.';font-size:0;line-height:0;">&nbsp;</td></tr>
<tr>
<td style="padding:28px 32px 0;">
<p style="margin:0;font-family:Georgia,\'Times New Roman\',serif;font-size:12px;letter-spacing:0.22em;text-transform:uppercase;color:'.self::INK.';font-weight:700;">Shoppy</p>
</td>
</tr>
<tr>
<td style="padding:18px 32px 0;">
<p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:0.16em;text-transform:uppercase;color:'.self::MUTED.';">'.$this->e($kicker).'</p>
<h1 style="margin:8px 0 0;font-family:Georgia,\'Times New Roman\',serif;font-size:28px;line-height:1.25;color:'.self::INK.';font-weight:700;">'.$this->e($title).'</h1>
<p style="margin:14px 0 0;display:inline-block;background-color:'.self::INK.';color:'.self::SURFACE.';font-family:Arial,Helvetica,sans-serif;font-size:13px;letter-spacing:0.04em;padding:7px 14px;border-radius:999px;">'.$this->e($badge).'</p>
</td>
</tr>
<tr>
<td style="padding:20px 32px 8px;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.65;color:'.self::BODY.';">'.$intro.'</td>
</tr>
'.$this->metaTable($meta).'
'.$this->linesTable($invoice).'
'.$this->totalRow($event->amountCents).'
<tr>
<td style="padding:8px 32px 28px;" align="center">
<a href="'.$this->e($this->url($ctaPath)).'" style="display:inline-block;background-color:'.self::ACCENT.';color:'.self::INK.';text-decoration:none;font-family:Arial,Helvetica,sans-serif;font-size:14px;font-weight:700;padding:13px 24px;border-radius:999px;">'.$this->e($ctaLabel).'</a>
</td>
</tr>
<tr>
<td style="padding:0 32px 28px;font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:1.6;color:'.self::MUTED.';border-top:1px solid '.self::LINE.';">
<p style="margin:20px 0 0;">'.$signoff.'</p>
</td>
</tr>
</table>
<p style="margin:20px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:'.self::MUTED.';">Shoppy · Facture envoyée automatiquement</p>
</td>
</tr>
</table>
</body>
</html>';
    }

    /**
     * @param array<string, string> $meta
     */
    private function metaTable(array $meta): string
    {
        $cells = '';
        foreach ($meta as $label => $value) {
            $cells .= '<td style="padding:0 16px 12px 0;font-family:Arial,Helvetica,sans-serif;vertical-align:top;">'
                .'<p style="margin:0;font-size:11px;letter-spacing:0.12em;text-transform:uppercase;color:'.self::MUTED.';">'.$this->e($label).'</p>'
                .'<p style="margin:6px 0 0;font-size:14px;color:'.self::INK.';font-weight:700;word-break:break-all;">'.$this->e($value).'</p>'
                .'</td>';
        }

        return '<tr><td style="padding:8px 32px 4px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>'.$cells.'</tr></table></td></tr>';
    }

    private function linesTable(?Invoice $invoice): string
    {
        if ($invoice === null || $invoice->lines() === []) {
            return '';
        }

        $rows = '<tr>'
            .$this->th('Description', 'left')
            .$this->th('Qté', 'right')
            .$this->th('Prix', 'right')
            .$this->th('Total', 'right')
            .'</tr>';

        foreach ($invoice->lines() as $line) {
            $rows .= $this->lineRow($line);
        }

        return '<tr><td style="padding:8px 32px 4px;">'
            .'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid '.self::LINE.';border-radius:12px;overflow:hidden;">'
            .$rows
            .'</table></td></tr>';
    }

    private function lineRow(InvoiceLine $line): string
    {
        return '<tr>'
            .$this->td($line->description(), 'left', false)
            .$this->td((string) $line->quantity(), 'right', false)
            .$this->td(self::euro($line->unitPriceCents()), 'right', false)
            .$this->td(self::euro($line->lineTotalCents()), 'right', true)
            .'</tr>';
    }

    private function totalRow(int $amountCents): string
    {
        return '<tr><td style="padding:4px 32px 16px;">'
            .'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:'.self::SOFT.';border-radius:12px;">'
            .'<tr>'
            .'<td style="padding:16px 20px;font-family:Arial,Helvetica,sans-serif;font-size:13px;letter-spacing:0.12em;text-transform:uppercase;color:'.self::MUTED.';">Total TTC</td>'
            .'<td align="right" style="padding:16px 20px;font-family:Georgia,\'Times New Roman\',serif;font-size:22px;color:'.self::INK.';font-weight:700;">'.$this->e(self::euro($amountCents)).'</td>'
            .'</tr></table></td></tr>';
    }

    private function th(string $label, string $align): string
    {
        return '<th align="'.$align.'" style="padding:10px 12px;background-color:'.self::CANVAS.';font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:'.self::MUTED.';font-weight:700;border-bottom:1px solid '.self::LINE.';">'.$this->e($label).'</th>';
    }

    private function td(string $value, string $align, bool $strong): string
    {
        $weight = $strong ? '700' : '400';
        $color = $strong ? self::INK : self::BODY;

        return '<td align="'.$align.'" style="padding:12px;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:'.$color.';font-weight:'.$weight.';border-bottom:1px solid '.self::LINE.';">'.$this->e($value).'</td>';
    }

    private function url(string $path): string
    {
        return rtrim($this->storefrontBaseUrl, '/').$path;
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private static function euro(int $cents): string
    {
        return sprintf('%d,%02d €', intdiv($cents, 100), $cents % 100);
    }
}
