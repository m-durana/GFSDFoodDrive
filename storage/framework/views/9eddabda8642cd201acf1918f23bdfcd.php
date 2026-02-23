<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Volunteer List — <?php echo e($volunteerName); ?> — GFSD Food Drive</title>
    <style>
        @page {
            size: letter;
            margin: 0.5in 0.75in;
        }

        body {
            font-family: 'Segoe UI', 'Trebuchet MS', Arial, sans-serif;
            font-size: 11pt;
            margin: 0;
            padding: 0;
            color: #000;
        }

        .header {
            text-align: center;
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 6pt;
            padding-bottom: 6pt;
            border-bottom: 2pt solid #000;
        }

        .header .volunteer-name {
            font-size: 14pt;
            font-weight: normal;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10pt;
        }

        th {
            background: #e0e0e0;
            font-size: 10pt;
            font-weight: bold;
            text-align: left;
            padding: 4pt 6pt;
            border: 1pt solid #999;
        }

        td {
            font-size: 10pt;
            padding: 4pt 6pt;
            border: 1pt solid #ccc;
            vertical-align: top;
        }

        .family-row {
            background: #f5f5f5;
            font-weight: bold;
        }

        .child-row td {
            padding-left: 20pt;
            font-size: 9pt;
        }

        .child-row td:first-child {
            padding-left: 6pt;
        }

        .notes-section {
            margin-top: 20pt;
            font-size: 10pt;
        }

        .notes-line {
            border-bottom: 1pt solid #000;
            height: 20pt;
            margin-bottom: 4pt;
        }

        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="padding: 10px; background: #eee; text-align: center; margin-bottom: 10px;">
        <button onclick="window.print()" style="padding: 8px 20px; font-size: 14px; cursor: pointer;">Print This List</button>
    </div>

    <div class="header">
        GFSD Food Drive — Volunteer List
        <br>
        <span class="volunteer-name"><?php echo e($volunteerName); ?></span>
    </div>

    <?php if($families->count() > 0): ?>
        <table>
            <thead>
                <tr>
                    <th style="width: 40pt;">#</th>
                    <th>Family / Child</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Delivery</th>
                    <th style="width: 50pt;">Done</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $families; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $family): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="family-row">
                        <td><?php echo e($family->family_number); ?></td>
                        <td><?php echo e($family->family_name); ?></td>
                        <td><?php echo e($family->phone1); ?></td>
                        <td><?php echo e($family->address); ?></td>
                        <td><?php echo e($family->delivery_preference ?? '-'); ?></td>
                        <td style="text-align: center;">
                            <?php if($family->family_done): ?>
                                &#10004;
                            <?php else: ?>
                                &#9744;
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php $__currentLoopData = $family->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="child-row">
                            <td></td>
                            <td><?php echo e($child->gender); ?>, age <?php echo e($child->age); ?> — <?php echo e($child->school ?? 'No school'); ?></td>
                            <td colspan="2">
                                <?php if($child->toy_ideas): ?>Toys: <?php echo e($child->toy_ideas); ?><?php endif; ?>
                                <?php if($child->all_sizes): ?> | Sizes: <?php echo e($child->all_sizes); ?><?php endif; ?>
                            </td>
                            <td><?php echo e($child->gift_level?->label() ?? 'None'); ?></td>
                            <td></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align: center; padding: 2in 0; font-size: 14pt; color: #666;">No families assigned to this volunteer.</p>
    <?php endif; ?>

    <div class="notes-section">
        <strong>Notes:</strong>
        <div class="notes-line"></div>
        <div class="notes-line"></div>
        <div class="notes-line"></div>
    </div>
</body>
</html>
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/documents/volunteer-list.blade.php ENDPATH**/ ?>