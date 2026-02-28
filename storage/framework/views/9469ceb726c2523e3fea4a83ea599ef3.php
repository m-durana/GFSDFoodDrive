<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Family Summary Sheets — GFSD Food Drive</title>
    <style>
        /* Matches original 708.docx - Franklin Gothic Heavy, large centered number */
        @page {
            size: letter;
            margin: 0.5in 0.75in;
        }

        body {
            font-family: 'Franklin Gothic Heavy', 'Arial Black', 'Impact', sans-serif;
            margin: 0;
            padding: 0;
            color: #000;
        }

        .family-page {
            page-break-after: always;
            text-align: center;
        }

        .family-page:last-child {
            page-break-after: auto;
        }

        .big-number {
            font-size: 72pt;
            font-weight: bold;
            text-align: center;
            margin: 0;
            padding: 10pt 0 5pt 0;
            line-height: 1;
        }

        .big-number-label {
            font-size: 20pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 5pt;
        }

        .summary-line {
            font-size: 20pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 6pt 0;
            text-align: center;
        }

        .divider {
            border: none;
            border-top: 3pt solid #000;
            margin: 8pt 0;
        }

        .age-section {
            text-align: center;
            margin: 4pt 0;
        }

        .age-label {
            font-size: 18pt;
            font-weight: bold;
            text-decoration: underline;
        }

        .age-count {
            font-size: 24pt;
            font-weight: bold;
            margin: 2pt 0 8pt 0;
        }

        .info-line {
            font-size: 18pt;
            font-weight: bold;
            text-align: center;
            margin: 6pt 0;
        }

        .info-line .field-value {
            text-decoration: underline;
            border-bottom: 2pt solid #000;
            padding-bottom: 2pt;
        }

        .boxes-section {
            margin-top: 15pt;
            text-align: center;
        }

        .boxes-label {
            font-size: 20pt;
            font-weight: bold;
        }

        .boxes-line {
            display: inline-block;
            width: 120pt;
            border-bottom: 3pt solid #000;
            margin-left: 10pt;
        }
    </style>
</head>
<body>
    <?php $__empty_1 = true; $__currentLoopData = $families; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $family): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="family-page">
            <div class="big-number-label">Family Number</div>
            <div class="big-number"><?php echo e($family->family_number); ?></div>

            <div class="summary-line">
                Total Family Members: <?php echo e($family->number_of_family_members); ?>

            </div>

            <div class="summary-line">
                Number of Children: <?php echo e($family->number_of_children); ?>

            </div>

            <hr class="divider">

            <div class="age-section">
                <div class="age-label">Infants (0-2)</div>
                <div class="age-count"><?php echo e($family->infants); ?></div>
            </div>

            <div class="age-section">
                <div class="age-label">Young Child (3-7)</div>
                <div class="age-count"><?php echo e($family->young_children); ?></div>
            </div>

            <div class="age-section">
                <div class="age-label">Child (8-12)</div>
                <div class="age-count"><?php echo e($family->children_count); ?></div>
            </div>

            <div class="age-section">
                <div class="age-label">Tween (13-14)</div>
                <div class="age-count"><?php echo e($family->tweens); ?></div>
            </div>

            <div class="age-section">
                <div class="age-label">Teenager (15-17)</div>
                <div class="age-count"><?php echo e($family->teenagers); ?></div>
            </div>

            <hr class="divider">

            <div class="info-line">
                Baby Food Needed? <span class="field-value"><?php echo e($family->needs_baby_supplies ? 'YES' : 'No'); ?></span>
            </div>

            <div class="boxes-section">
                <span class="boxes-label"># of Boxes:</span>
                <span class="boxes-line">&nbsp;</span>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p style="text-align: center; padding: 3in 0; font-size: 18pt; color: #666;">No families match the selected filter.</p>
    <?php endif; ?>
</body>
</html>
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/documents/family-summary.blade.php ENDPATH**/ ?>