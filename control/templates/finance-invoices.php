<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$can_manage = Control_Auth::has_permission('finance_invoicing');

// Data Binding
$invoices = $wpdb->get_results("SELECT i.*, p.full_name FROM {$wpdb->prefix}control_fin_invoices i JOIN {$wpdb->prefix}control_patients p ON i.patient_id = p.id ORDER BY i.created_at DESC");
?>

<div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h4 style="margin:0; font-weight:900; color:var(--control-primary);"><?php _e('سجل الفواتير الصادرة', 'control'); ?></h4>
    <?php if($can_manage): ?>
        <button id="add-invoice-btn" class="control-btn" style="background:var(--control-primary); border:none; padding:8px 20px; border-radius:10px; font-weight:800; font-size:0.75rem;">
            <span class="dashicons dashicons-plus-alt" style="margin-left:5px;"></span><?php _e('فاتورة جديدة', 'control'); ?>
        </button>
    <?php endif; ?>
</div>

<div class="control-card" style="padding:0; overflow:hidden; border:1px solid #eef2f6; border-radius:16px;">
    <table class="control-table-refined">
        <thead>
            <tr>
                <th><?php _e('رقم الفاتورة', 'control'); ?></th>
                <th><?php _e('الطفل', 'control'); ?></th>
                <th><?php _e('التاريخ', 'control'); ?></th>
                <th><?php _e('المبلغ', 'control'); ?></th>
                <th><?php _e('المحصل', 'control'); ?></th>
                <th><?php _e('الحالة', 'control'); ?></th>
                <th style="width:120px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php if($invoices): foreach($invoices as $inv): ?>
                <tr data-search="<?php echo esc_attr($inv->full_name . ' ' . $inv->invoice_number); ?>">
                    <td style="font-family:monospace; font-weight:800; font-size:0.75rem;">#<?php echo $inv->invoice_number; ?></td>
                    <td style="font-weight:700; font-size:0.85rem;"><?php echo esc_html($inv->full_name); ?></td>
                    <td style="font-size:0.75rem; color:var(--control-muted);"><?php echo date('Y/m/d', strtotime($inv->invoice_date)); ?></td>
                    <td class="font-bold"><?php echo number_format($inv->total_amount, 2); ?></td>
                    <td class="text-success font-bold"><?php echo number_format($inv->paid_amount, 2); ?></td>
                    <td><span class="badge-status-refined status-<?php echo $inv->status; ?>"><?php echo $inv->status; ?></span></td>
                    <td>
                        <div class="action-btn-group">
                            <button class="action-icon-btn record-payment-btn" data-id="<?php echo $inv->id; ?>" data-num="<?php echo $inv->invoice_number; ?>" data-remain="<?php echo $inv->total_amount - $inv->paid_amount; ?>" title="تحصيل"><span class="dashicons dashicons-money-alt"></span></button>
                            <?php if(Control_Auth::has_permission('finance_manage')): ?>
                                <button class="action-icon-btn delete-invoice-btn" data-id="<?php echo $inv->id; ?>" style="color:#ef4444;"><span class="dashicons dashicons-trash"></span></button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="7" style="text-align:center; padding:30px; color:var(--control-muted);"><?php _e('لا توجد فواتير.', 'control'); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
