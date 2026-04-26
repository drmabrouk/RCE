<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$can_manage = Control_Auth::has_permission('finance_manage');
$expenses = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}control_fin_expenses ORDER BY expense_date DESC");
?>

<div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h4 style="margin:0; font-weight:800; color:var(--control-primary);"><?php _e('سجل المصروفات التشغيلية', 'control'); ?></h4>
    <?php if($can_manage): ?>
        <button id="add-expense-btn" class="control-btn" style="background:var(--control-primary); border:none; padding:10px 25px; border-radius:12px; font-weight:800;">
            <span class="dashicons dashicons-plus-alt" style="margin-left:5px;"></span><?php _e('تسجيل مصروف جديد', 'control'); ?>
        </button>
    <?php endif; ?>
</div>

<div class="control-card" style="padding:0; overflow:hidden; border:none; box-shadow:0 10px 30px rgba(0,0,0,0.04); border-radius:20px;">
    <table class="control-table">
        <thead>
            <tr>
                <th><?php _e('التاريخ', 'control'); ?></th>
                <th><?php _e('الفئة', 'control'); ?></th>
                <th><?php _e('الوصف', 'control'); ?></th>
                <th><?php _e('المبلغ', 'control'); ?></th>
                <th><?php _e('النوع', 'control'); ?></th>
                <th style="width:100px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php if($expenses): foreach($expenses as $exp): ?>
                <tr data-search="<?php echo esc_attr($exp->category . ' ' . $exp->description . ' ' . $exp->expense_date); ?>">
                    <td><?php echo date('Y/m/d', strtotime($exp->expense_date)); ?></td>
                    <td>
                        <span class="badge-pastel" style="background:#f1f5f9; color:#475569; font-weight:800; text-transform:uppercase; font-size:0.65rem;">
                            <?php echo esc_html($exp->category); ?>
                        </span>
                    </td>
                    <td style="max-width:300px; font-size:0.85rem; color:#64748b;"><?php echo esc_html($exp->description); ?></td>
                    <td style="font-weight:900; color:#ef4444; font-size:1rem;"><?php echo number_format($exp->amount, 2); ?></td>
                    <td>
                        <?php if($exp->is_recurring): ?>
                            <span class="badge-pastel" style="background:#fffbeb; color:#b45309; font-size:0.6rem;"><?php _e('دوري', 'control'); ?></span>
                        <?php else: ?>
                            <span class="badge-pastel" style="background:#f8fafc; color:#94a3b8; font-size:0.6rem;"><?php _e('لمرة واحدة', 'control'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:left;">
                        <?php if($can_manage): ?>
                            <div class="action-btn-group">
                                <?php if($exp->attachment_url): ?>
                                    <a href="<?php echo esc_url($exp->attachment_url); ?>" target="_blank" class="action-icon-btn" title="عرض المرفق"><span class="dashicons dashicons-paperclip"></span></a>
                                <?php endif; ?>
                                <button class="action-icon-btn delete-expense-btn" data-id="<?php echo $exp->id; ?>" title="حذف"><span class="dashicons dashicons-trash"></span></button>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="6" style="text-align:center; padding:40px; color:var(--control-muted);"><?php _e('لا توجد مصروفات مسجلة.', 'control'); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
