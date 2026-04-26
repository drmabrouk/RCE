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
    <h4 style="margin:0; font-weight:800; color:var(--control-primary);"><?php _e('سجل التحصيلات النقدية', 'control'); ?></h4>
</div>

<div class="control-card" style="padding:0; overflow-x:auto; border:none; box-shadow:0 10px 30px rgba(0,0,0,0.05);">
    <table class="control-table">
        <thead>
            <tr>
                <th><?php _e('رقم العملية', 'control'); ?></th>
                <th><?php _e('التاريخ', 'control'); ?></th>
                <th><?php _e('العميل (الطفل)', 'control'); ?></th>
                <th><?php _e('رقم الفاتورة', 'control'); ?></th>
                <th><?php _e('طريقة الدفع', 'control'); ?></th>
                <th><?php _e('المبلغ المحصل', 'control'); ?></th>
                <th style="width:100px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php if($payments): foreach($payments as $pay): ?>
                <tr data-search="<?php echo esc_attr($pay->patient_name . ' ' . $pay->invoice_number . ' ' . $pay->payment_method . ' ' . $pay->payment_date); ?>">
                    <td><strong>#<?php echo $pay->id; ?></strong></td>
                    <td><?php echo date('Y/m/d', strtotime($pay->payment_date)); ?></td>
                    <td><?php echo esc_html($pay->patient_name); ?></td>
                    <td class="font-bold"><?php echo $pay->invoice_number; ?></td>
                    <td>
                        <span class="badge-pastel" style="background:#f1f5f9; color:#475569; font-size:0.7rem; font-weight:800; padding:4px 10px; border-radius:8px;">
                            <?php echo esc_html($pay->payment_method); ?>
                        </span>
                    </td>
                    <td class="font-bold text-success" style="font-size:1rem;"><?php echo number_format($pay->amount, 2); ?></td>
                    <td style="text-align:left;">
                        <div class="action-btn-group">
                            <button class="action-icon-btn delete-payment-btn" data-id="<?php echo $pay->id; ?>" title="حذف العملية"><span class="dashicons dashicons-trash"></span></button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="7" style="text-align:center; padding:40px; color:var(--control-muted);"><?php _e('لا توجد سجلات تحصيل حالياً.', 'control'); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
jQuery(document).ready(function($) {
    $('.delete-payment-btn').on('click', function() {
        const id = $(this).data('id');
        if(!confirm('<?php _e("هل أنت متأكد من حذف هذا السجل المالي؟ سيتم تحديث رصيد الفاتورة المرتبطة.", "control"); ?>')) return;

        $.post(control_ajax.ajax_url, {
            action: 'control_delete_fin_payment',
            id: id,
            nonce: control_ajax.nonce
        }, function(res) {
            if(res.success) {
                location.reload();
            } else {
                alert(res.data.message);
            }
        });
    });
});
</script>
