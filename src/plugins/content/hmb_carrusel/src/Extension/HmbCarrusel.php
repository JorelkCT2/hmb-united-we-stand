<?php
namespace Joomla\Plugin\Content\HmbCarrusel\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;

final class HmbCarrusel extends CMSPlugin
{
    protected $autoloadLanguage = false;

    public function onContentPrepare($context, &$article, &$params, $page = 0)
    {
        $field = isset($article->text) ? 'text' : (isset($article->introtext) ? 'introtext' : '');
        if (!$field) return true;

        $text = &$article->$field;
        if (strpos($text, '{hmb_carrusel}') === false) return true;

        $altura   = (int) $this->params->get('altura', 420);
        $autoplay = (int) $this->params->get('autoplay', 1);
        $interval = (int) $this->params->get('interval', 4000);
        $thumbs   = (int) $this->params->get('thumbs', 1);
        $uid      = 0;

        $text = preg_replace_callback(
            '/\{hmb_carrusel\}(.*?)\{\/hmb_carrusel\}/s',
            function ($m) use ($altura, $autoplay, $interval, $thumbs, &$uid, $article) {
                $uid++;
                $id    = 'hmbc-' . (int)(isset($article->id) ? $article->id : 0) . '-' . $uid;
                $fotos = $this->parseLines($m[1]);
                if (empty($fotos)) return '';
                return $this->render($id, $fotos, $altura, $autoplay, $interval, $thumbs);
            },
            $text
        );

        return true;
    }

    private function parseLines($raw)
    {
        $fotos = [];
        foreach (explode("\n", $raw) as $line) {
            $line = trim(strip_tags($line));
            if ($line === '' || $line[0] === '#') continue;
            $parts  = explode('|', $line, 3);
            $src    = trim($parts[0]);
            $titulo = isset($parts[1]) ? trim($parts[1]) : '';
            $fuente = isset($parts[2]) ? trim($parts[2]) : '';

            // Auto-extraer dominio si hay URL y no hay fuente
            if ($fuente === '' && preg_match('/^https?:\/\//i', $src)) {
                $parsed = parse_url($src);
                if (!empty($parsed['host'])) {
                    $fuente = ($parsed['scheme'] ?? 'https') . '://' . $parsed['host'];
                }
            }

            if ($src === '') continue;
            if (!preg_match('/^https?:\/\//i', $src)) {
                $src = rtrim(Uri::root(), '/') . '/' . ltrim($src, '/');
            }
            $fotos[] = [
                'src'    => htmlspecialchars($src, ENT_QUOTES, 'UTF-8'),
                'titulo' => htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'),
                'fuente' => htmlspecialchars($fuente, ENT_QUOTES, 'UTF-8'),
            ];
        }
        return $fotos;
    }

    private function render($id, $fotos, $altura, $autoplay, $interval, $thumbs)
    {
        $n      = count($fotos);
        $altS   = (int) round($altura * 0.6);
        $thW    = (int) round($altura * 0.18);
        $apJs   = $autoplay ? 'true' : 'false';
        $thBot  = $thumbs ? 'calc(var(--cth) + 10px)' : '10px';
        $json   = json_encode(array_values($fotos));

        $h  = '<style>';
        $h .= '#'.$id.'{--cr:#CC2200;--cr2:#8B0000;--cdk:#050000;--cdk2:#0a0000;--cth:70px;font-family:\'Oswald\',Arial,sans-serif;user-select:none;margin:2rem 0;}';
        $h .= '.'.$id.'-wrap{background:var(--cdk);border:2px solid var(--cr2);border-top:3px solid var(--cr);overflow:hidden;}';
        $h .= '.'.$id.'-track{position:relative;width:100%;height:'.$altura.'px;overflow:hidden;cursor:zoom-in;}';
        $h .= '@media(max-width:600px){.'.$id.'-track{height:'.$altS.'px;}}';
        $h .= '.'.$id.'-sl{position:absolute;inset:0;opacity:0;transition:opacity .5s ease;z-index:0;}';
        $h .= '.'.$id.'-sl.on{opacity:1;z-index:2;}';
        $h .= '.'.$id.'-sl.out{opacity:0;z-index:1;}';
        $h .= '.'.$id.'-sl img{width:100%;height:100%;object-fit:contain;display:block;}';
        $h .= '.'.$id.'-ovl{position:absolute;bottom:0;left:0;right:0;height:80px;background:linear-gradient(to top,rgba(5,0,0,.9),transparent);pointer-events:none;z-index:3;}';
        $h .= '.'.$id.'-cap{position:absolute;bottom:'.$thBot.';left:0;right:0;text-align:center;z-index:5;padding:0 56px;}';
        $h .= '.'.$id.'-cap span{font-family:\'Metal Mania\',cursive,\'Oswald\',sans-serif;font-size:clamp(13px,2vw,18px);color:#fff;letter-spacing:2px;text-shadow:0 0 12px rgba(204,34,0,.8);}';
        $h .= '.'.$id.'-btn{position:absolute;top:50%;transform:translateY(-50%);z-index:10;background:rgba(5,0,0,.75);border:1px solid var(--cr2);color:var(--cr);font-size:26px;width:42px;height:42px;display:flex;align-items:center;justify-content:center;cursor:pointer;border-radius:2px;transition:background .2s,color .2s;line-height:1;}';
        $h .= '.'.$id.'-btn:hover{background:var(--cr2);color:#fff;}';
        $h .= '.'.$id.'-prev{left:8px;}.'.$id.'-next{right:8px;}';
        $h .= '.'.$id.'-bar{position:absolute;bottom:0;left:0;height:2px;background:var(--cr);z-index:6;width:0;}';
        $h .= '.'.$id.'-wm{position:absolute;bottom:10px;right:10px;z-index:7;background:rgba(0,0,0,.55);color:rgba(255,255,255,.65);font-family:\'Oswald\',sans-serif;font-size:9px;letter-spacing:1px;text-transform:uppercase;padding:3px 10px 3px 8px;border-radius:20px;backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);pointer-events:none;white-space:nowrap;max-width:200px;overflow:hidden;text-overflow:ellipsis;opacity:0;transition:opacity .4s .2s;}';
        $h .= '.'.$id.'-sl.on .'.$id.'-wm{opacity:1;}';
        $h .= '.'.$id.'-dots{display:flex;justify-content:center;gap:6px;padding:7px 0 5px;background:var(--cdk2);}';
        $h .= '.'.$id.'-dot{width:8px;height:8px;border-radius:50%;background:#333;border:1px solid #2a0000;cursor:pointer;transition:background .2s,transform .2s;}';
        $h .= '.'.$id.'-dot.on{background:var(--cr);transform:scale(1.3);}';
        if ($thumbs) {
            $h .= '.'.$id.'-ths{display:flex;justify-content:center;gap:3px;background:var(--cdk2);padding:4px;border-top:2px solid var(--cr2);overflow-x:auto;scrollbar-width:thin;scrollbar-color:var(--cr2) var(--cdk);height:var(--cth);box-sizing:content-box;}';
            $h .= '.'.$id.'-th{flex:0 0 auto;width:'.$thW.'px;height:var(--cth);overflow:hidden;cursor:pointer;border:2px solid transparent;transition:border-color .2s,opacity .2s;opacity:.5;}';
            $h .= '.'.$id.'-th.on{border-color:var(--cr);opacity:1;}';
            $h .= '.'.$id.'-th img{width:100%;height:100%;object-fit:cover;display:block;}';
            $h .= '.'.$id.'-th:hover{opacity:.85;}';
        }
        $h .= '.'.$id.'-lb{display:none;position:fixed;inset:0;background:rgba(0,0,0,.93);z-index:99999;align-items:center;justify-content:center;flex-direction:column;}';
        $h .= '.'.$id.'-lb.on{display:flex;}';
        $h .= '.'.$id.'-lbi{max-width:95vw;max-height:85vh;width:auto;height:auto;object-fit:contain;border:2px solid var(--cr2);box-shadow:0 0 60px rgba(139,0,0,.5);}';
        $h .= '.'.$id.'-lbx{position:fixed;top:14px;right:14px;background:var(--cr2);border:1px solid var(--cr);color:#fff;font-size:22px;width:42px;height:42px;display:flex;align-items:center;justify-content:center;cursor:pointer;border-radius:2px;z-index:100000;}';
        $h .= '.'.$id.'-lbx:hover{background:var(--cr);}';
        $h .= '.'.$id.'-lbpn{position:fixed;top:50%;transform:translateY(-50%);background:rgba(5,0,0,.8);border:1px solid var(--cr2);color:var(--cr);font-size:30px;width:50px;height:50px;display:flex;align-items:center;justify-content:center;cursor:pointer;border-radius:2px;z-index:100000;}';
        $h .= '.'.$id.'-lbpn:hover{background:var(--cr2);color:#fff;}';
        $h .= '.'.$id.'-lbp{left:8px;}.'.$id.'-lbn{right:8px;}';
        $h .= '.'.$id.'-lbf{text-align:center;margin-top:10px;}';
        $h .= '.'.$id.'-lbf p{font-family:\'Metal Mania\',cursive;color:#bbb;font-size:14px;letter-spacing:2px;margin:0 0 3px;}';
        $h .= '.'.$id.'-lbf small{font-family:\'Oswald\',sans-serif;color:#444;font-size:11px;letter-spacing:2px;}';
        $h .= '</style>';

        $h .= '<div id="'.$id.'"><div class="'.$id.'-wrap"><div class="'.$id.'-track" id="'.$id.'-track">';
        foreach ($fotos as $i => $f) {
            $cls    = $i === 0 ? ' on' : '';
            $wmHtml = '';
            if (!empty($f['fuente'])) {
                $wmHtml = '<div class="'.$id.'-wm">&#9670; '.$f['fuente'].'</div>';
            }
            $h .= '<div class="'.$id.'-sl'.$cls.'" data-i="'.$i.'">'
                . '<img src="'.$f['src'].'" alt="'.($f['titulo'] ?: 'Foto '.($i+1)).'" loading="eager">'
                . $wmHtml
                . '</div>';
        }
        $h .= '<div class="'.$id.'-ovl"></div>';
        $h .= '<div class="'.$id.'-cap" id="'.$id.'-cap"><span>'.$fotos[0]['titulo'].'</span></div>';
        $h .= '<div class="'.$id.'-bar" id="'.$id.'-bar"></div>';
        $h .= '<button class="'.$id.'-btn '.$id.'-prev">&#8249;</button>';
        $h .= '<button class="'.$id.'-btn '.$id.'-next">&#8250;</button>';
        $h .= '</div>';
        $h .= '<div class="'.$id.'-dots">';
        for ($i = 0; $i < $n; $i++) {
            $h .= '<div class="'.$id.'-dot'.($i===0?' on':'').'" data-i="'.$i.'"></div>';
        }
        $h .= '</div>';
        if ($thumbs) {
            $h .= '<div class="'.$id.'-ths">';
            foreach ($fotos as $i => $f) {
                $h .= '<div class="'.$id.'-th'.($i===0?' on':'').'" data-i="'.$i.'"><img src="'.$f['src'].'" alt="" loading="eager"></div>';
            }
            $h .= '</div>';
        }
        $h .= '</div></div>';

        $h .= '<div class="'.$id.'-lb" id="'.$id.'-lb">';
        $h .= '<button class="'.$id.'-lbx" id="'.$id.'-lbx">&#10005;</button>';
        $h .= '<button class="'.$id.'-lbpn '.$id.'-lbp" id="'.$id.'-lbp">&#8249;</button>';
        $h .= '<button class="'.$id.'-lbpn '.$id.'-lbn" id="'.$id.'-lbn">&#8250;</button>';
        $h .= '<img src="" alt="" class="'.$id.'-lbi" id="'.$id.'-lbi">';
        $h .= '<div class="'.$id.'-lbf"><p id="'.$id.'-lbc"></p><small id="'.$id.'-lbk"></small></div>';
        $h .= '</div>';

        $h .= '<script>(function(){';
        $h .= 'var ID="'.$id.'",F='.$json.',N=F.length,AP='.$apJs.',IV='.$interval.';';
        $h .= 'if(!N)return;';
        $h .= 'var c=0,lc=0,t=null;';
        $h .= 'function g(s){return document.getElementById(ID+s);}';
        $h .= 'function qa(s){return document.getElementById(ID).querySelectorAll("."+ID+s);}';
        $h .= 'var sls=qa("-sl"),dots=qa("-dot"),ths=qa("-th");';
        $h .= 'var cap=g("-cap"),bar=g("-bar"),lb=g("-lb"),lbi=g("-lbi"),lbc=g("-lbc"),lbk=g("-lbk");';
        $h .= 'function go(n,u){';
        $h .= '  var prev=c;';
        $h .= '  sls[prev].classList.remove("on");';
        $h .= '  sls[prev].classList.add("out");';
        $h .= '  setTimeout(function(){sls[prev].classList.remove("out");},520);';
        $h .= '  dots[prev]&&dots[prev].classList.remove("on");';
        $h .= '  ths[prev]&&ths[prev].classList.remove("on");';
        $h .= '  c=(n+N)%N;';
        $h .= '  sls[c].classList.add("on");';
        $h .= '  dots[c]&&dots[c].classList.add("on");';
        $h .= '  if(ths[c]){';
        $h .= '    ths[c].classList.add("on");';
        $h .= '    var thsEl=document.querySelector("#"+ID+" ."+ID+"-ths");';
        $h .= '    if(thsEl){';
        $h .= '      var tRect=ths[c].getBoundingClientRect();';
        $h .= '      var cRect=thsEl.getBoundingClientRect();';
        $h .= '      var offset=(tRect.left-cRect.left)-(thsEl.offsetWidth/2)+(ths[c].offsetWidth/2);';
        $h .= '      thsEl.scrollLeft+=offset;';
        $h .= '    }';
        $h .= '  }';
        $h .= '  if(cap)cap.querySelector("span").textContent=F[c].titulo||"";';
        $h .= '  if(bar&&AP){bar.style.transition="none";bar.style.width="0";void bar.offsetWidth;bar.style.transition="width "+IV+"ms linear";bar.style.width="100%";}';
        $h .= '  if(u&&AP)play();';
        $h .= '}';
        $h .= 'function play(){clearInterval(t);t=setInterval(function(){go(c+1,false);},IV);if(bar){bar.style.transition="none";bar.style.width="0";void bar.offsetWidth;bar.style.transition="width "+IV+"ms linear";bar.style.width="100%";}}';
        $h .= 'function stop(){clearInterval(t);if(bar){bar.style.transition="none";bar.style.width="0";}}';
        $h .= 'var tr=g("-track");';
        $h .= 'if(tr){tr.addEventListener("mouseenter",stop);tr.addEventListener("mouseleave",function(){if(AP)play();});tr.addEventListener("click",function(){olb(c);});}';
        $h .= 'document.querySelector("#"+ID+" ."+ID+"-prev").addEventListener("click",function(e){e.stopPropagation();go(c-1,true);});';
        $h .= 'document.querySelector("#"+ID+" ."+ID+"-next").addEventListener("click",function(e){e.stopPropagation();go(c+1,true);});';
        $h .= 'dots.forEach(function(d){d.addEventListener("click",function(){go(+this.dataset.i,true);});});';
        $h .= 'ths.forEach(function(h){h.addEventListener("click",function(){go(+this.dataset.i,true);});});';
        $h .= 'function olb(i){lc=i;ulb();lb.classList.add("on");document.body.style.overflow="hidden";stop();}';
        $h .= 'function clb(){lb.classList.remove("on");document.body.style.overflow="";if(AP)play();}';
        $h .= 'function glb(d){lc=(lc+d+N)%N;ulb();}';
        $h .= 'function ulb(){lbi.src=F[lc].src;lbi.alt=F[lc].titulo||"";if(lbc)lbc.textContent=F[lc].titulo||"";if(lbk)lbk.textContent=(lc+1)+" / "+N+(F[lc].fuente?" · "+F[lc].fuente:"");}';
        $h .= 'g("-lbx").addEventListener("click",clb);g("-lbp").addEventListener("click",function(){glb(-1);});g("-lbn").addEventListener("click",function(){glb(1);});';
        $h .= 'lb.addEventListener("click",function(e){if(e.target===lb)clb();});';
        $h .= 'document.addEventListener("keydown",function(e){if(!lb.classList.contains("on"))return;if(e.key==="Escape")clb();if(e.key==="ArrowLeft")glb(-1);if(e.key==="ArrowRight")glb(1);});';
        $h .= 'function sw(el,oL,oR){var sx=0;el.addEventListener("touchstart",function(e){sx=e.touches[0].clientX;},{passive:true});el.addEventListener("touchend",function(e){var dx=e.changedTouches[0].clientX-sx;if(Math.abs(dx)>40){if(dx<0)oL();else oR();}},{passive:true});}';
        $h .= 'if(tr)sw(tr,function(){go(c+1,true);},function(){go(c-1,true);});';
        $h .= 'sw(lb,function(){glb(1);},function(){glb(-1);});';
        $h .= 'go(0,false);if(AP)play();';
        $h .= '})();</script>';

        return $h;
    }
}
