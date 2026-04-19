<?php
global $wpdb;
$can_manage = Control_Auth::has_permission('finance_manage');
$expenses = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}control_fin_expenses ORDER BY expense_date DESC");
$categories = array(
    'rent' => __('الإيجار', 'control'),
    'equipment' => __('الأجهزة والمعدات', 'control'),
    'utilities' => __('المرافق (كهرباء/مياه)', 'control'),
    'misc' => __('مصاريف نثرية', 'control'),
);
?>

<div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h4 style="margin:0; font-weight:800; color:var(--control-primary);"><?php _e('إدارة المصروفات التشغيلية', 'control'); ?></h4>
    <?php if($can_manage): ?>
        <button id="add-expense-btn" class="control-btn" style="background:var(--control-primary); border:none; padding:10px 25px;">
            <span class="dashicons dashicons-plus-alt" style="margin-left:5px;"></span><?php _e('إضافة مصروف', 'control'); ?>
        </button>
    <?php endif; ?>
</div>

<div class="control-card" style="padding:0; overflow-x:auto; border:none; box-shadow:0 10px 30px rgba(0,0,0,0.05);">
    <table class="control-table enterprise-table">
        <thead>
            <tr>
                <th><?php _e('التاريخ', 'control'); ?></th>
                <th><?php _e('الفئة', 'control'); ?></th>
                <th><?php _e('الوصف والبيان', 'control'); ?></th>
                <th><?php _e('المبلغ', 'control'); ?></th>
                <th style="width:100px;"><?php _e('المرفقات', 'control'); ?></th>
                <th style="width:80px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php if($expenses): foreach($expenses as $ex): ?>
                <tr>
                    <td><?php echo date('Y/m/d', strtotime($ex->expense_date)); ?></td>
                    <td><span class="cat-badge"><?php echo $categories[$ex->category] ?? $ex->category; ?></span></td>
                    <td style="max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?php echo esc_html($ex->description); ?></td>
                    <td class="font-bold text-danger"><?php echo number_format($ex->amount, 2); ?></td>
                    <td class="text-center">
                        <?php if($ex->attachment_url): ?>
                            <a href="<?php echo esc_url($ex->attachment_url); ?>" target="_blank" class="attach-link" title="عرض المرفق"><span class="dashicons dashicons-paperclip"></span></a>
                        <?php else: ?>
                            <span style="opacity:0.3">---</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:left;">
                        <?php if($can_manage): ?>
                            <button class="action-icon delete-btn delete-expense-btn" data-id="<?php echo $ex->id; ?>" title="حذف المصروف"><span class="dashicons dashicons-trash"></span></button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="6" class="text-center py-10"><?php _e('لا توجد مصروفات مسجلة.', 'control'); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.cat-badge { background: #f1f5f9; color: #475569; padding: 4px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; border: 1px solid #e2e8f0; }
.attach-link { color: var(--control-primary); display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; background: #eff6ff; transition: 0.2s; }
.attach-link:hover { background: var(--control-primary); color: #fff; }
</style>
