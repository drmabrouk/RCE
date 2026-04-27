<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$can_manage = Control_Auth::has_permission('finance_manage');

// Data Binding
$expenses = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}control_fin_expenses ORDER BY expense_date DESC");
?>

<div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h4 style="margin:0; font-weight:900; color:var(--control-primary);"><?php _e('سجل المصروفات', 'control'); ?></h4>
    <?php if($can_manage): ?>
        <button id="add-expense-btn" class="control-btn" style="background:var(--control-primary); border:none; padding:8px 20px; border-radius:10px; font-weight:800; font-size:0.75rem;">
            <span class="dashicons dashicons-plus-alt" style="margin-left:5px;"></span><?php _e('تسجيل مصروف', 'control'); ?>
        </button>
    <?php endif; ?>
</div>

<div class="control-card" style="padding:0; overflow:hidden; border-radius:16px; border:1px solid #eef2f6;">
    <table class="control-table-refined">
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
                <tr>
                    <td style="font-size:0.75rem; color:var(--control-muted);"><?php echo date('Y/m/d', strtotime($exp->expense_date)); ?></td>
                    <td><span class="badge-status-refined" style="background:#f1f5f9; color:#475569; font-size:0.65rem;"><?php echo esc_html($exp->category); ?></span></td>
                    <td style="max-width:250px; font-size:0.8rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?php echo esc_html($exp->description); ?></td>
                    <td class="font-bold text-danger"><?php echo number_format($exp->amount, 2); ?></td>
                    <td>
                        <?php if($exp->is_recurring): ?>
                            <span class="badge-status-refined" style="background:#fffbeb; color:#b45309;"><?php _e('دوري', 'control'); ?></span>
                        <?php else: ?>
                            <span class="badge-status-refined" style="background:#f8fafc; color:#94a3b8;"><?php _e('مرة واحدة', 'control'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($can_manage): ?>
                            <div class="action-btn-group">
                                <?php if($exp->attachment_url): ?>
                                    <a href="<?php echo esc_url($exp->attachment_url); ?>" target="_blank" class="action-icon-btn"><span class="dashicons dashicons-paperclip"></span></a>
                                <?php endif; ?>
                                <button class="action-icon-btn delete-expense-btn" data-id="<?php echo $exp->id; ?>" style="color:#ef4444;"><span class="dashicons dashicons-trash"></span></button>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="6" style="text-align:center; padding:30px; color:var(--control-muted);"><?php _e('لا توجد مصروفات.', 'control'); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
