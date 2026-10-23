<?php defined('_JEXEC') or die;
if (empty($topItems)) return;
?>
<div id="<?php echo $moduleId; ?>">
  <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;scrollbar-width:none;">
    <ul class="hmb-ml-ul" style="display:flex;flex-direction:row;flex-wrap:nowrap;justify-content:center;list-style:none;margin:0;padding:0;width:100%;">
    <?php foreach ($topItems as $item):
      $url    = htmlspecialchars($item->flink ?? '#', ENT_QUOTES);
      $title  = htmlspecialchars($item->title, ENT_QUOTES);
      $hasSub = !empty($children[$item->id]);
      $isActive = ((int)$item->id === (int)$activeId);
    ?>
    <li style="flex-shrink:0;display:block;" data-id="<?php echo (int)$item->id; ?>">
      <a href="<?php echo $url; ?>" style="display:flex;align-items:center;gap:4px;padding:0 16px;height:46px;color:<?php echo $isActive?'#fff':'#999'; ?>;font-family:'Oswald',sans-serif;font-size:13px;font-weight:600;letter-spacing:2px;text-transform:uppercase;text-decoration:none;white-space:nowrap;border-bottom:2px solid <?php echo $isActive?'#CC2200':'transparent'; ?>;-webkit-tap-highlight-color:transparent;"><?php echo $title; ?><?php if($hasSub): ?>&nbsp;<span style="font-size:9px;opacity:.5;">▾</span><?php endif; ?></a>
      <?php if ($hasSub): ?>
      <div style="display:none;" class="hmb-sub-data">
        <?php foreach ($children[$item->id] as $child):
          $cUrl = htmlspecialchars($child->flink ?? '#', ENT_QUOTES);
          $cTxt = htmlspecialchars($child->title, ENT_QUOTES);
          $cHas = !empty($children[$child->id]) ? '1' : '0';
          $cId  = (int)$child->id;
        ?>
        <a href="<?php echo $cUrl; ?>" data-s2="<?php echo $cHas; ?>" data-s2id="<?php echo $cId; ?>"><?php echo $cTxt; ?></a>
        <?php if (!empty($children[$child->id])): ?>
        <div class="hmb-s2" style="display:none;" data-pid="<?php echo $cId; ?>">
          <?php foreach ($children[$child->id] as $grand): ?>
          <a href="<?php echo htmlspecialchars($grand->flink ?? '#', ENT_QUOTES); ?>"><?php echo htmlspecialchars($grand->title, ENT_QUOTES); ?></a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </li>
    <?php endforeach; ?>
    </ul>
  </div>
</div>

<script>
(function(){
  var mid = '<?php echo $moduleId; ?>';
  var nav = document.getElementById(mid);
  if(!nav) return;

  /* Panel nivel 2 — igual que antes */
  var panel = document.createElement('ul');
  panel.style.cssText = [
    'display:none',
    'position:fixed',
    'background:#0a0000',
    'border-top:2px solid #CC2200',
    'border-bottom:1px solid #330000',
    'border-left:1px solid #2a0000',
    'border-right:1px solid #2a0000',
    'list-style:none',
    'margin:0',
    'padding:4px 0',
    'z-index:2147483646',
    'min-width:200px',
    'box-shadow:0 8px 30px rgba(0,0,0,.9)'
  ].join(';');
  document.body.appendChild(panel);

  /* Panel nivel 3 — flyout derecha, solo desktop */
  var panel3 = document.createElement('ul');
  panel3.style.cssText = [
    'display:none',
    'position:fixed',
    'background:#0a0000',
    'border-top:1px solid #2a0000',
    'border-bottom:1px solid #2a0000',
    'border-left:2px solid #CC2200',
    'border-right:1px solid #2a0000',
    'list-style:none',
    'margin:0',
    'padding:4px 0',
    'z-index:2147483647',
    'min-width:200px',
    'box-shadow:8px 8px 30px rgba(0,0,0,.9)'
  ].join(';');
  document.body.appendChild(panel3);

  var openLi = null;
  var openLi2 = null;
  var closeTimer = null;
  var closeTimer3 = null;

  function isMob(){ return window.innerWidth <= 768; }

  function positionPanel(li){
    var r = li.getBoundingClientRect();
    if(isMob()){
      var nr = nav.getBoundingClientRect();
      panel.style.top      = Math.round(nr.bottom) + 'px';
      panel.style.left     = '0';
      panel.style.right    = '0';
      panel.style.width    = '100%';
      panel.style.minWidth = '0';
    } else {
      panel.style.top      = Math.round(r.bottom) + 'px';
      panel.style.left     = Math.round(r.left) + 'px';
      panel.style.right    = 'auto';
      panel.style.width    = 'auto';
      panel.style.minWidth = '200px';
    }
  }

  function closePanel3(){
    panel3.style.display = 'none';
    openLi2 = null;
  }

  function openPanel3(item2el, sub2el){
    panel3.innerHTML = '';
    sub2el.querySelectorAll('a').forEach(function(link){
      var li3 = document.createElement('li');
      var a = document.createElement('a');
      a.href = link.href;
      a.textContent = link.textContent;
      a.style.cssText = 'display:block;padding:11px 20px;color:#888;font-family:Oswald,sans-serif;font-size:12px;letter-spacing:1px;text-transform:uppercase;text-decoration:none;border-bottom:1px solid #1a0000;white-space:nowrap;';
      a.addEventListener('mouseenter',function(){this.style.color='#FF4500';this.style.background='rgba(139,0,0,.15)';});
      a.addEventListener('mouseleave',function(){this.style.color='#888';this.style.background='';});
      li3.appendChild(a);
      panel3.appendChild(li3);
    });
    var rP = panel.getBoundingClientRect();
    var rI = item2el.getBoundingClientRect();
    panel3.style.top     = Math.round(rI.top) + 'px';
    panel3.style.left    = Math.round(rP.right) + 'px';
    panel3.style.display = 'block';
    openLi2 = item2el;
  }

  function openPanel(li){
    var data = li.querySelector('.hmb-sub-data');
    if(!data) return;
    panel.innerHTML = '';
    closePanel3();

    data.querySelectorAll('a[data-s2]').forEach(function(link){
      var hasSub2 = link.getAttribute('data-s2') === '1';
      var sub2id  = link.getAttribute('data-s2id');
      var item = document.createElement('li');
      item.style.position = 'relative';
      var a = document.createElement('a');
      a.href = link.href;
      a.textContent = link.textContent;
      a.style.cssText = 'display:block;padding:11px 20px;color:#888;font-family:Oswald,sans-serif;font-size:12px;letter-spacing:1px;text-transform:uppercase;text-decoration:none;border-bottom:1px solid #1a0000;white-space:nowrap;-webkit-tap-highlight-color:transparent;' + (hasSub2 ? 'padding-right:30px;' : '');
      a.addEventListener('mouseenter',function(){this.style.color='#FF4500';this.style.background='rgba(139,0,0,.15)';});
      a.addEventListener('mouseleave',function(){this.style.color='#888';this.style.background='';});

      if(hasSub2){
        var arr = document.createElement('span');
        arr.textContent = '\u203a';
        arr.style.cssText = 'position:absolute;right:10px;top:50%;transform:translateY(-50%);font-size:15px;color:#CC2200;pointer-events:none;';
        item.appendChild(arr);

        item.addEventListener('mouseenter', function(){
          if(isMob()) return;
          clearTimeout(closeTimer3);
          var sub2 = data.querySelector('.hmb-s2[data-pid="' + sub2id + '"]');
          if(sub2) openPanel3(item, sub2);
        });
        item.addEventListener('mouseleave', function(){
          if(isMob()) return;
          closeTimer3 = setTimeout(closePanel3, 150);
        });
      } else {
        item.addEventListener('mouseenter', function(){
          if(isMob()) return;
          clearTimeout(closeTimer3);
          closeTimer3 = setTimeout(closePanel3, 150);
        });
      }

      item.appendChild(a);
      panel.appendChild(item);
    });

    positionPanel(li);
    panel.style.display = 'block';
    openLi = li;
    li.querySelector('a').style.color = '#fff';
    li.querySelector('a').style.borderBottomColor = '#CC2200';
  }

  function closePanel(){
    if(openLi){
      var a = openLi.querySelector('a');
      if(a){ a.style.color=''; a.style.borderBottomColor=''; }
    }
    panel.style.display  = 'none';
    panel3.style.display = 'none';
    openLi = null; openLi2 = null;
  }

  window.addEventListener('scroll', function(){
    if(openLi && panel.style.display !== 'none') positionPanel(openLi);
    if(openLi2 && panel3.style.display !== 'none'){
      var rP = panel.getBoundingClientRect();
      var rI = openLi2.getBoundingClientRect();
      panel3.style.top  = Math.round(rI.top)   + 'px';
      panel3.style.left = Math.round(rP.right)  + 'px';
    }
  }, {passive:true});

  nav.querySelectorAll('li[data-id]').forEach(function(li){
    var hasSub = !!li.querySelector('.hmb-sub-data');
    if(!hasSub) return;

    li.addEventListener('mouseenter', function(){
      if(isMob()) return;
      clearTimeout(closeTimer);
      openPanel(li);
    });
    li.addEventListener('mouseleave', function(){
      if(isMob()) return;
      closeTimer = setTimeout(closePanel, 200);
    });

    li.querySelector('a').addEventListener('click', function(e){
      if(!isMob()) return;
      if(openLi !== li){ e.preventDefault(); openPanel(li); }
    });
  });

  panel.addEventListener('mouseenter', function(){ clearTimeout(closeTimer); });
  panel.addEventListener('mouseleave', function(){
    if(!isMob()) closeTimer = setTimeout(closePanel, 200);
  });

  panel3.addEventListener('mouseenter', function(){ clearTimeout(closeTimer3); });
  panel3.addEventListener('mouseleave', function(){
    if(!isMob()) closeTimer3 = setTimeout(closePanel3, 150);
  });

  document.addEventListener('click', function(e){
    if(!nav.contains(e.target) && !panel.contains(e.target) && !panel3.contains(e.target)) closePanel();
  });

  function fixMobileUl(){
    var ul = nav.querySelector('.hmb-ml-ul');
    if(!ul) return;
    if(isMob()){
      ul.style.justifyContent = 'flex-start';
      ul.style.width = 'max-content';
      ul.style.minWidth = '0';
      ul.parentElement.style.overflowX = 'auto';
    } else {
      ul.style.justifyContent = 'center';
      ul.style.width = '100%';
      ul.style.minWidth = '';
      ul.parentElement.style.overflowX = 'visible';
    }
  }
  fixMobileUl();
  window.addEventListener('resize', fixMobileUl, {passive:true});

})();
</script>
