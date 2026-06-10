<?php /** @var array $timeline */ ?>
<ul class="chatter">
<?php if (empty($timeline)): ?>
    <li class="text-muted small py-3">Sin actividad registrada.</li>
<?php else: ?>
    <?php foreach ($timeline as $item): ?>
        <?php
        $nombre = $item['data']['usuario_nombre']
            ?? $item['data']['realizado_por_nombre']
            ?? $item['data']['registrado_por_nombre']
            ?? 'Sistema';
        $inicial = strtoupper(mb_substr($nombre, 0, 1));
        ?>
        <li class="chatter-item">
            <div class="chatter-avatar"><?= e($inicial) ?></div>
            <div class="flex-grow-1">
                <div class="chatter-meta">
                    <strong><?= e($nombre) ?></strong>
                    · <?= e(date('d/m/Y H:i', strtotime($item['fecha']))) ?>
                    <?php if ($item['tipo'] === 'visita'): ?>
                        · <i class="bi bi-geo-alt"></i> Visita
                    <?php elseif ($item['tipo'] === 'asignacion'): ?>
                        · <i class="bi bi-arrow-left-right"></i> Asignación
                    <?php elseif ($item['tipo'] === 'email'): ?>
                        · <i class="bi bi-envelope"></i> Email
                    <?php elseif ($item['tipo'] === 'tarea'): ?>
                        · <i class="bi bi-check2-square"></i> Tarea
                    <?php elseif ($item['tipo'] === 'venta'): ?>
                        · <i class="bi bi-cart-check"></i> Venta
                    <?php else: ?>
                        · <i class="bi bi-chat-left-text"></i> Nota
                    <?php endif; ?>
                </div>
                <div class="chatter-body">
                    <?php if ($item['tipo'] === 'visita'): ?>
                        <?php if (($item['data']['estado'] ?? '') === 'cancelada'): ?>
                            <span class="text-muted">Visita cancelada</span>
                            <?php if (!empty($item['data']['tipo'])): ?>
                                — <?= e(str_replace('_', ' ', $item['data']['tipo'])) ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-capitalize"><?= e(str_replace('_', ' ', $item['data']['tipo'] ?? '')) ?></span>
                            <?php if (!empty($item['data']['resultado'])): ?>
                                — <?= e(str_replace('_', ' ', $item['data']['resultado'])) ?>
                            <?php endif; ?>
                            <?php if (!empty($item['data']['es_primera_visita'])): ?>
                                <span class="badge badge-primera-visita">1ª visita</span>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if (!empty($item['data']['notas'])): ?>
                            <div class="mt-1 text-muted"><?= e($item['data']['notas']) ?></div>
                        <?php endif; ?>
                    <?php elseif ($item['tipo'] === 'asignacion'): ?>
                        <?php
                        $tipoAsig = $item['data']['tipo'] ?? '';
                        $textoAsig = match ($tipoAsig) {
                            'transferencia_inpro' => 'Transferencia de gestión a INPRO',
                            'transferencia_empresa' => 'Transferencia de gestión a empresa colaboradora',
                            'asignacion_inicial' => 'Asignación a empresa colaboradora',
                            default => ucfirst(str_replace('_', ' ', $tipoAsig)),
                        };
                        ?>
                        <?= e($textoAsig) ?>
                        <?php if (!empty($item['data']['motivo'])): ?>
                            — <?= e($item['data']['motivo']) ?>
                        <?php endif; ?>
                    <?php elseif ($item['tipo'] === 'email'): ?>
                        Recordatorio enviado a <strong><?= e($item['data']['destinatario'] ?? '') ?></strong>
                        <?php if (!empty($item['data']['asunto'])): ?>
                            <div class="mt-1 text-muted"><?= e($item['data']['asunto']) ?></div>
                        <?php endif; ?>
                    <?php elseif ($item['tipo'] === 'tarea'): ?>
                        <strong><?= e($item['data']['titulo'] ?? 'Tarea') ?></strong>
                        <span class="badge rounded-pill bg-<?= ($item['data']['estado'] ?? '') === 'completada' ? 'success' : 'secondary' ?> ms-1">
                            <?= e($item['data']['estado'] ?? '') ?>
                        </span>
                        <?php if (!empty($item['data']['descripcion'])): ?>
                            <div class="mt-1 text-muted"><?= e($item['data']['descripcion']) ?></div>
                        <?php endif; ?>
                    <?php elseif ($item['tipo'] === 'venta'): ?>
                        <?= e($item['data']['concepto_venta'] ?? 'Venta') ?>
                        — <strong><?= number_format((float) ($item['data']['importe_final_eur'] ?? 0), 2, ',', '.') ?> €</strong>
                        <span class="badge rounded-pill bg-<?= ($item['data']['estado'] ?? '') === 'validada' ? 'success' : 'warning text-dark' ?> ms-1">
                            <?= e($item['data']['estado'] ?? '') ?>
                        </span>
                    <?php else: ?>
                        <?= e($item['data']['contenido'] ?? '') ?>
                    <?php endif; ?>
                </div>
            </div>
        </li>
    <?php endforeach; ?>
<?php endif; ?>
</ul>
