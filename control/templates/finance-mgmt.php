<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'dashboard';
$can_manage = Control_Auth::has_permission('finance_manage');
$can_invoice = Control_Auth::has_permission('finance_invoicing');
$can_payroll = Control_Auth::has_permission('finance_payroll_view');

// Security: If specialist can only see payroll, force that tab if they try to access others
if ( ! $can_manage && ! $can_invoice && $can_payroll ) {
    $active_tab = 'payroll';
}

$tabs = array();

if ( $can_manage || $can_payroll ) {
    $tabs['dashboard'] = array(
        'label' => __('لوحة المعلومات', 'control'),
        'icon'  => 'dashicons-chart-bar',
        'template' => 'finance-dashboard.php'
    );
}

if ( $can_invoice || $can_manage ) {
    $tabs['invoices'] = array(
        'label' => __('الفواتير والتحصيل', 'control'),
        'icon'  => 'dashicons-cart',
        'template' => 'finance-invoices.php'
    );
}

if ( $can_payroll || $can_manage ) {
    $tabs['payroll'] = array(
        'label' => __('رواتب الأخصائيين', 'control'),
        'icon'  => 'dashicons-money-alt',
        'template' => 'finance-payroll.php'
    );
}

if ( $can_manage ) {
    $tabs['expenses'] = array(
        'label' => __('إدارة المصروفات', 'control'),
        'icon'  => 'dashicons-trending-down',
        'template' => 'finance-expenses.php'
    );
}

// Fallback if requested tab doesn't exist for user
if ( ! isset( $tabs[$active_tab] ) ) {
    $active_tab = array_key_first( $tabs );
}
?>

<div class="control-module-container">
    <div class="control-header-flex" style="margin-bottom: 25px; border-bottom: 1px solid var(--control-border); padding-bottom: 0;">
        <div style="display:flex; align-items:center; gap:15px; margin-bottom: 15px;">
            <div style="width: 50px; height: 50px; background: var(--control-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #fff;">
                <span class="dashicons dashicons-bank" style="font-size: 24px; width: 24px; height: 24px;"></span>
            </div>
            <div>
                <h2 style="font-weight:800; font-size:1.4rem; margin:0; color:var(--control-text-dark);"><?php _e('الإدارة المالية', 'control'); ?></h2>
                <p style="margin:0; color:var(--control-muted); font-size:0.85rem;"><?php _e('إدارة شاملة للإيرادات، المصروفات، والرواتب.', 'control'); ?></p>
            </div>
        </div>

        <div class="control-tabs-nav" style="display:flex; gap:10px; margin-top:10px;">
            <?php foreach ( $tabs as $id => $tab ) : ?>
                <a href="<?php echo add_query_arg( 'tab', $id ); ?>" class="tab-nav-item <?php echo ($active_tab === $id) ? 'active' : ''; ?>">
                    <span class="dashicons <?php echo $tab['icon']; ?>"></span>
                    <?php echo $tab['label']; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="control-tab-content-area">
        <?php
        if ( isset( $tabs[$active_tab] ) ) {
            include CONTROL_PATH . 'templates/' . $tabs[$active_tab]['template'];
        }
        ?>
    </div>
</div>

<style>
.tab-nav-item {
    padding: 12px 25px;
    text-decoration: none;
    color: var(--control-muted);
    font-weight: 700;
    font-size: 0.9rem;
    border-bottom: 3px solid transparent;
    transition: 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
}
.tab-nav-item .dashicons { font-size: 18px; width: 18px; height: 18px; }
.tab-nav-item:hover { color: var(--control-primary); background: rgba(0,0,0,0.02); }
.tab-nav-item.active { color: var(--control-primary); border-bottom-color: var(--control-accent); background: #fff; }
</style>
