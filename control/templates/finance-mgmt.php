<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$can_manage = Control_Auth::has_permission('finance_manage');
$can_invoice = Control_Auth::has_permission('finance_invoicing');
$can_payroll = Control_Auth::has_permission('finance_payroll_view');

$tabs = array();

if ( $can_manage || $can_payroll ) {
    $tabs['dashboard'] = array(
        'label' => __('لوحة المعلومات', 'control'),
        'icon'  => 'chart-bar',
        'template' => 'finance-dashboard.php'
    );
}

if ( $can_invoice || $can_manage ) {
    $tabs['invoices'] = array(
        'label' => __('الفواتير', 'control'),
        'icon'  => 'cart',
        'template' => 'finance-invoices.php'
    );
    $tabs['collections'] = array(
        'label' => __('التحصيلات', 'control'),
        'icon'  => 'money-alt',
        'template' => 'finance-collections.php'
    );
}

if ( $can_payroll || $can_manage ) {
    $tabs['payroll'] = array(
        'label' => __('رواتب الأخصائيين', 'control'),
        'icon'  => 'groups',
        'template' => 'finance-payroll.php'
    );
}

if ( $can_manage ) {
    $tabs['expenses'] = array(
        'label' => __('إدارة المصروفات', 'control'),
        'icon'  => 'trending-down',
        'template' => 'finance-expenses.php'
    );
}

$active_tab = array_key_first($tabs);
?>

<div class="view-section-container">
    <div style="display:flex; gap:30px; align-items:flex-start;" class="finance-management-layout">

        <!-- Right Column: Navigation & Search Sidebar -->
        <div class="p-internal-sidebar" style="width:300px; flex-shrink:0; position:sticky; top:100px;">

            <!-- Context-Based Search Box -->
            <div class="control-card" style="padding:15px; border-radius:20px; background:#fff; border:2px solid var(--control-primary-soft); margin-bottom:20px; box-shadow:0 12px 30px rgba(0,0,0,0.04);">
                <div style="position:relative;">
                    <span class="dashicons dashicons-search" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); color:var(--control-primary); font-size: 18px;"></span>
                    <input type="text" id="finance-search-input" placeholder="<?php _e('بحث في القسم الحالي...', 'control'); ?>" style="padding:12px 40px 12px 12px; border-radius:12px; border:1.5px solid #f1f5f9; width:100%; font-size: 0.9rem; background:#f8fafc; border-color:#e2e8f0; font-weight:700;">
                </div>
            </div>

            <!-- Financial Sidebar Tabs -->
            <div class="control-card" style="padding:10px; border-radius:20px; background:#fff; border:1px solid #f1f5f9; box-shadow:0 10px 30px rgba(0,0,0,0.02); overflow:hidden;">
                <div style="padding:10px 15px 5px; color:var(--control-muted); font-size:0.75rem; font-weight:800; text-transform:uppercase; letter-spacing:0.5px;">
                    <?php _e('أقسام المالية', 'control'); ?>
                </div>
                <?php foreach($tabs as $id => $tab): ?>
                    <div class="p-nav-item <?php echo ($active_tab === $id) ? 'active' : ''; ?>" data-tab="tab-<?php echo $id; ?>" style="display:flex; align-items:center; gap:12px; padding:12px 15px; border-radius:15px; cursor:pointer; transition:0.3s; margin-bottom:2px;">
                        <div class="nav-icon-box" style="width:35px; height:35px; border-radius:10px; background:#f8fafc; display:flex; align-items:center; justify-content:center; color:var(--control-primary);">
                            <span class="dashicons dashicons-<?php echo $tab['icon']; ?>"></span>
                        </div>
                        <span style="font-weight:800; flex:1; font-size:0.9rem;"><?php echo $tab['label']; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Quick Summary Stats -->
            <div class="p-sidebar-stats" style="margin-top:20px; padding:25px 20px; background:var(--control-primary); border-radius:24px; text-align:center; color:#fff; box-shadow:0 15px 35px var(--control-primary-soft);">
                <p style="margin:0; font-size:0.85rem; color:rgba(255,255,255,0.7); font-weight:700;"><?php _e('الرصيد المتاح', 'control'); ?></p>
                <?php
                $revenue = $wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}control_fin_payments") ?: 0;
                $expenses = $wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}control_fin_expenses") ?: 0;
                $payroll = $wpdb->get_var("SELECT SUM(net_salary) FROM {$wpdb->prefix}control_fin_payroll WHERE payment_status = 'paid'") ?: 0;
                $net_balance = $revenue - ($expenses + $payroll);
                ?>
                <div style="font-size:2.2rem; font-weight:900; color:#fff; margin:5px 0;"><?php echo number_format($net_balance, 2); ?></div>
                <div style="font-size:0.7rem; color:rgba(255,255,255,0.6);"><?php _e('درهم إماراتي', 'control'); ?></div>
            </div>
        </div>

        <!-- Left Column: Content Area -->
        <div style="flex:1; min-width:0;">
            <div id="finance-tabs-content">
                <?php foreach($tabs as $id => $tab): ?>
                    <div id="tab-<?php echo $id; ?>" class="p-tab-pane" style="<?php echo ($active_tab === $id) ? '' : 'display:none;'; ?>">
                        <?php
                        // Load template if it exists
                        $template_path = CONTROL_PATH . 'templates/' . $tab['template'];
                        if ( file_exists($template_path) ) {
                            include $template_path;
                        } else {
                            echo '<div class="control-card">' . sprintf(__('قريباً: %s', 'control'), $tab['label']) . '</div>';
                        }
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<style>
.finance-management-layout { display: flex; }
.p-nav-item.active { background: #1e293b; color: #fff !important; box-shadow: 0 10px 20px rgba(0,0,0,0.15); }
.p-nav-item.active .nav-icon-box { background: rgba(255,255,255,0.1) !important; color: #fff !important; }
.p-nav-item:not(.active):hover { background: #f8fafc; transform: translateX(-5px); }
.p-nav-item:active { transform: scale(0.98); }

/* Table Styling Overrides */
.control-table { width: 100%; border-collapse: separate; border-spacing: 0; margin-top: 10px; }
.control-table th { background: #f8fafc; color: #64748b; font-weight: 800; text-transform: uppercase; font-size: 0.7rem; padding: 15px 20px; border-bottom: 1px solid #edf2f7; text-align: right; }
.control-table td { padding: 15px 20px; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; color: #334155; }
.control-table tr:nth-child(even) { background-color: #fafbfc; }
.control-table tr:hover { background-color: #f1f5f9; transition: 0.2s; }

.action-btn-group { display: flex; gap: 8px; justify-content: flex-end; }
.action-icon-btn { width: 32px; height: 32px; border-radius: 8px; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; background: #f1f5f9; color: #475569; }
.action-icon-btn:hover { background: var(--control-primary); color: #fff; }

@media (max-width: 1024px) {
    .finance-management-layout { flex-direction: column-reverse; }
    .p-internal-sidebar { width: 100% !important; position: static !important; margin-bottom: 30px; }
}
</style>

<script>
jQuery(document).ready(function($) {
    $('.p-nav-item').on('click', function() {
        const targetTab = $(this).data('tab');
        $('.p-nav-item').removeClass('active');
        $(this).addClass('active');
        $('.p-tab-pane').hide();
        $('#' + targetTab).fadeIn(300);

        // Reset search input on tab switch
        $('#finance-search-input').val('');
        applyFinanceFilters();
    });

    function applyFinanceFilters() {
        const query = $('#finance-search-input').val().toLowerCase();
        const activeTabId = $('.p-nav-item.active').data('tab');

        $(`#${activeTabId} table tbody tr`).each(function() {
            const row = $(this);
            const searchText = row.text().toLowerCase();
            // Also check data-search if available
            const dataSearch = row.data('search') ? row.data('search').toLowerCase() : '';

            if (searchText.includes(query) || dataSearch.includes(query)) {
                row.show();
            } else {
                row.hide();
            }
        });

        // Dashboard filtering (if needed, though mostly stats)
        if (activeTabId === 'tab-dashboard') {
            $('.control-card').each(function() {
                if ($(this).text().toLowerCase().includes(query)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    }

    $('#finance-search-input').on('keyup', function() {
        applyFinanceFilters();
    });

    // Global Financial Action Handlers (Modals)
    $(document).on('click', '#add-invoice-btn', function() { $('#invoice-modal').css('display', 'flex'); });
    $(document).on('click', '.record-payment-btn', function() {
        const id = $(this).data('id');
        const num = $(this).data('num');
        const remain = $(this).data('remain');
        $('#pay-invoice-id').val(id);
        $('#pay-invoice-num').text(num);
        $('#pay-amount').val(remain);
        $('#payment-modal').css('display', 'flex');
    });
    $(document).on('click', '#add-expense-btn', function() { $('#expense-modal').css('display', 'flex'); });
    $(document).on('click', '#add-payroll-btn', function() { $('#payroll-modal').css('display', 'flex'); });

    // AJAX Form Submissions
    $('#finance-invoice-form').on('submit', function(e) {
        e.preventDefault();
        // Simplified invoice save logic for brevity in this UI upgrade
        $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_save_fin_invoice&nonce=' + control_ajax.nonce, function(res) {
            if(res.success) location.reload();
        });
    });

    $('#finance-payment-form').on('submit', function(e) {
        e.preventDefault();
        $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_save_fin_payment&nonce=' + control_ajax.nonce, function(res) {
            if(res.success) location.reload();
        });
    });

    $('#finance-expense-form').on('submit', function(e) {
        e.preventDefault();
        $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_save_fin_expense&nonce=' + control_ajax.nonce, function(res) {
            if(res.success) location.reload();
        });
    });

    $('#finance-payroll-form').on('submit', function(e) {
        e.preventDefault();
        $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_save_fin_payroll&nonce=' + control_ajax.nonce, function(res) {
            if(res.success) location.reload();
        });
    });
});
</script>

<!-- Financial Modals -->
<div id="invoice-modal" class="control-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:10001; align-items:center; justify-content:center; backdrop-filter:blur(5px);">
    <div class="control-card" style="width:100%; max-width:600px; padding:35px; border-radius:24px;">
        <h3 style="margin-top:0; font-weight:800;"><?php _e('إنشاء فاتورة جديدة', 'control'); ?></h3>
        <form id="finance-invoice-form">
            <div class="wiz-field no-label">
                <select name="patient_id" required>
                    <option value=""><?php _e('اختر الطفل...', 'control'); ?></option>
                    <?php
                    $pts = $wpdb->get_results("SELECT id, full_name FROM {$wpdb->prefix}control_patients ORDER BY full_name ASC");
                    foreach($pts as $p): echo '<option value="'.$p->id.'">'.esc_html($p->full_name).'</option>'; endforeach;
                    ?>
                </select>
            </div>
            <div class="wiz-grid">
                <div class="wiz-field no-label"><input type="text" name="invoice_number" placeholder="INV-XXXX" value="INV-<?php echo time(); ?>" required></div>
                <div class="wiz-field no-label"><input type="date" name="invoice_date" value="<?php echo date('Y-m-d'); ?>" required></div>
            </div>
            <div class="wiz-field no-label"><input type="number" name="total_amount" placeholder="<?php _e('المبلغ الإجمالي', 'control'); ?>" step="0.01" required></div>
            <div class="wiz-field no-label"><textarea name="notes" placeholder="<?php _e('ملاحظات الفاتورة...', 'control'); ?>" rows="2"></textarea></div>
            <input type="hidden" name="subtotal" value="0"> <!-- Calculated backend or simplified for now -->
            <input type="hidden" name="items" value="[]">
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="control-btn" style="flex:1; background:var(--control-primary); border:none;"><?php _e('حفظ الفاتورة', 'control'); ?></button>
                <button type="button" onclick="jQuery('.control-modal').hide()" class="control-btn" style="flex:1; background:#f1f5f9; color:#475569 !important; border:none;"><?php _e('إلغاء', 'control'); ?></button>
            </div>
        </form>
    </div>
</div>

<div id="payment-modal" class="control-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:10002; align-items:center; justify-content:center; backdrop-filter:blur(5px);">
    <div class="control-card" style="width:100%; max-width:450px; padding:35px; border-radius:24px;">
        <h3 style="margin-top:0; font-weight:800;"><?php _e('تحصيل دفعة مالية', 'control'); ?></h3>
        <p style="color:var(--control-muted); font-size:0.9rem;"><?php _e('فاتورة رقم:', 'control'); ?> <strong id="pay-invoice-num" style="color:var(--control-primary);"></strong></p>
        <form id="finance-payment-form">
            <input type="hidden" name="invoice_id" id="pay-invoice-id">
            <div class="wiz-field no-label"><input type="number" name="amount" id="pay-amount" placeholder="<?php _e('المبلغ المراد تحصيله', 'control'); ?>" step="0.01" required></div>
            <div class="wiz-field no-label">
                <select name="payment_method" required>
                    <option value="cash"><?php _e('نقدي', 'control'); ?></option>
                    <option value="bank_transfer"><?php _e('تحويل بنكي', 'control'); ?></option>
                    <option value="card"><?php _e('بطاقة بنكية', 'control'); ?></option>
                </select>
            </div>
            <div class="wiz-field no-label"><input type="text" name="transaction_id" placeholder="<?php _e('رقم العملية / المرجع', 'control'); ?>"></div>
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="control-btn" style="flex:1; background:#10b981; border:none;"><?php _e('تأكيد التحصيل', 'control'); ?></button>
                <button type="button" onclick="jQuery('.control-modal').hide()" class="control-btn" style="flex:1; background:#f1f5f9; color:#475569 !important; border:none;"><?php _e('إلغاء', 'control'); ?></button>
            </div>
        </form>
    </div>
</div>

<div id="expense-modal" class="control-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:10003; align-items:center; justify-content:center; backdrop-filter:blur(5px);">
    <div class="control-card" style="width:100%; max-width:500px; padding:35px; border-radius:24px;">
        <h3 style="margin-top:0; font-weight:800;"><?php _e('تسجيل مصروفات', 'control'); ?></h3>
        <form id="finance-expense-form">
            <div class="wiz-field no-label">
                <select name="category" required>
                    <option value="rent"><?php _e('الإيجار', 'control'); ?></option>
                    <option value="equipment"><?php _e('الأجهزة والمعدات', 'control'); ?></option>
                    <option value="utilities"><?php _e('المرافق (كهرباء/ماء)', 'control'); ?></option>
                    <option value="misc"><?php _e('مصروفات متنوعة', 'control'); ?></option>
                </select>
            </div>
            <div class="wiz-field no-label"><input type="text" name="description" placeholder="<?php _e('وصف المصروف...', 'control'); ?>" required></div>
            <div class="wiz-grid">
                <div class="wiz-field no-label"><input type="number" name="amount" placeholder="<?php _e('المبلغ', 'control'); ?>" step="0.01" required></div>
                <div class="wiz-field no-label"><input type="date" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required></div>
            </div>
            <div class="wiz-field no-label" style="display:flex; align-items:center; gap:10px; padding:10px;">
                <input type="checkbox" name="is_recurring" value="1" style="width:20px; height:20px;">
                <label style="font-size:0.85rem; font-weight:700; color:var(--control-muted);"><?php _e('مصروف دوري (شهري)', 'control'); ?></label>
            </div>
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="control-btn" style="flex:1; background:#ef4444; border:none;"><?php _e('حفظ المصروف', 'control'); ?></button>
                <button type="button" onclick="jQuery('.control-modal').hide()" class="control-btn" style="flex:1; background:#f1f5f9; color:#475569 !important; border:none;"><?php _e('إلغاء', 'control'); ?></button>
            </div>
        </form>
    </div>
</div>

<div id="payroll-modal" class="control-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:10004; align-items:center; justify-content:center; backdrop-filter:blur(5px);">
    <div class="control-card" style="width:100%; max-width:550px; padding:35px; border-radius:24px;">
        <h3 style="margin-top:0; font-weight:800;"><?php _e('صرف راتب للأخصائي', 'control'); ?></h3>
        <form id="finance-payroll-form">
            <div class="wiz-field no-label">
                <select name="specialist_id" required>
                    <?php
                    $staff = $wpdb->get_results("SELECT id, first_name, last_name, role FROM {$wpdb->prefix}control_staff ORDER BY first_name ASC");
                    foreach($staff as $s): echo '<option value="'.$s->id.'">'.esc_html($s->first_name . ' ' . $s->last_name).' ('.$s->role.')</option>'; endforeach;
                    ?>
                </select>
            </div>
            <div class="wiz-grid">
                <div class="wiz-field no-label">
                    <select name="month" required>
                        <?php for($m=1; $m<=12; $m++): echo '<option value="'.$m.'" '.selected(date('n'), $m, false).'>'.$m.'</option>'; endfor; ?>
                    </select>
                </div>
                <div class="wiz-field no-label">
                    <select name="year" required>
                        <?php for($y=date('Y'); $y>=2020; $y--): echo '<option value="'.$y.'">'.$y.'</option>'; endfor; ?>
                    </select>
                </div>
            </div>
            <div class="wiz-grid">
                <div class="wiz-field no-label"><input type="number" name="base_salary" placeholder="<?php _e('الراتب الأساسي', 'control'); ?>" step="0.01" required></div>
                <div class="wiz-field no-label"><input type="number" name="net_salary" placeholder="<?php _e('صافي الراتب', 'control'); ?>" step="0.01" required></div>
            </div>
            <div class="wiz-grid">
                <div class="wiz-field no-label"><input type="number" name="incentives" placeholder="<?php _e('حوافز', 'control'); ?>" step="0.01" value="0"></div>
                <div class="wiz-field no-label"><input type="number" name="deductions" placeholder="<?php _e('خصومات', 'control'); ?>" step="0.01" value="0"></div>
            </div>
            <div class="wiz-field no-label">
                <select name="payment_status">
                    <option value="unpaid"><?php _e('معلق', 'control'); ?></option>
                    <option value="paid"><?php _e('مدفوع', 'control'); ?></option>
                </select>
            </div>
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="control-btn" style="flex:1; background:var(--control-primary); border:none;"><?php _e('اعتماد الراتب', 'control'); ?></button>
                <button type="button" onclick="jQuery('.control-modal').hide()" class="control-btn" style="flex:1; background:#f1f5f9; color:#475569 !important; border:none;"><?php _e('إلغاء', 'control'); ?></button>
            </div>
        </form>
    </div>
</div>
