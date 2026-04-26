<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$can_manage = Control_Auth::has_permission('finance_manage');
$can_view = Control_Auth::has_permission('finance_payroll_view');

$specialists = $wpdb->get_results("SELECT id, first_name, last_name, role FROM {$wpdb->prefix}control_staff WHERE role IN ('occupational_therapist', 'physical_rehab', 'sports_therapy', 'speech_therapist', 'behavior_modification', 'psych_assessor') ORDER BY first_name ASC");
$payroll = $wpdb->get_results("SELECT pr.*, st.first_name, st.last_name, st.role FROM {$wpdb->prefix}control_fin_payroll pr JOIN {$wpdb->prefix}control_staff st ON pr.specialist_id = st.id ORDER BY pr.year DESC, pr.month DESC");
?>

<div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h4 style="margin:0; font-weight:800; color:var(--control-primary);"><?php _e('سجل رواتب الكادر الفني', 'control'); ?></h4>
    <?php if($can_manage): ?>
        <button id="add-payroll-btn" class="control-btn" style="background:var(--control-primary); border:none; padding:10px 25px; border-radius:12px; font-weight:800;">
            <span class="dashicons dashicons-plus-alt" style="margin-left:5px;"></span><?php _e('تسجيل راتب شهر', 'control'); ?>
        </button>
    <?php endif; ?>
</div>

<div class="control-card" style="padding:0; overflow:hidden; border:none; box-shadow:0 10px 30px rgba(0,0,0,0.04); border-radius:20px;">
    <table class="control-table">
        <thead>
            <tr>
                <th><?php _e('الأخصائي', 'control'); ?></th>
                <th><?php _e('الفترة', 'control'); ?></th>
                <th><?php _e('إجمالي الجلسات', 'control'); ?></th>
                <th><?php _e('الراتب الأساسي', 'control'); ?></th>
                <th><?php _e('الحوافز/الخصم', 'control'); ?></th>
                <th><?php _e('صافي الراتب', 'control'); ?></th>
                <th><?php _e('الحالة', 'control'); ?></th>
                <th style="width:100px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php if($payroll): foreach($payroll as $pr): ?>
                <tr data-search="<?php echo esc_attr($pr->first_name . ' ' . $pr->last_name . ' ' . $pr->role . ' ' . $pr->month . '-' . $pr->year); ?>">
                    <td>
                        <div style="font-weight:700;"><?php echo esc_html($pr->first_name . ' ' . $pr->last_name); ?></div>
                        <small style="color:var(--control-muted);"><?php echo $pr->role; ?></small>
                    </td>
                    <td><span class="badge-pastel" style="background:#f1f5f9; color:#475569; font-weight:800;"><?php echo $pr->month . ' / ' . $pr->year; ?></span></td>
                    <td style="text-align:center; font-weight:800;"><?php echo $pr->total_sessions; ?></td>
                    <td><?php echo number_format($pr->base_salary, 2); ?></td>
                    <td>
                        <span style="color:#10b981;">+<?php echo number_format($pr->incentives, 2); ?></span> /
                        <span style="color:#ef4444;">-<?php echo number_format($pr->deductions, 2); ?></span>
                    </td>
                    <td style="font-weight:900; color:var(--control-primary); font-size:1rem;"><?php echo number_format($pr->net_salary, 2); ?></td>
                    <td>
                        <span class="patient-status-badge status-<?php echo $pr->payment_status; ?>" style="font-size:0.65rem; padding:4px 12px;">
                            <?php echo $pr->payment_status === 'paid' ? __('مدفوع', 'control') : __('معلق', 'control'); ?>
                        </span>
                    </td>
                    <td style="text-align:left;">
                        <?php if($can_manage): ?>
                            <div class="action-btn-group">
                                <button class="action-icon-btn edit-payroll-btn" data-id="<?php echo $pr->id; ?>"><span class="dashicons dashicons-edit"></span></button>
                                <button class="action-icon-btn delete-payroll-btn" data-id="<?php echo $pr->id; ?>"><span class="dashicons dashicons-trash"></span></button>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="8" style="text-align:center; padding:40px; color:var(--control-muted);"><?php _e('لا توجد سجلات رواتب.', 'control'); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
