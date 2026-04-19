<?php
global $wpdb;
$can_manage = Control_Auth::has_permission('finance_manage');
$current_user = Control_Auth::current_user();

$payroll_records = array();
if ($can_manage) {
    $payroll_records = $wpdb->get_results("SELECT pr.*, s.first_name, s.last_name FROM {$wpdb->prefix}control_fin_payroll pr JOIN {$wpdb->prefix}control_staff s ON pr.specialist_id = s.id ORDER BY pr.year DESC, pr.month DESC");
} else {
    $payroll_records = $wpdb->get_results($wpdb->prepare("SELECT pr.*, s.first_name, s.last_name FROM {$wpdb->prefix}control_fin_payroll pr JOIN {$wpdb->prefix}control_staff s ON pr.specialist_id = s.id WHERE pr.specialist_id = %d ORDER BY pr.year DESC, pr.month DESC", $current_user->id));
}
?>

<div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h4 style="margin:0; font-weight:800; color:var(--control-primary);"><?php _e('مستحقات ورواتب الأخصائيين', 'control'); ?></h4>
    <?php if($can_manage): ?>
        <button id="add-payroll-btn" class="control-btn" style="background:var(--control-primary); border:none; padding:10px 25px;">
            <span class="dashicons dashicons-plus-alt" style="margin-left:5px;"></span><?php _e('توليد مسير', 'control'); ?>
        </button>
    <?php endif; ?>
</div>

<div class="control-card" style="padding:0; overflow-x:auto; border:none; box-shadow:0 10px 30px rgba(0,0,0,0.05);">
    <table class="control-table enterprise-table">
        <thead>
            <tr>
                <th><?php _e('الأخصائي', 'control'); ?></th>
                <th><?php _e('الفترة', 'control'); ?></th>
                <th><?php _e('الجلسات', 'control'); ?></th>
                <th><?php _e('الأساسي', 'control'); ?></th>
                <th><?php _e('حوافز / خصومات', 'control'); ?></th>
                <th><?php _e('الصافي', 'control'); ?></th>
                <th><?php _e('الحالة', 'control'); ?></th>
                <th style="width:120px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php if($payroll_records): foreach($payroll_records as $pr): ?>
                <tr>
                    <td class="font-bold"><?php echo esc_html($pr->first_name . ' ' . $pr->last_name); ?></td>
                    <td><?php echo $pr->month . ' / ' . $pr->year; ?></td>
                    <td><span class="session-pill"><?php echo $pr->total_sessions; ?></span></td>
                    <td><?php echo number_format($pr->base_salary, 2); ?></td>
                    <td>
                        <span class="text-success">+<?php echo number_format($pr->incentives, 2); ?></span> /
                        <span class="text-danger">-<?php echo number_format($pr->deductions, 2); ?></span>
                    </td>
                    <td class="font-bold text-primary"><?php echo number_format($pr->net_salary, 2); ?></td>
                    <td><span class="pr-status-badge status-<?php echo $pr->payment_status; ?>"><?php echo ($pr->payment_status === 'paid' ? __('مدفوع', 'control') : __('معلق', 'control')); ?></span></td>
                    <td style="text-align:left;">
                        <div style="display:flex; gap:5px; justify-content:flex-end;">
                            <button class="action-icon view-btn view-payslip-btn" data-json='<?php echo json_encode($pr); ?>'><span class="dashicons dashicons-text-page"></span></button>
                            <?php if($can_manage): ?>
                                <button class="action-icon delete-btn delete-payroll-btn" data-id="<?php echo $pr->id; ?>"><span class="dashicons dashicons-trash"></span></button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="8" class="text-center py-10"><?php _e('لا توجد سجلات رواتب.', 'control'); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.session-pill { background: #f1f5f9; padding: 2px 10px; border-radius: 8px; font-weight: 700; color: #475569; }
.pr-status-badge { font-size: 0.7rem; font-weight: 800; padding: 4px 10px; border-radius: 50px; }
.pr-status-badge.status-paid { background: #ecfdf5; color: #059669; }
.pr-status-badge.status-unpaid { background: #fff7ed; color: #d97706; }
.text-primary { color: var(--control-primary); }
.text-danger { color: #ef4444; }
</style>
