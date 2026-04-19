<?php
global $wpdb;
$can_manage = Control_Auth::has_permission('finance_invoicing');
$invoices = $wpdb->get_results("SELECT i.*, p.full_name FROM {$wpdb->prefix}control_fin_invoices i JOIN {$wpdb->prefix}control_patients p ON i.patient_id = p.id ORDER BY i.created_at DESC");
$patients = $wpdb->get_results("SELECT id, full_name FROM {$wpdb->prefix}control_patients ORDER BY full_name ASC");
?>

<div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h4 style="margin:0; font-weight:800; color:var(--control-primary);"><?php _e('قائمة الفواتير والتحصيلات', 'control'); ?></h4>
    <?php if($can_manage): ?>
        <button id="add-invoice-btn" class="control-btn" style="background:var(--control-primary); border:none; padding:10px 25px;">
            <span class="dashicons dashicons-plus-alt" style="margin-left:5px;"></span><?php _e('إنشاء فاتورة', 'control'); ?>
        </button>
    <?php endif; ?>
</div>

<div class="control-card" style="padding:0; overflow-x:auto; border:none; box-shadow:0 10px 30px rgba(0,0,0,0.05);">
    <table class="control-table enterprise-table">
        <thead>
            <tr>
                <th><?php _e('رقم الفاتورة', 'control'); ?></th>
                <th><?php _e('العميل (الطفل)', 'control'); ?></th>
                <th><?php _e('التاريخ', 'control'); ?></th>
                <th><?php _e('المبلغ', 'control'); ?></th>
                <th><?php _e('المدفوع', 'control'); ?></th>
                <th><?php _e('الحالة', 'control'); ?></th>
                <th style="width:180px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php if($invoices): foreach($invoices as $inv): ?>
                <tr>
                    <td class="font-bold">#<?php echo $inv->invoice_number; ?></td>
                    <td><?php echo esc_html($inv->full_name); ?></td>
                    <td><?php echo date('Y/m/d', strtotime($inv->invoice_date)); ?></td>
                    <td class="font-bold"><?php echo number_format($inv->total_amount, 2); ?></td>
                    <td class="text-success"><?php echo number_format($inv->paid_amount, 2); ?></td>
                    <td><span class="fin-badge badge-<?php echo $inv->status; ?>"><?php echo $inv->status; ?></span></td>
                    <td style="text-align:left;">
                        <div style="display:flex; gap:5px; justify-content:flex-end;">
                            <button class="action-icon collect-btn record-payment-btn" data-id="<?php echo $inv->id; ?>" data-num="<?php echo $inv->invoice_number; ?>" data-remain="<?php echo $inv->total_amount - $inv->paid_amount; ?>" title="تحصيل"><span class="dashicons dashicons-money-alt"></span></button>
                            <button class="action-icon view-btn view-invoice-btn" data-id="<?php echo $inv->id; ?>" title="عرض"><span class="dashicons dashicons-visibility"></span></button>
                            <?php if(Control_Auth::has_permission('finance_manage')): ?>
                                <button class="action-icon delete-btn delete-invoice-btn" data-id="<?php echo $inv->id; ?>" title="حذف"><span class="dashicons dashicons-trash"></span></button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="7" class="text-center py-10"><?php _e('لا توجد فواتير مسجلة.', 'control'); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.enterprise-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.enterprise-table th { background: #f8fafc; color: #64748b; font-weight: 800; text-transform: uppercase; font-size: 0.7rem; padding: 15px 20px; border-bottom: 1px solid #edf2f7; }
.enterprise-table td { padding: 15px 20px; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; }
.fin-badge { padding: 4px 12px; border-radius: 50px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; }
.badge-paid { background: #ecfdf5; color: #059669; }
.badge-pending { background: #fff7ed; color: #d97706; }
.badge-partial { background: #eff6ff; color: #2563eb; }
.badge-overdue { background: #fef2f2; color: #ef4444; }
.action-icon { width: 32px; height: 32px; border-radius: 8px; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; }
.collect-btn { background: #10b981; color: #fff; }
.view-btn { background: #f1f5f9; color: #475569; }
.delete-btn { background: #fee2e2; color: #ef4444; }
.font-bold { font-weight: 700; }
.text-success { color: #10b981; font-weight: 700; }
</style>
