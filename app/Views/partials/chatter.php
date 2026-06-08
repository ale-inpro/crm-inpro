<?php /** @var array $timeline */ ?>
<ul class="chatter">
<?php if (empty($timeline)): ?>
    <li class="text-muted small py-3">Sin actividad registrada.</li>
<?php else: ?>
    <?php foreach ($timeline as $item): ?>
        <?php
        $nombre = $item['data']['usuario_nombre']
            ?? $item['data']['realizado_por_nombre']
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
                    <?php else: ?>
                        · <i class="bi bi-chat-left-text"></i> Nota
                    <?php endif; ?>
                </div>
                <div class="chatter-body">
                    <?php if ($item['tipo'] === 'visita'): ?>
                        <span class="text-capitalize"><?= e(str_replace('_', ' ', $item['data']['tipo'])) ?></span>
                        — <?= e(str_replace('_', ' ', $item['data']['resultado'])) ?>
                        <?php if ($item['data']['es_primera_visita']): ?>
                            <span class="badge badge-primera-visita">1ª visita</span>
                        <?php endif; ?>
                        <?php if (!empty($item['data']['notas'])): ?>
                            <div class="mt-1 text-muted"><?= e($item['data']['notas']) ?></div>
                        <?php endif; ?>
                    <?php elseif ($item['tipo'] === 'asignacion'): ?>
                        <?= e(str_replace('_', ' ', $item['data']['tipo'])) ?>
                        <?php if (!empty($item['data']['motivo'])): ?>
                            — <?= e($item['data']['motivo']) ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <?= e($item['data']['contenido'] ?? '') ?>
                    <?php endif; ?>
                </div>
            </div>
        </li>
    <?php endforeach; ?>
<?php endif; ?>
</ul>
