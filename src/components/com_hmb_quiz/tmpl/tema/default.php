<?php
\defined('_JEXEC') or die;

/** @var \Hmb\Component\Quiz\Site\View\Tema\HtmlView $this */
$e = static function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); };
$t = $this->tema;
?>
<article class="mq-tema" style="max-width:820px;margin:0 auto;padding:16px;line-height:1.65">
	<h1><?php echo $e($t['h1']); ?></h1>

	<?php foreach ($t['intro'] as $p) : ?>
		<p><?php echo $e($p); ?></p>
	<?php endforeach; ?>

	<p style="margin:24px 0"><a style="display:inline-block;padding:12px 28px;font-weight:700;border-radius:6px;background:#c00;color:#fff;text-decoration:none" href="<?php echo $e($this->jugarUrl); ?>">🤘 Jugar al Metal Quiz gratis</a></p>

	<h2>Preguntas de ejemplo</h2>
	<ol>
		<?php foreach ($t['preguntas'] as $par) : ?>
			<li><strong><?php echo $e($par[0]); ?></strong><br><details><summary>Ver respuesta</summary><?php echo $e($par[1]); ?></details></li>
		<?php endforeach; ?>
	</ol>

	<?php if ($this->bandas) : ?>
		<h2>Bandas del quiz en nuestra base de datos</h2>
		<p>Consulta la ficha completa, con miembros e historia, de las bandas sobre las que te preguntamos:</p>
		<ul>
			<?php foreach ($this->bandas as $b) : ?>
				<li><a href="<?php echo $e($b['url']); ?>"><?php echo $e($b['nombre']); ?></a></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<p>¿Quieres más? Mira las <a href="<?php echo $e($this->efemUrl); ?>">efemérides del metal</a>: qué pasó un día como hoy en la historia del rock y el heavy metal.</p>

	<h2>Más quizzes de metal</h2>
	<ul>
		<?php foreach ($this->otros as $o) : ?>
			<li><a href="<?php echo $e($o['url']); ?>"><?php echo $e($o['nombre']); ?></a></li>
		<?php endforeach; ?>
	</ul>
</article>
