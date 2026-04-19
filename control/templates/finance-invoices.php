<?php
global $wpdb;
$can_manage = Control_Auth::has_permission('finance_invoicing');
$invoices = $wpdb->get_results("SELECT i.*, p.full_name FROM {$wpdb->prefix}control_fin_invoices i JOIN {$wpdb->prefix}control_patients p ON i.patient_id = p.id ORDER BY i.created_at DESC");
$patients = $wpdb->get_results("SELECT id, full_name FROM {$wpdb->prefix}control_patients ORDER BY full_name ASC");
?>

<div style="display:flex; justify-content: flex-end; margin-bottom: 20px;">
    <?php if($can_manage): ?>
        <button id="add-invoice-btn" class="control-btn" style="background:var(--control-primary); border:none;">
            <span class="dashicons dashicons-plus-alt" style="margin-left:5px;"></span><?php _e('إنشاء فاتورة جديدة', 'control'); ?>
        </button>
    <?php endif; ?>
</div>

<div class="control-card" style="padding:0; overflow:hidden;">
    <table class="control-table">
        <thead>
            <tr>
                <th><?php _e('رقم الفاتورة', 'control'); ?></th>
                <th><?php _e('اسم الطفل', 'control'); ?></th>
                <th><?php _e('التاريخ', 'control'); ?></th>
                <th><?php _e('المبلغ الكلي', 'control'); ?></th>
                <th><?php _e('المدفوع', 'control'); ?></th>
                <th><?php _e('الحالة', 'control'); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if($invoices): foreach($invoices as $inv): ?>
                <tr>
                    <td><strong><?php echo $inv->invoice_number; ?></strong></td>
                    <td><?php echo esc_html($inv->full_name); ?></td>
                    <td><?php echo $inv->invoice_date; ?></td>
                    <td><?php echo number_format($inv->total_amount, 2); ?></td>
                    <td><?php echo number_format($inv->paid_amount, 2); ?></td>
                    <td><span class="fin-status-badge status-<?php echo $inv->status; ?>"><?php echo $inv->status; ?></span></td>
                    <td style="text-align:left; display:flex; gap:8px; justify-content:flex-end;">
                        <button class="control-btn record-payment-btn" data-id="<?php echo $inv->id; ?>" data-num="<?php echo $inv->invoice_number; ?>" data-remain="<?php echo $inv->total_amount - $inv->paid_amount; ?>" style="padding:4px 10px; font-size:0.75rem; background:#10b981; border:none;"><?php _e('تحصيل', 'control'); ?></button>
                        <button class="control-btn view-invoice-btn" data-id="<?php echo $inv->id; ?>" style="padding:4px 10px; font-size:0.75rem; background:#fff; color:var(--control-text-dark) !important; border:1px solid var(--control-border);"><?php _e('تفاصيل', 'control'); ?></button>
                        <?php if(Control_Auth::has_permission('finance_manage')): ?>
                            <button class="delete-invoice-btn" data-id="<?php echo $inv->id; ?>" style="background:none; border:none; color:#ef4444; cursor:pointer;"><span class="dashicons dashicons-trash"></span></button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="7" style="text-align:center; padding:30px; color:var(--control-muted);"><?php _e('لا توجد فواتير حالياً.', 'control'); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Invoice Modal -->
<div id="invoice-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10006; align-items:center; justify-content:center; backdrop-filter: blur(4px);">
    <div class="control-card" style="width:100%; max-width:850px; padding:0; border-radius:20px; overflow:hidden;">
        <div style="background:var(--control-primary); color:#fff; padding:20px 30px; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="color:#fff; margin:0; font-size:1.1rem;"><?php _e('تفاصيل الفاتورة', 'control'); ?></h3>
            <button onclick="jQuery('#invoice-modal').hide()" style="background:none; border:none; color:#fff; cursor:pointer;"><span class="dashicons dashicons-no-alt"></span></button>
        </div>
        <form id="invoice-form" style="padding:30px;">
            <div class="control-grid" style="grid-template-columns: 1fr 1fr 1fr; gap:20px; margin-bottom:25px;">
                <div class="control-form-group">
                    <select name="patient_id" id="inv-patient" required>
                        <option value=""><?php _e('اختر الطفل...', 'control'); ?></option>
                        <?php foreach($patients as $pt): ?>
                            <option value="<?php echo $pt->id; ?>"><?php echo esc_html($pt->full_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label><?php _e('الطفل', 'control'); ?></label>
                </div>
                <div class="control-form-group">
                    <input type="text" name="invoice_number" id="inv-number" value="INV-<?php echo time(); ?>" required>
                    <label><?php _e('رقم الفاتورة', 'control'); ?></label>
                </div>
                <div class="control-form-group">
                    <input type="date" name="invoice_date" id="inv-date" value="<?php echo date('Y-m-d'); ?>" required>
                    <label><?php _e('تاريخ الفاتورة', 'control'); ?></label>
                </div>
            </div>

            <div style="background:#f8fafc; border-radius:12px; padding:20px; margin-bottom:25px;">
                <h4 style="margin-top:0;"><?php _e('بنود الخدمة', 'control'); ?></h4>
                <table class="control-table" id="invoice-items-table">
                    <thead>
                        <tr style="background:#f1f5f9;">
                            <th><?php _e('الوصف / الخدمة', 'control'); ?></th>
                            <th style="width:80px;"><?php _e('العدد', 'control'); ?></th>
                            <th style="width:120px;"><?php _e('سعر الوحدة', 'control'); ?></th>
                            <th style="width:120px;"><?php _e('الإجمالي', 'control'); ?></th>
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="text" class="item-desc" required></td>
                            <td><input type="number" class="item-qty" value="1" min="1"></td>
                            <td><input type="number" class="item-price" step="0.01" required></td>
                            <td><span class="item-total">0.00</span></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" id="add-item-row" class="control-btn" style="margin-top:10px; background:#fff; color:var(--control-text-dark) !important; border:1px solid var(--control-border); font-size:0.75rem;"><span class="dashicons dashicons-plus" style="font-size:14px;"></span> <?php _e('إضافة بند آخر', 'control'); ?></button>
            </div>

            <div style="display:flex; justify-content:flex-end;">
                <div style="width:300px; display:flex; flex-direction:column; gap:10px; font-size:1.1rem;">
                    <div style="display:flex; justify-content:space-between;">
                        <span><?php _e('المجموع الفرعي:', 'control'); ?></span>
                        <strong id="inv-subtotal">0.00</strong>
                    </div>
                    <div style="display:flex; justify-content:space-between; color:#ef4444;">
                        <span><?php _e('الخصم:', 'control'); ?></span>
                        <input type="number" name="discount" id="inv-discount" value="0" step="0.01" style="width:100px; text-align:left; border:none; background:none; font-weight:800; color:#ef4444;">
                    </div>
                    <div style="display:flex; justify-content:space-between; border-top:2px solid var(--control-border); padding-top:10px; font-size:1.4rem; color:var(--control-primary);">
                        <span><?php _e('الإجمالي النهائي:', 'control'); ?></span>
                        <strong id="inv-total">0.00</strong>
                    </div>
                </div>
            </div>

            <div style="margin-top:30px; display:flex; gap:15px;">
                <button type="submit" class="control-btn" style="flex:2; background:var(--control-primary); border:none; font-weight:800;"><?php _e('حفظ الفاتورة', 'control'); ?></button>
                <button type="button" onclick="jQuery('#invoice-modal').hide()" class="control-btn" style="flex:1; background:var(--control-bg); color:var(--control-text-dark) !important; border:none;"><?php _e('إلغاء', 'control'); ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Payment Modal -->
<div id="payment-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10007; align-items:center; justify-content:center; backdrop-filter: blur(4px);">
    <div class="control-card" style="width:100%; max-width:400px; padding:30px;">
        <h3 id="pay-modal-title"></h3>
        <form id="payment-form">
            <input type="hidden" name="invoice_id" id="pay-inv-id">
            <div class="control-form-group">
                <input type="number" name="amount" id="pay-amount" step="0.01" required>
                <label><?php _e('مبلغ التحصيل', 'control'); ?></label>
            </div>
            <div class="control-form-group">
                <select name="payment_method" required>
                    <option value="cash"><?php _e('نقداً', 'control'); ?></option>
                    <option value="card"><?php _e('بطاقة بنكية', 'control'); ?></option>
                    <option value="transfer"><?php _e('تحويل بنكي', 'control'); ?></option>
                </select>
                <label><?php _e('طريقة الدفع', 'control'); ?></label>
            </div>
            <div class="control-form-group">
                <input type="text" name="transaction_id">
                <label><?php _e('رقم العملية / المرجع', 'control'); ?></label>
            </div>
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="control-btn" style="flex:1; background:#10b981; border:none;"><?php _e('تأكيد الدفع', 'control'); ?></button>
                <button type="button" onclick="jQuery('#payment-modal').hide()" class="control-btn" style="flex:1; background:var(--control-bg); color:var(--control-text-dark) !important; border:none;"><?php _e('إلغاء', 'control'); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#add-invoice-btn').on('click', function() {
        $('#invoice-form')[0].reset();
        $('#invoice-items-table tbody').html('<tr><td><input type="text" class="item-desc" required></td><td><input type="number" class="item-qty" value="1" min="1"></td><td><input type="number" class="item-price" step="0.01" required></td><td><span class="item-total">0.00</span></td><td></td></tr>');
        $('#invoice-modal').css('display', 'flex');
        calculateInvoiceTotals();
    });

    $('#add-item-row').on('click', function() {
        $('#invoice-items-table tbody').append('<tr><td><input type="text" class="item-desc" required></td><td><input type="number" class="item-qty" value="1" min="1"></td><td><input type="number" class="item-price" step="0.01" required></td><td><span class="item-total">0.00</span></td><td><button type="button" class="remove-row" style="background:none; border:none; color:#ef4444; cursor:pointer;"><span class="dashicons dashicons-no-alt"></span></button></td></tr>');
    });

    $(document).on('click', '.remove-row', function() { $(this).closest('tr').remove(); calculateInvoiceTotals(); });

    $(document).on('input', '.item-qty, .item-price, #inv-discount', calculateInvoiceTotals);

    function calculateInvoiceTotals() {
        let subtotal = 0;
        $('#invoice-items-table tbody tr').each(function() {
            const qty = parseFloat($(this).find('.item-qty').val()) || 0;
            const price = parseFloat($(this).find('.item-price').val()) || 0;
            const total = qty * price;
            $(this).find('.item-total').text(total.toFixed(2));
            subtotal += total;
        });
        const discount = parseFloat($('#inv-discount').val()) || 0;
        const total = subtotal - discount;
        $('#inv-subtotal').text(subtotal.toFixed(2));
        $('#inv-total').text(total.toFixed(2));
    }

    $('#invoice-form').on('submit', function(e) {
        e.preventDefault();
        const items = [];
        $('#invoice-items-table tbody tr').each(function() {
            items.push({
                description: $(this).find('.item-desc').val(),
                quantity: $(this).find('.item-qty').val(),
                unit_price: $(this).find('.item-price').val(),
                total_price: $(this).find('.item-total').text()
            });
        });

        const formData = {
            action: 'control_save_fin_invoice',
            nonce: control_ajax.nonce,
            patient_id: $('#inv-patient').val(),
            invoice_number: $('#inv-number').val(),
            invoice_date: $('#inv-date').val(),
            subtotal: $('#inv-subtotal').text(),
            discount: $('#inv-discount').val(),
            total_amount: $('#inv-total').text(),
            items: JSON.stringify(items)
        };

        $.post(control_ajax.ajax_url, formData, (res) => {
            if(res.success) location.reload();
            else alert(res.data);
        });
    });

    $('.record-payment-btn').on('click', function() {
        const data = $(this).data();
        $('#pay-modal-title').text('<?php _e('تحصيل مبلغ للفاتورة: ', 'control'); ?>' + data.num);
        $('#pay-inv-id').val(data.id);
        $('#pay-amount').val(data.remain);
        $('#payment-modal').css('display', 'flex');
    });

    $('#payment-form').on('submit', function(e) {
        e.preventDefault();
        $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_save_fin_payment&nonce=' + control_ajax.nonce, (res) => {
            if(res.success) location.reload();
            else alert(res.data);
        });
    });

    $('.delete-invoice-btn').on('click', function() {
        if(!confirm('<?php _e('حذف الفاتورة وكافة مدفوعاتها؟', 'control'); ?>')) return;
        $.post(control_ajax.ajax_url, { action: 'control_delete_fin_invoice', id: $(this).data('id'), nonce: control_ajax.nonce }, () => location.reload());
    });
});
</script>

<style>
.fin-status-badge { font-size: 0.65rem; padding: 2px 8px; border-radius: 10px; font-weight: 700; text-transform: uppercase; }
.fin-status-badge.status-paid { background: #ecfdf5; color: #059669; }
.fin-status-badge.status-pending { background: #fff7ed; color: #d97706; }
.fin-status-badge.status-partial { background: #eff6ff; color: #2563eb; }
.fin-status-badge.status-overdue { background: #fef2f2; color: #ef4444; }
#invoice-items-table input { border: 1px solid transparent; width: 100%; padding: 5px; }
#invoice-items-table input:focus { border-color: var(--control-border); background: #fff; }
</style>
