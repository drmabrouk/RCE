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
    <!-- Forced Flex Container - Cache Busting Rebuild -->
    <div style="display:flex !important; flex-direction:row !important; flex-wrap:nowrap !important; gap:30px; align-items:flex-start; width: 100%;" class="finance-management-layout">

        <!-- Right Column: Navigation Sidebar (Fixed Width) -->
        <div class="p-internal-sidebar" style="width:280px !important; flex: 0 0 280px !important; position:sticky; top:100px; z-index: 10;">

            <!-- Context-Based Search Box -->
            <div class="control-card" style="padding:12px; border-radius:16px; background:#fff; border:1px solid #e2e8f0; margin-bottom:20px; box-shadow:0 10px 25px rgba(0,0,0,0.02);">
                <div style="position:relative;">
                    <span class="dashicons dashicons-search" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); color:var(--control-muted); font-size: 16px;"></span>
                    <input type="text" id="finance-search-input" placeholder="<?php _e('بحث سريع...', 'control'); ?>" style="padding:10px 35px 10px 10px; border-radius:10px; border:1.5px solid #f1f5f9; width:100%; font-size: 0.85rem; background:#fcfdfe; font-weight:700;">
                </div>
            </div>

            <!-- Financial Sidebar Tabs -->
            <div class="control-card" style="padding:8px; border-radius:16px; background:#fff; border:1px solid #eef2f6; box-shadow:0 10px 30px rgba(0,0,0,0.02); overflow:hidden;">
                <div style="padding:10px 15px 8px; color:var(--control-muted); font-size:0.65rem; font-weight:900; text-transform:uppercase; letter-spacing:1px; opacity: 0.6;">
                    <?php _e('محاور الإدارة المالية', 'control'); ?>
                </div>
                <?php foreach($tabs as $id => $tab): ?>
                    <div class="p-nav-item <?php echo ($active_tab === $id) ? 'active' : ''; ?>" data-tab="tab-<?php echo $id; ?>" style="display:flex; align-items:center; gap:12px; padding:10px 15px; border-radius:12px; cursor:pointer; transition:0.3s; margin-bottom:2px;">
                        <div class="nav-icon-box" style="width:30px; height:30px; border-radius:8px; background:#f8fafc; display:flex; align-items:center; justify-content:center; color:var(--control-muted);">
                            <span class="dashicons dashicons-<?php echo $tab['icon']; ?>" style="font-size: 16px;"></span>
                        </div>
                        <span style="font-weight:800; flex:1; font-size:0.85rem;"><?php echo $tab['label']; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- NET Balance (Refined No Dark Box) -->
            <div class="p-sidebar-stats" style="margin-top:20px; padding:20px; background:#fff; border-radius:16px; text-align:center; border:1px solid #eef2f6; box-shadow:0 10px 30px rgba(0,0,0,0.02);">
                <p style="margin:0; font-size:0.65rem; color:var(--control-muted); font-weight:900; text-transform:uppercase;"><?php _e('السيولة النقدية الصافية', 'control'); ?></p>
                <?php
                $revenue = $wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}control_fin_payments") ?: 0;
                $expenses = $wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}control_fin_expenses") ?: 0;
                $payroll = $wpdb->get_var("SELECT SUM(net_salary) FROM {$wpdb->prefix}control_fin_payroll WHERE payment_status = 'paid'") ?: 0;
                $net_balance = $revenue - ($expenses + $payroll);
                ?>
                <div style="font-size:1.8rem; font-weight:900; color:var(--control-primary); margin:2px 0;"><?php echo number_format($net_balance, 2); ?></div>
                <div style="font-size:0.6rem; color:var(--control-muted); font-weight:800;"><?php _e('درهم إماراتي', 'control'); ?></div>
            </div>
        </div>

        <!-- Left Column: Content Area (Flexible Width) -->
        <div style="flex:1 !important; min-width:0 !important; width: 100%;">
            <div id="finance-tabs-content">
                <?php foreach($tabs as $id => $tab): ?>
                    <div id="tab-<?php echo $id; ?>" class="p-tab-pane" style="<?php echo ($active_tab === $id) ? '' : 'display:none;'; ?>">
                        <?php
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
/* Sidebar Active State Sync */
.p-nav-item.active { background: var(--control-primary) !important; color: #fff !important; box-shadow: 0 8px 15px rgba(0,0,0,0.1); }
.p-nav-item.active .nav-icon-box { background: rgba(255,255,255,0.1) !important; color: #fff !important; }
.p-nav-item:not(.active):hover { background: #f8fafc; transform: translateX(-3px); }

/* Remove Legacy Components via CSS Force */
.dark-bottom-box, #old-financial-cards, .legacy-table-ui { display: none !important; visibility: hidden !important; height: 0 !important; margin: 0 !important; }

@media (max-width: 1200px) {
    .finance-management-layout { flex-direction: column-reverse !important; flex-wrap: wrap !important; }
    .p-internal-sidebar { width: 100% !important; flex: 1 0 100% !important; position: static !important; }
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
        $('#finance-search-input').val('');
    });

    $('#finance-search-input').on('keyup', function() {
        const query = $(this).val().toLowerCase();
        const activeTabId = $('.p-nav-item.active').data('tab');
        $(`#${activeTabId} table tbody tr`).each(function() {
            const text = $(this).text().toLowerCase();
            const searchAttr = $(this).data('search') ? $(this).data('search').toLowerCase() : '';
            if (text.includes(query) || searchAttr.includes(query)) $(this).show(); else $(this).hide();
        });
    });
});
</script>
