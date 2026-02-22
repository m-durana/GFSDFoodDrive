<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Gift Tags</title>
    <style>
        /* Avery 8163 shipping labels: 2" x 4", 10 per page (2 columns, 5 rows) */
        /* Letter paper: 8.5" x 11" */
        @page {
            size: letter;
            margin: 0.5in 0.15in;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            margin: 0;
            padding: 0;
        }

        .labels-table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: auto;
        }

        .labels-table td {
            width: 50%;
            height: 2in;
            padding: 0.1in 0.15in;
            vertical-align: top;
            overflow: hidden;
            border: 1px dashed #ccc; /* guides for cutting — remove for production */
        }

        .label-content {
            height: 100%;
        }

        .family-number {
            font-size: 18pt;
            font-weight: bold;
            float: right;
            color: #333;
        }

        .child-info {
            font-size: 9pt;
            line-height: 1.3;
        }

        .child-info .field-label {
            font-weight: bold;
            color: #555;
        }

        .child-info .field-value {
            color: #000;
        }

        /* Force page break after every 10 labels (5 rows of 2) */
        .labels-table tr:nth-child(5n+1) {
            page-break-before: auto;
        }
    </style>
</head>
<body>
    <?php if($children->count() === 0): ?>
        <p style="text-align: center; padding: 2in; font-size: 14pt; color: #666;">No children match the selected filter.</p>
    <?php else: ?>
        <table class="labels-table">
            <?php $__currentLoopData = $children->chunk(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rowIndex => $pair): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                
                <?php if($rowIndex > 0 && $rowIndex % 5 === 0): ?>
                    </table>
                    <div style="page-break-before: always;"></div>
                    <table class="labels-table">
                <?php endif; ?>
                <tr>
                    <?php $__currentLoopData = $pair; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <td>
                            <div class="label-content">
                                <div class="family-number">#<?php echo e($child->family->family_number); ?></div>
                                <div class="child-info">
                                    <div><span class="field-label"><?php echo e($child->gender); ?></span>, Age <span class="field-value"><?php echo e($child->age); ?></span></div>
                                    <?php if($child->clothing_options): ?>
                                        <div><span class="field-label">Clothing:</span> <span class="field-value"><?php echo e($child->clothing_options); ?></span></div>
                                    <?php endif; ?>
                                    <?php if($child->clothing_styles): ?>
                                        <div><span class="field-label">Styles:</span> <span class="field-value"><?php echo e($child->clothing_styles); ?></span></div>
                                    <?php endif; ?>
                                    <?php if($child->all_sizes): ?>
                                        <div><span class="field-label">Sizes:</span> <span class="field-value"><?php echo e($child->all_sizes); ?></span></div>
                                    <?php endif; ?>
                                    <?php if($child->toy_ideas): ?>
                                        <div><span class="field-label">Toy Ideas:</span> <span class="field-value"><?php echo e($child->toy_ideas); ?></span></div>
                                    <?php endif; ?>
                                    <?php if($child->gift_preferences): ?>
                                        <div><span class="field-label">Gift Pref:</span> <span class="field-value"><?php echo e($child->gift_preferences); ?></span></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    
                    <?php if($pair->count() === 1): ?>
                        <td></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </table>
    <?php endif; ?>
</body>
</html>
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/documents/gift-tags.blade.php ENDPATH**/ ?>