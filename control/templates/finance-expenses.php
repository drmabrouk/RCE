<?php
global $wpdb;
$can_manage = Control_Auth::has_permission('finance_manage');
$expenses = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}control_fin_expenses ORDER BY expense_date DESC");
$categories = array(
    'rent' => __('الإيجار', 'control'),
    'equipment' => __('الأجهزة والمعدات', 'control'),
    'utilities' => __('المرافق (كهرباء/مياه)', 'control'),
    'misc' => __('مصاريف نثرية', 'control'),
);
?>

<div style="display:flex; justify-content: flex-end; margin-bottom: 20px;">
    <?php if($can_manage): ?>
        <button id="add-expense-btn" class="control-btn" style="background:var(--control-primary); border:none;">
            <span class="dashicons dashicons-plus-alt" style="margin-left:5px;"></span><?php _e('إضافة مصروف جديد', 'control'); ?>
        </button>
    <?php endif; ?>
</div>

<div class="control-card" style="padding:0; overflow:hidden;">
    <table class="control-table">
        <thead>
            <tr>
                <th><?php _e('التاريخ', 'control'); ?></th>
                <th><?php _e('الفئة', 'control'); ?></th>
                <th><?php _e('الوصف', 'control'); ?></th>
                <th><?php _e('المبلغ', 'control'); ?></th>
                <th><?php _e('المرفق', 'control'); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if($expenses): foreach($expenses as $ex): ?>
                <tr>
                    <td><?php echo $ex->expense_date; ?></td>
                    <td><span style="background:var(--control-bg); padding:2px 8px; border-radius:6px; font-size:0.75rem; font-weight:700;"><?php echo $categories[$ex->category] ?? $ex->category; ?></span></td>
                    <td><?php echo esc_html($ex->description); ?></td>
                    <td style="font-weight:700; color:#ef4444;"><?php echo number_format($ex->amount, 2); ?></td>
                    <td>
                        <?php if($ex->attachment_url): ?>
                            <a href="<?php echo esc_url($ex->attachment_url); ?>" target="_blank" style="color:var(--control-primary);"><span class="dashicons dashicons-paperclip"></span></a>
                        <?php else: ?>
                            ---
                        <?php endif; ?>
                    </td>
                    <td style="text-align:left;">
                        <?php if($can_manage): ?>
                            <button class="delete-expense-btn" data-id="<?php echo $ex->id; ?>" style="background:none; border:none; color:#ef4444; cursor:pointer;"><span class="dashicons dashicons-trash"></span></button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="6" style="text-align:center; padding:30px; color:var(--control-muted);"><?php _e('لا توجد مصروفات مسجلة.', 'control'); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Expense Modal -->
<div id="expense-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10006; align-items:center; justify-content:center; backdrop-filter: blur(4px);">
    <div class="control-card" style="width:100%; max-width:500px; padding:30px;">
        <h3><?php _e('إضافة مصروف جديد', 'control'); ?></h3>
        <form id="expense-form">
            <div class="control-form-group">
                <select name="category" required>
                    <?php foreach($categories as $val => $label): ?>
                        <option value="<?php echo $val; ?>"><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
                <label><?php _e('الفئة', 'control'); ?></label>
            </div>
            <div class="control-form-group">
                <input type="text" name="description" required placeholder=" ">
                <label><?php _e('الوصف / البيان', 'control'); ?></label>
            </div>
            <div class="control-grid" style="grid-template-columns: 1fr 1fr; gap:20px;">
                <div class="control-form-group">
                    <input type="number" name="amount" step="0.01" required>
                    <label><?php _e('المبلغ', 'control'); ?></label>
                </div>
                <div class="control-form-group">
                    <input type="date" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required>
                    <label><?php _e('التاريخ', 'control'); ?></label>
                </div>
            </div>
            <div class="control-form-group">
                <div style="display:flex; align-items:center; gap:10px;">
                    <input type="checkbox" name="is_recurring" value="1" style="width:18px; height:18px;">
                    <span style="font-size:0.9rem; font-weight:700;"><?php _e('مصروف متكرر دورياً', 'control'); ?></span>
                </div>
            </div>
            <div class="control-form-group">
                <input type="hidden" name="attachment_url" id="exp-attach-url">
                <button type="button" id="upload-exp-attach" class="control-btn" style="width:100%; background:#f8fafc; color:var(--control-text-dark) !important; border:1px solid var(--control-border); border-style:dashed;">
                    <span class="dashicons dashicons-upload"></span> <?php _e('إرفاق مستند / فاتورة', 'control'); ?>
                </button>
            </div>
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="control-btn" style="flex:1; background:var(--control-primary); border:none;"><?php _e('حفظ المصروف', 'control'); ?></button>
                <button type="button" onclick="jQuery('#expense-modal').hide()" class="control-btn" style="flex:1; background:var(--control-bg); color:var(--control-text-dark) !important; border:none;"><?php _e('إلغاء', 'control'); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#add-expense-btn').on('click', function() {
        $('#expense-form')[0].reset();
        $('#expense-modal').css('display', 'flex');
    });

    $('#upload-exp-attach').on('click', function(e) {
        e.preventDefault();
        const frame = wp.media({ title: 'ارفاق مستند', multiple: false }).open();
        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();
            $('#exp-attach-url').val(attachment.url);
            $('#upload-exp-attach').text('تم الإرفاق: ' + attachment.filename).css('background', '#ecfdf5');
        });
    });

    $('#expense-form').on('submit', function(e) {
        e.preventDefault();
        $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_save_fin_expense&nonce=' + control_ajax.nonce, (res) => {
            if(res.success) location.reload();
            else alert(res.data);
        });
    });

    $('.delete-expense-btn').on('click', function() {
        if(!confirm('<?php _e('حذف سجل المصروف؟', 'control'); ?>')) return;
        $.post(control_ajax.ajax_url, { action: 'control_delete_fin_expense', id: $(this).data('id'), nonce: control_ajax.nonce }, () => location.reload());
    });
});
</script>
