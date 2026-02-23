<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Gift Tags — GFSD Food Drive</title>
    <style>
        /* Avery 8163 — 2 columns x 3 rows = 6 labels per letter page */
        @page {
            size: letter;
            margin: 0.5in;
        }

        body {
            font-family: 'Century Gothic', 'Trebuchet MS', Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #000;
        }

        .cards-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cards-table td.card {
            width: 48%;
            height: 3.0in;
            vertical-align: top;
            padding: 0.1in 0.15in;
            border: 1px dashed #bbb;
            overflow: hidden;
        }

        .cards-table td.spacer {
            width: 4%;
        }

        .card-number {
            font-size: 24pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 4pt;
            color: #000;
        }

        .card-field {
            font-size: 11pt;
            line-height: 1.4;
            margin-bottom: 2pt;
        }

        .card-field .label {
            font-weight: bold;
            font-size: 11pt;
        }

        .card-field .value {
            font-size: 11pt;
        }

        .card-footer {
            font-size: 8pt;
            font-weight: bold;
            text-align: center;
            margin-top: 6pt;
            padding-top: 4pt;
            border-top: 1px solid #999;
            color: #333;
            line-height: 1.3;
        }

        .card-footer .email {
            font-weight: normal;
            font-style: italic;
            font-size: 7pt;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <?php if($children->count() === 0): ?>
        <p style="text-align: center; padding: 3in 0; font-size: 18pt; color: #666;">No children match the selected filter.</p>
    <?php else: ?>
        <?php $__currentLoopData = $children->chunk(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rowIndex => $pair): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($rowIndex > 0 && $rowIndex % 3 === 0): ?>
                <div class="page-break"></div>
            <?php endif; ?>
            <table class="cards-table">
                <tr>
                    <?php $__currentLoopData = $pair; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <td class="card">
                            <table style="width: 100%; border: none; border-collapse: collapse;">
                                <tr>
                                    <td style="vertical-align: top; padding: 0; border: none;">
                                        <div class="card-number">#<?php echo e($child->family->family_number); ?></div>
                                    </td>
                                    <td style="width: 1.0in; vertical-align: top; text-align: right; padding: 0; border: none;">
                                        <?php if(isset($qrCodes[$child->id])): ?>
                                            <img src="<?php echo e($qrCodes[$child->id]); ?>" alt="QR" style="width: 0.9in; height: 0.9in;">
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>

                            <div class="card-field">
                                <span class="label">Gender:</span> <span class="value"><?php echo e($child->gender); ?></span>
                            </div>
                            <div class="card-field">
                                <span class="label">Age:</span> <span class="value"><?php echo e($child->age); ?></span>
                            </div>
                            <?php if($child->clothing_options): ?>
                                <div class="card-field">
                                    <span class="label">Clothing:</span> <span class="value"><?php echo e($child->clothing_options); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if($child->clothing_styles): ?>
                                <div class="card-field">
                                    <span class="label">Styles:</span> <span class="value"><?php echo e($child->clothing_styles); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if($child->all_sizes): ?>
                                <div class="card-field">
                                    <span class="label">Sizes:</span> <span class="value"><?php echo e($child->all_sizes); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if($child->toy_ideas): ?>
                                <div class="card-field">
                                    <span class="label">Toy Ideas:</span> <span class="value"><?php echo e($child->toy_ideas); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if($child->gift_preferences): ?>
                                <div class="card-field">
                                    <span class="label">Gift Preferences:</span> <span class="value"><?php echo e($child->gift_preferences); ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="card-footer">
                                Please bring in all gifts <u>UNWRAPPED</u><br>
                                with this tag attached.<br>
                                <span class="email">Questions? Email: fooddrive@gfalls.wednet.edu</span>
                            </div>
                        </td>
                        <?php if($loop->first && $pair->count() > 1): ?>
                            <td class="spacer"></td>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php if($pair->count() === 1): ?>
                        <td class="spacer"></td>
                        <td class="card" style="border-color: transparent;"></td>
                    <?php endif; ?>
                </tr>
            </table>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>
</body>
</html>
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/documents/gift-tags.blade.php ENDPATH**/ ?>