(function(){
'use strict';
var current=-1, cards=[], hoverTimers=new Map();
var modal, iframe, titleEl, bandEl, descEl, closeBtn, prevBtn, nextBtn;

function init(){
    modal    = document.getElementById('hmbModal');
    if(!modal) return;
    iframe   = document.getElementById('hmbModalIframe');
    titleEl  = document.getElementById('hmbModalTitle');
    bandEl   = document.getElementById('hmbModalBand');
    descEl   = document.getElementById('hmbModalDesc');
    closeBtn = document.getElementById('hmbModalClose');
    prevBtn  = document.getElementById('hmbModalPrev');
    nextBtn  = document.getElementById('hmbModalNext');

    closeBtn.addEventListener('click', close);
    modal.addEventListener('click', function(e){ if(e.target===modal) close(); });
    prevBtn.addEventListener('click', function(){ open(current-1); });
    nextBtn.addEventListener('click', function(){ open(current+1); });
    document.addEventListener('keydown', function(e){
        if(!modal.classList.contains('is-open')) return;
        if(e.key==='Escape') close();
        if(e.key==='ArrowLeft') open(current-1);
        if(e.key==='ArrowRight') open(current+1);
    });

    cards = Array.from(document.querySelectorAll('#hmb-videos-component .hmb-video-card'));
    cards.forEach(function(card, i){
        card.dataset.cardIndex = i;
        card.addEventListener('mouseenter', function(){
            var p=this.querySelector('.hmb-hover-player');
            if(!p) return;
            var t=setTimeout(function(){
                var f=p.querySelector('.hmb-hover-iframe');
                if(f&&p.dataset.src&&!f.src) f.src=p.dataset.src;
            },350);
            hoverTimers.set(this,t);
        });
        card.addEventListener('mouseleave', function(){
            clearTimeout(hoverTimers.get(this));
            var f=this.querySelector('.hmb-hover-iframe');
            if(f) f.src='';
        });
        card.addEventListener('click', function(e){
            if(e.target.tagName==='A') return;
            open(parseInt(this.dataset.cardIndex,10));
        });
    });
}

function open(idx){
    if(idx<0||idx>=cards.length) return;
    current=idx;
    var c=cards[idx];
    titleEl.textContent = c.dataset.title||'';
    bandEl.textContent  = c.dataset.band ? '🎸 '+c.dataset.band : '';
    if(descEl) descEl.textContent = c.dataset.desc||'';
    prevBtn.disabled=(current===0);
    nextBtn.disabled=(current===cards.length-1);
    iframe.src='https://www.youtube.com/embed/'+c.dataset.youtubeId+'?autoplay=1&rel=0&modestbranding=1';
    modal.classList.add('is-open');
    document.body.style.overflow='hidden';
}

function close(){
    modal.classList.remove('is-open');
    setTimeout(function(){ iframe.src=''; },350);
    document.body.style.overflow='';
}

if(document.readyState==='loading'){
    document.addEventListener('DOMContentLoaded',init);
} else { init(); }
})();
