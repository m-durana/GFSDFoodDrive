{{-- Grinch peeking overscroll effect --}}
<div id="grinch-top" style="position:fixed;top:-200px;left:50%;transform:translateX(-50%);z-index:9999;pointer-events:none;transition:none;">
    <img src="{{ asset('images/grinch_peeking.png') }}" alt="" style="height:200px;transform:scaleY(-1);" draggable="false">
</div>
<div id="grinch-bottom" style="position:fixed;bottom:-200px;left:50%;transform:translateX(-50%);z-index:9999;pointer-events:none;transition:none;">
    <img src="{{ asset('images/grinch_peeking.png') }}" alt="" style="height:200px;" draggable="false">
</div>
<script>
(function(){
    var top=document.getElementById('grinch-top'),
        bot=document.getElementById('grinch-bottom'),
        ticking=false;
    function update(){
        var y=window.pageYOffset||document.documentElement.scrollTop;
        var maxScroll=document.documentElement.scrollHeight-window.innerHeight;
        if(y<0){
            var show=Math.min(Math.abs(y),200);
            top.style.top=(-200+show)+'px';
        } else {
            top.style.top='-200px';
        }
        if(y>maxScroll){
            var over=Math.min(y-maxScroll,200);
            bot.style.bottom=(-200+over)+'px';
        } else {
            bot.style.bottom='-200px';
        }
        ticking=false;
    }
    window.addEventListener('scroll',function(){
        if(!ticking){ticking=true;requestAnimationFrame(update);}
    });
    // Touch overscroll for mobile (touchmove beyond bounds)
    var startY=0,startScroll=0;
    document.addEventListener('touchstart',function(e){
        startY=e.touches[0].clientY;
        startScroll=window.pageYOffset;
    },{passive:true});
    document.addEventListener('touchmove',function(e){
        var dy=e.touches[0].clientY-startY;
        var maxScroll=document.documentElement.scrollHeight-window.innerHeight;
        if(startScroll<=0&&dy>0){
            var show=Math.min(dy,200);
            top.style.top=(-200+show)+'px';
        }
        if(startScroll>=maxScroll&&dy<0){
            var show=Math.min(Math.abs(dy),200);
            bot.style.bottom=(-200+show)+'px';
        }
    },{passive:true});
    document.addEventListener('touchend',function(){
        top.style.top='-200px';
        bot.style.bottom='-200px';
    },{passive:true});
})();
</script>
