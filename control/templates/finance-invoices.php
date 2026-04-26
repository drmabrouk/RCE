<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$can_manage = Control_Auth::has_permission('finance_invoicing');
$invoices = $wpdb->get_results("SELECT i.*, p.full_name FROM {$wpdb->prefix}control_fin_invoices i JOIN {$wpdb->prefix}control_patients p ON i.patient_id = p.id ORDER BY i.created_at DESC");
$patients = $wpdb->get_results("SELECT id, full_name FROM {$wpdb->prefix}control_patients ORDER BY full_name ASC");
?>

<div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h4 style="margin:0; font-weight:800; color:var(--control-primary);"><?php _e('قائمة الفواتير الصادرة', 'control'); ?></h4>
    <?php if($can_manage): ?>
        <button id="add-invoice-btn" class="control-btn" style="background:var(--control-primary); border:none; padding:10px 25px; border-radius:12px; font-weight:800;">
            <span class="dashicons dashicons-plus-alt" style="margin-left:5px;"></span><?php _e('إنشاء فاتورة', 'control'); ?>
        </button>
    <?php endif; ?>
</div>

<div class="control-card" style="padding:0; overflow:hidden; border:none; box-shadow:0 10px 30px rgba(0,0,0,0.04); border-radius:20px;">
    <table class="control-table">
        <thead>
            <tr>
                <th><?php _e('رقم الفاتورة', 'control'); ?></th>
                <th><?php _e('العميل (الطفل)', 'control'); ?></th>
                <th><?php _e('التاريخ', 'control'); ?></th>
                <th><?php _e('المبلغ الإجمالي', 'control'); ?></th>
                <th><?php _e('المبلغ المحصل', 'control'); ?></th>
                <th><?php _e('الحالة', 'control'); ?></th>
                <th style="width:150px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php if($invoices): foreach($invoices as $inv): ?>
                <tr data-search="<?php echo esc_attr($inv->full_name . ' ' . $inv->invoice_number . ' ' . $inv->status . ' ' . $inv->invoice_date); ?>">
                    <td style="font-family:monospace; font-weight:800;">#<?php echo $inv->invoice_number; ?></td>
                    <td style="font-weight:700;"><?php echo esc_html($inv->full_name); ?></td>
                    <td><?php echo date('Y/m/d', strtotime($inv->invoice_date)); ?></td>
                    <td style="font-weight:800; color:var(--control-primary);"><?php echo number_format($inv->total_amount, 2); ?></td>
                    <td style="font-weight:800; color:#10b981;"><?php echo number_format($inv->paid_amount, 2); ?></td>
                    <td><span class="patient-status-badge status-<?php echo $inv->status; ?>" style="font-size:0.65rem; padding:4px 12px;"><?php echo $inv->status; ?></span></td>
                    <td style="text-align:left;">
                        <div class="action-btn-group">
                            <button class="action-icon-btn record-payment-btn" data-id="<?php echo $inv->id; ?>" data-num="<?php echo $inv->invoice_number; ?>" data-remain="<?php echo $inv->total_amount - $inv->paid_amount; ?>" title="تحصيل دفعة"><span class="dashicons dashicons-money-alt"></span></button>
                            <button class="action-icon-btn view-invoice-btn" data-id="<?php echo $inv->id; ?>" title="عرض"><span class="dashicons dashicons-visibility"></span></button>
                            <?php if(Control_Auth::has_permission('finance_manage')): ?>
                                <button class="action-icon-btn delete-invoice-btn" data-id="<?php echo $inv->id; ?>" title="حذف"><span class="dashicons dashicons-trash"></span></button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="7" style="text-align:center; padding:40px; color:var(--control-muted);"><?php _e('لا توجد فواتير مسجلة.', 'control'); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
