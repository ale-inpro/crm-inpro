<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class ResendService
{
    private static ?array $mailConfig = null;

    private static function mailConfig(): array
    {
        if (self::$mailConfig === null) {
            self::$mailConfig = require APP_PATH . '/config/mail.php';
        }
        return self::$mailConfig;
    }

    /**
     * Genera el cuerpo HTML del recordatorio de visita (se envuelve en template() al mandar).
     */
    public function plantillaRecordatorioVisita(array $data): string
    {
        $cliente   = htmlspecialchars((string) ($data['cliente_nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
        $fecha     = htmlspecialchars((string) ($data['fecha_formateada'] ?? ''), ENT_QUOTES, 'UTF-8');
        $comercial = htmlspecialchars((string) ($data['comercial_nombre'] ?? 'INPRO'), ENT_QUOTES, 'UTF-8');
        $esRemota  = !empty($data['es_remota']);
        $tipoLabel = $esRemota ? 'Videollamada' : 'Visita presencial';
        $tipoIcon  = $esRemota ? '💻' : '📍';
        $mensaje   = trim((string) ($data['mensaje_extra'] ?? ''));

        $extraHtml = '';
        if ($mensaje !== '') {
            $extraHtml = '<div style="margin-top:20px;padding:14px 16px;background:#f8faf9;border-left:4px solid #1a7f4b;border-radius:4px;">'
                . '<p style="margin:0 0 6px;font-size:13px;color:#1a7f4b;font-weight:600;">Mensaje adicional</p>'
                . '<p style="margin:0;color:#444;">' . nl2br(htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8')) . '</p>'
                . '</div>';
        }

        return <<<HTML
        <p style="margin:0 0 16px;color:#333;">Estimado/a equipo de <strong>{$cliente}</strong>,</p>
        <p style="margin:0 0 20px;color:#444;line-height:1.6;">
            Le escribimos desde <strong>INPRO</strong> para recordarle la siguiente cita comercial que tenemos programada con ustedes.
        </p>
        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f4f7f5;border-radius:8px;margin-bottom:20px;">
            <tr>
                <td style="padding:18px 20px;">
                    <p style="margin:0 0 10px;font-size:12px;text-transform:uppercase;letter-spacing:.05em;color:#1a7f4b;font-weight:700;">Detalles de la visita</p>
                    <p style="margin:0 0 8px;font-size:15px;color:#222;"><strong>{$tipoIcon} {$tipoLabel}</strong></p>
                    <p style="margin:0 0 8px;font-size:15px;color:#222;"><strong>Fecha y hora:</strong> {$fecha}</p>
                    <p style="margin:0;font-size:15px;color:#222;"><strong>Comercial INPRO:</strong> {$comercial}</p>
                </td>
            </tr>
        </table>
        <p style="margin:0 0 16px;color:#444;line-height:1.6;">
            Si necesita reprogramar la cita o tiene alguna consulta previa, puede responder a este correo o contactar con su comercial asignado.
        </p>
        {$extraHtml}
        <p style="margin:24px 0 0;color:#444;">Saludos cordiales,<br><strong style="color:#1a7f4b;">{$comercial}</strong><br><span style="color:#666;font-size:14px;">INPRO</span></p>
        HTML;
    }

    public function enviarRecordatorioVisita(array $user, int $visitaId, string $destinatario, string $asunto, string $html): void
    {
        $cfg    = self::mailConfig();
        $apiKey = (string) ($cfg['resend_api_key'] ?? '');

        if ($apiKey === '') {
            throw new \RuntimeException('RESEND_API_KEY no configurada.');
        }

        $intercept = (string) ($cfg['intercept_to'] ?? '');
        $to = $intercept !== '' ? $intercept : $destinatario;

        $payload = json_encode([
            'from'    => $cfg['from'] ?? 'CRM INPRO <onboarding@resend.dev>',
            'to'      => [$to],
            'subject' => $asunto,
            'html'    => $this->template($asunto, $html),
        ]);

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
        ]);
        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code < 200 || $code >= 300) {
            throw new \RuntimeException('Error Resend (' . $code . '): ' . $response);
        }

        $visita = (new \App\Models\VisitaModel())->findById($visitaId);
        $db = Database::connection();
        $db->prepare('UPDATE visitas SET recordatorio_enviado_at = NOW() WHERE id = ?')->execute([$visitaId]);
        $db->prepare('
            INSERT INTO actividad_emails (cliente_id, visita_id, usuario_id, destinatario, asunto)
            VALUES (?,?,?,?,?)
        ')->execute([
            $visita['cliente_id'], $visitaId, $user['id'], $destinatario, $asunto,
        ]);
        (new AuditoriaService())->log((int) $user['id'], 'visitas', $visitaId, 'recordatorio_email', [
            'destinatario' => $destinatario,
        ]);
    }

    private function template(string $title, string $bodyHtml): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>{$title}</title>
            <style>
                body  { font-family:'Segoe UI',Arial,sans-serif; background:#f4f4f4; margin:0; padding:0; }
                .wrap { max-width:600px; margin:30px auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.08); }
                .hdr  { background:#1a6b3a; color:#fff; padding:22px 30px; }
                .hdr h1 { margin:0; font-size:1.1rem; font-weight:700; letter-spacing:.4px; }
                .bdy  { padding:26px 30px; color:#333; line-height:1.65; }
                .bdy h2 { color:#1a6b3a; margin-top:0; font-size:1rem; }
                .ftr  { background:#f0f0f0; padding:12px 30px; font-size:.76rem; color:#999; }
            </style>
        </head>
        <body>
            <div class="wrap">
                <div class="hdr"><h1>CRM INPRO</h1></div>
                <div class="bdy">{$bodyHtml}</div>
                <div class="ftr">Mensaje automático. No respondas a este correo.</div>
            </div>
        </body>
        </html>
        HTML;
    }
}
