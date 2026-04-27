<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$payments = $wpdb->get_results("
    SELECT pay.*, inv.invoice_number, p.full_name as patient_name
    FROM {$wpdb->prefix}control_fin_payments pay
    JOIN {$wpdb->prefix}control_fin_invoices inv ON pay.invoice_id = inv.id
    JOIN {$wpdb->prefix}control_patients p ON inv.patient_id = p.id
    ORDER BY pay.payment_date DESC
");
?>

<div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h4 style="margin:0; font-weight:900; color:var(--control-primary);"><?php _e('سجل التحصيلات النقدية', 'control'); ?></h4>
</div>

<div class="control-card" style="padding:0; overflow:hidden; border-radius:16px; border:1px solid #eef2f6;">
    <table class="control-table-refined">
        <thead>
            <tr>
                <th><?php _e('رقم العملية', 'control'); ?></th>
                <th><?php _e('التاريخ', 'control'); ?></th>
                <th><?php _e('الطفل', 'control'); ?></th>
                <th><?php _e('الفاتورة', 'control'); ?></th>
                <th><?php _e('الطريقة', 'control'); ?></th>
                <th><?php _e('المبلغ', 'control'); ?></th>
                <th style="width:80px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php if($payments): foreach($payments as $pay): ?>
                <tr data-search="<?php echo esc_attr($pay->patient_name . ' ' . $pay->invoice_number); ?>">
                    <td style="font-weight:800; font-size:0.75rem;">#<?php echo $pay->id; ?></td>
                    <td style="font-size:0.75rem; color:var(--control-muted);"><?php echo date('Y/m/d', strtotime($pay->payment_date)); ?></td>
                    <td style="font-weight:700; font-size:0.85rem;"><?php echo esc_html($pay->patient_name); ?></td>
                    <td style="font-family:monospace;"><?php echo $pay->invoice_number; ?></td>
                    <td><span class="badge-status-refined" style="background:#f1f5f9; color:#475569;"><?php echo esc_html($pay->payment_method); ?></span></td>
                    <td class="font-bold text-success" style="font-size:0.95rem;"><?php echo number_format($pay->amount, 2); ?></td>
                    <td>
                        <button class="action-icon-btn delete-payment-btn" data-id="<?php echo $pay->id; ?>" style="color:#ef4444;"><span class="dashicons dashicons-trash"></span></button>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="7" style="text-align:center; padding:30px; color:var(--control-muted);"><?php _e('لا توجد تحصيلات.', 'control'); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
