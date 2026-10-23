<?php defined('_JEXEC') or die; ?>

<style>
#<?php echo $moduleId; ?>{
  --fire:var(--fire, #CC2200);--blood:var(--blood, #8B0000);--ember:var(--ember, #FF4500);--gold:var(--gold-light, #DAA520);
}
#<?php echo $moduleId; ?> .hmb-tc{
  display:flex;flex-wrap:wrap;gap:8px 10px;align-items:center;padding:4px 0;
}
#<?php echo $moduleId; ?> .hmb-tc a{
  display:inline-flex;align-items:center;
  padding:4px 12px;
  font-family:'Oswald',sans-serif;
  text-transform:uppercase;
  letter-spacing:1px;
  text-decoration:none;
  color:var(--grey-8, #888888);
  background:linear-gradient(145deg,var(--steel, #1A1A1A),var(--dark, #0A0A0A));
  border:1px solid var(--grey-2, #2A2A2A);
  border-top-color:var(--grey-3, #333333);
  border-bottom-color:#000;
  border-radius:2px;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.04),0 2px 4px rgba(0,0,0,.5);
  transition:color .25s,border-color .25s,box-shadow .25s,transform .2s;
  position:relative;
  overflow:hidden;
  white-space:nowrap;
  line-height:1.4;
}
/* Brillo metálico superior */
#<?php echo $moduleId; ?> .hmb-tc a::before{
  content:'';position:absolute;top:0;left:0;right:0;height:40%;
  background:linear-gradient(180deg,rgba(255,255,255,.05),transparent);
  pointer-events:none;
}
/* Glow de fondo al hover */
#<?php echo $moduleId; ?> .hmb-tc a::after{
  content:'';position:absolute;inset:0;
  background:var(--fire);opacity:0;
  transition:opacity .25s;pointer-events:none;
}
#<?php echo $moduleId; ?> .hmb-tc a:hover::after{opacity:.1;}
#<?php echo $moduleId; ?> .hmb-tc a:hover{
  color:#fff;
  border-color:var(--fire);
  transform:translateY(-2px);
  box-shadow:
    inset 0 1px 0 rgba(255,255,255,.06),
    0 0 8px rgba(204,34,0,.4),
    0 4px 12px rgba(0,0,0,.7);
}
#<?php echo $moduleId; ?> .hmb-tc a:active{transform:translateY(0);}
/* Badge de contador */
#<?php echo $moduleId; ?> .hmb-tc a .hmb-tc-count{
  display:inline-block;
  margin-left:5px;
  font-size:9px;
  opacity:.45;
  font-weight:400;
  vertical-align:middle;
}
/* Animación de entrada escalonada */
#<?php echo $moduleId; ?> .hmb-tc a{
  animation:hmbTcIn .35s ease both;
}
@keyframes hmbTcIn{
  from{opacity:0;transform:scale(.85);}
  to{opacity:1;transform:scale(1);}
}
<?php for($i=1;$i<=40;$i++): ?>
#<?php echo $moduleId; ?> .hmb-tc a:nth-child(<?php echo $i; ?>){animation-delay:<?php echo $i*0.03; ?>s;}
<?php endfor; ?>
</style>

<div id="<?php echo $moduleId; ?>">
  <div class="hmb-tc" role="list" aria-label="Nube de etiquetas">
    <?php foreach ($tags as $tag):
      $showCount = (bool)$params->get('show_count', 1);
    ?>
    <a href="<?php echo htmlspecialchars($tag->url, ENT_QUOTES); ?>"
       role="listitem"
       style="font-size:<?php echo $tag->size; ?>px;font-weight:<?php echo $tag->weight; ?>;"
       title="<?php echo htmlspecialchars($tag->title, ENT_QUOTES); ?> (<?php echo $tag->count; ?>)">
      <?php echo htmlspecialchars($tag->title, ENT_QUOTES); ?>
      <?php if ($showCount): ?>
      <span class="hmb-tc-count"><?php echo $tag->count; ?></span>
      <?php endif; ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>
