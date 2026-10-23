<?php
defined('_JEXEC') or die;
?>
<div class="container-fluid">
    <div class="row mb-4">
        <?php foreach (['checkins' => 'Check-ins', 'horns' => 'Cuernos 🤘', 'matches' => 'Matches', 'messages' => 'Mensajes', 'reports' => 'Denuncias'] as $key => $label) : ?>
            <div class="col">
                <div class="card text-center">
                    <div class="card-body">
                        <h2><?php echo (int) ($this->stats[$key] ?? 0); ?></h2>
                        <span class="text-muted"><?php echo $label; ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <h3>Últimas denuncias</h3>
    <?php if (empty($this->reports)) : ?>
        <p class="text-muted">Sin denuncias. La parroquia se porta bien.</p>
    <?php else : ?>
        <table class="table table-striped">
            <thead><tr><th>Fecha</th><th>Denunciante</th><th>Denunciado</th><th>Motivo</th></tr></thead>
            <tbody>
            <?php foreach ($this->reports as $r) : ?>
                <tr>
                    <td><?php echo htmlspecialchars((string) $r->created, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string) $r->reporter, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string) $r->reported, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string) $r->reason, ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
