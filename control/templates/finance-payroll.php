<?php
global $wpdb;
$can_manage = Control_Auth::has_permission('finance_manage');
$current_user = Control_Auth::current_user();

$payroll_records = array();
if ($can_manage) {
    $payroll_records = $wpdb->get_results("SELECT pr.*, s.first_name, s.last_name FROM {$wpdb->prefix}control_fin_payroll pr JOIN {$wpdb->prefix}control_staff s ON pr.specialist_id = s.id ORDER BY pr.year DESC, pr.month DESC");
    $specialists = $wpdb->get_results("SELECT id, first_name, last_name FROM {$wpdb->prefix}control_staff WHERE role IN ('therapist', 'coach', 'specialist') ORDER BY first_name ASC");
} else {
    // Only show their own payroll
    $payroll_records = $wpdb->get_results($wpdb->prepare("SELECT pr.*, s.first_name, s.last_name FROM {$wpdb->prefix}control_fin_payroll pr JOIN {$wpdb->prefix}control_staff s ON pr.specialist_id = s.id WHERE pr.specialist_id = %d ORDER BY pr.year DESC, pr.month DESC", $current_user->id));
}
?>

<div style="display:flex; justify-content: flex-end; margin-bottom: 20px;">
    <?php if($can_manage): ?>
        <button id="add-payroll-btn" class="control-btn" style="background:var(--control-primary); border:none;">
            <span class="dashicons dashicons-plus-alt" style="margin-left:5px;"></span><?php _e('إنشاء مسير راتب', 'control'); ?>
        </button>
    <?php endif; ?>
</div>

<div class="control-card" style="padding:0; overflow:hidden;">
    <table class="control-table">
        <thead>
            <tr>
                <th><?php _e('الأخصائي', 'control'); ?></th>
                <th><?php _e('الشهر / السنة', 'control'); ?></th>
                <th><?php _e('إجمالي الجلسات', 'control'); ?></th>
                <th><?php _e('صافي المستحق', 'control'); ?></th>
                <th><?php _e('الحالة', 'control'); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if($payroll_records): foreach($payroll_records as $pr): ?>
                <tr>
                    <td><strong><?php echo esc_html($pr->first_name . ' ' . $pr->last_name); ?></strong></td>
                    <td><?php echo $pr->month . ' / ' . $pr->year; ?></td>
                    <td><?php echo $pr->total_sessions; ?></td>
                    <td><?php echo number_format($pr->net_salary, 2); ?></td>
                    <td><span class="payroll-status status-<?php echo $pr->payment_status; ?>"><?php echo ($pr->payment_status === 'paid' ? __('تم الصرف', 'control') : __('معلق', 'control')); ?></span></td>
                    <td style="text-align:left;">
                        <button class="control-btn view-payslip-btn" data-json='<?php echo json_encode($pr); ?>' style="padding:4px 10px; font-size:0.75rem; background:#fff; color:var(--control-text-dark) !important; border:1px solid var(--control-border);"><?php _e('كشف تفصيلي', 'control'); ?></button>
                        <?php if($can_manage): ?>
                            <button class="delete-payroll-btn" data-id="<?php echo $pr->id; ?>" style="background:none; border:none; color:#ef4444; cursor:pointer;"><span class="dashicons dashicons-trash"></span></button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="6" style="text-align:center; padding:30px; color:var(--control-muted);"><?php _e('لا توجد سجلات رواتب.', 'control'); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Payroll Entry Modal -->
<?php if($can_manage): ?>
<div id="payroll-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10006; align-items:center; justify-content:center; backdrop-filter: blur(4px);">
    <div class="control-card" style="width:100%; max-width:600px; padding:0; border-radius:20px; overflow:hidden;">
        <div style="background:var(--control-primary); color:#fff; padding:20px 30px; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="color:#fff; margin:0; font-size:1.1rem;"><?php _e('إدخال مسير راتب جديد', 'control'); ?></h3>
            <button onclick="jQuery('#payroll-modal').hide()" style="background:none; border:none; color:#fff; cursor:pointer;"><span class="dashicons dashicons-no-alt"></span></button>
        </div>
        <form id="payroll-form" style="padding:30px;">
            <div class="control-grid" style="grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
                <div class="control-form-group">
                    <select name="specialist_id" id="pr-specialist" required>
                        <?php foreach($specialists as $s): ?>
                            <option value="<?php echo $s->id; ?>"><?php echo esc_html($s->first_name . ' ' . $s->last_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label><?php _e('الأخصائي', 'control'); ?></label>
                </div>
                <div style="display:flex; gap:10px;">
                    <div class="control-form-group" style="flex:1;">
                        <input type="number" name="month" value="<?php echo date('m'); ?>" min="1" max="12" required>
                        <label><?php _e('الشهر', 'control'); ?></label>
                    </div>
                    <div class="control-form-group" style="flex:1;">
                        <input type="number" name="year" value="<?php echo date('Y'); ?>" required>
                        <label><?php _e('السنة', 'control'); ?></label>
                    </div>
                </div>
            </div>

            <div class="control-grid" style="grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
                <div class="control-form-group">
                    <input type="number" name="total_sessions" id="pr-sessions" value="0">
                    <label><?php _e('عدد الجلسات المنفذة', 'control'); ?></label>
                </div>
                <div class="control-form-group">
                    <input type="number" name="base_salary" id="pr-base" value="0" step="0.01">
                    <label><?php _e('الراتب الأساسي / أتعاب الجلسات', 'control'); ?></label>
                </div>
                <div class="control-form-group">
                    <input type="number" name="incentives" id="pr-incentives" value="0" step="0.01">
                    <label><?php _e('الحوافز / المكافآت', 'control'); ?></label>
                </div>
                <div class="control-form-group">
                    <input type="number" name="deductions" id="pr-deductions" value="0" step="0.01">
                    <label><?php _e('الخصومات / الجزاءات', 'control'); ?></label>
                </div>
            </div>

            <div style="background:var(--control-bg); padding:15px; border-radius:10px; margin-bottom:20px; text-align:center;">
                <span style="font-weight:700; color:var(--control-muted);"><?php _e('صافي الراتب المستحق:', 'control'); ?></span>
                <div id="pr-net-display" style="font-size:1.8rem; font-weight:800; color:var(--control-primary);">0.00</div>
                <input type="hidden" name="net_salary" id="pr-net-value">
            </div>

            <div style="display:flex; gap:15px;">
                <button type="submit" class="control-btn" style="flex:2; background:var(--control-primary); border:none; font-weight:800;"><?php _e('اعتماد وصرف', 'control'); ?></button>
                <button type="button" onclick="jQuery('#payroll-modal').hide()" class="control-btn" style="flex:1; background:var(--control-bg); color:var(--control-text-dark) !important; border:none;"><?php _e('إلغاء', 'control'); ?></button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
jQuery(document).ready(function($) {
    $('#add-payroll-btn').on('click', function() {
        $('#payroll-form')[0].reset();
        $('#payroll-modal').css('display', 'flex');
        calculateNetSalary();
    });

    $(document).on('input', '#pr-base, #pr-incentives, #pr-deductions', calculateNetSalary);

    function calculateNetSalary() {
        const base = parseFloat($('#pr-base').val()) || 0;
        const inc = parseFloat($('#pr-incentives').val()) || 0;
        const ded = parseFloat($('#pr-deductions').val()) || 0;
        const net = base + inc - ded;
        $('#pr-net-display').text(net.toFixed(2));
        $('#pr-net-value').val(net.toFixed(2));
    }

    $('#payroll-form').on('submit', function(e) {
        e.preventDefault();
        $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_save_fin_payroll&nonce=' + control_ajax.nonce, (res) => {
            if(res.success) location.reload();
            else alert(res.data);
        });
    });

    $('.delete-payroll-btn').on('click', function() {
        if(!confirm('<?php _e('حذف سجل الراتب؟', 'control'); ?>')) return;
        $.post(control_ajax.ajax_url, { action: 'control_delete_fin_payroll', id: $(this).data('id'), nonce: control_ajax.nonce }, () => location.reload());
    });
});
</script>

<style>
.payroll-status { font-size: 0.65rem; padding: 2px 8px; border-radius: 10px; font-weight: 700; }
.payroll-status.status-paid { background: #ecfdf5; color: #059669; }
.payroll-status.status-unpaid { background: #fff7ed; color: #d97706; }
</style>
