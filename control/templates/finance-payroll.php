<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$can_manage = Control_Auth::has_permission('finance_manage');

// Data Binding
$payroll = $wpdb->get_results("SELECT pr.*, st.first_name, st.last_name, st.role FROM {$wpdb->prefix}control_fin_payroll pr JOIN {$wpdb->prefix}control_staff st ON pr.specialist_id = st.id ORDER BY pr.year DESC, pr.month DESC");
?>

<div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h4 style="margin:0; font-weight:900; color:var(--control-primary);"><?php _e('سجل رواتب الكادر', 'control'); ?></h4>
    <?php if($can_manage): ?>
        <button id="add-payroll-btn" class="control-btn" style="background:var(--control-primary); border:none; padding:8px 20px; border-radius:10px; font-weight:800; font-size:0.75rem;">
            <span class="dashicons dashicons-plus-alt" style="margin-left:5px;"></span><?php _e('تسجيل راتب', 'control'); ?>
        </button>
    <?php endif; ?>
</div>

<div class="control-card" style="padding:0; overflow:hidden; border-radius:16px; border:1px solid #eef2f6;">
    <table class="control-table-refined">
        <thead>
            <tr>
                <th><?php _e('الأخصائي', 'control'); ?></th>
                <th><?php _e('الفترة', 'control'); ?></th>
                <th><?php _e('الجلسات', 'control'); ?></th>
                <th><?php _e('الأساسي', 'control'); ?></th>
                <th><?php _e('حوافز/خصم', 'control'); ?></th>
                <th><?php _e('صافي الراتب', 'control'); ?></th>
                <th><?php _e('الحالة', 'control'); ?></th>
                <th style="width:100px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php if($payroll): foreach($payroll as $pr): ?>
                <tr>
                    <td>
                        <div style="font-weight:700; font-size:0.85rem;"><?php echo esc_html($pr->first_name . ' ' . $pr->last_name); ?></div>
                        <small style="color:var(--control-muted); font-size:0.65rem;"><?php echo $pr->role; ?></small>
                    </td>
                    <td><span class="badge-status-refined" style="background:#f1f5f9; color:#475569;"><?php echo $pr->month . '/' . $pr->year; ?></span></td>
                    <td style="text-align:center;"><strong><?php echo $pr->total_sessions; ?></strong></td>
                    <td><?php echo number_format($pr->base_salary, 0); ?></td>
                    <td>
                        <span class="text-success">+<?php echo number_format($pr->incentives, 0); ?></span> /
                        <span class="text-danger">-<?php echo number_format($pr->deductions, 0); ?></span>
                    </td>
                    <td class="font-bold" style="font-size:0.9rem;"><?php echo number_format($pr->net_salary, 2); ?></td>
                    <td><span class="badge-status-refined status-<?php echo $pr->payment_status; ?>"><?php echo $pr->payment_status; ?></span></td>
                    <td>
                        <?php if($can_manage): ?>
                            <div class="action-btn-group">
                                <button class="action-icon-btn edit-payroll-btn" data-id="<?php echo $pr->id; ?>"><span class="dashicons dashicons-edit"></span></button>
                                <button class="action-icon-btn delete-payroll-btn" data-id="<?php echo $pr->id; ?>" style="color:#ef4444;"><span class="dashicons dashicons-trash"></span></button>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="8" style="text-align:center; padding:30px; color:var(--control-muted);"><?php _e('لا توجد سجلات.', 'control'); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
