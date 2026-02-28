<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $__env->yieldContent('code'); ?> - <?php echo $__env->yieldContent('title'); ?> | GFSD Food Drive</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .snowflake {
            position: fixed;
            color: #fff;
            font-size: 1.5em;
            animation: fall linear infinite;
            opacity: 0.7;
            pointer-events: none;
            z-index: 0;
        }
        @keyframes fall {
            0% { transform: translateY(-10vh) rotate(0deg); }
            100% { transform: translateY(105vh) rotate(360deg); }
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-b from-red-900 via-red-800 to-green-900 flex items-center justify-center relative overflow-hidden">
    
    <script>
        for(var i=0;i<30;i++){
            var s=document.createElement('div');
            s.className='snowflake';
            s.textContent='*';
            s.style.left=Math.random()*100+'vw';
            s.style.animationDuration=(3+Math.random()*5)+'s';
            s.style.animationDelay=Math.random()*5+'s';
            s.style.fontSize=(10+Math.random()*20)+'px';
            s.style.opacity=0.3+Math.random()*0.5;
            document.body.appendChild(s);
        }
    </script>

    <div class="relative z-10 text-center px-6">
        
        <div class="text-[10rem] sm:text-[14rem] font-black text-white/20 leading-none select-none">
            <?php echo $__env->yieldContent('code'); ?>
        </div>

        
        <div class="flex items-center justify-center space-x-2 -mt-8 mb-6">
            <span class="text-3xl">🎄</span>
            <div class="h-0.5 w-16 bg-white/30"></div>
            <span class="text-4xl"><?php echo $__env->yieldContent('emoji', '🎅'); ?></span>
            <div class="h-0.5 w-16 bg-white/30"></div>
            <span class="text-3xl">🎄</span>
        </div>

        <h1 class="text-3xl sm:text-4xl font-bold text-white mb-3"><?php echo $__env->yieldContent('title'); ?></h1>
        <p class="text-lg text-red-200 mb-8 max-w-md mx-auto"><?php echo $__env->yieldContent('message'); ?></p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="<?php echo e(url('/')); ?>" class="inline-flex items-center px-6 py-3 bg-white text-red-800 font-semibold rounded-full shadow-lg hover:bg-red-50 transition">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                Back to Home
            </a>
            <a href="javascript:history.back()" class="inline-flex items-center px-6 py-3 border-2 border-white/40 text-white font-semibold rounded-full hover:bg-white/10 transition">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" /></svg>
                Go Back
            </a>
        </div>

        <p class="mt-12 text-sm text-white/40">&copy; <?php echo e(date('Y')); ?> GFSD Food Drive</p>
    </div>
</body>
</html>
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/errors/layout.blade.php ENDPATH**/ ?>