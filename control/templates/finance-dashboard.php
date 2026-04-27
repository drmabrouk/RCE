<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

// Force Data Binding Re-connection
$total_invoiced  = $wpdb->get_var("SELECT SUM(total_amount) FROM {$wpdb->prefix}control_fin_invoices") ?: 0;
$total_collected = $wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}control_fin_payments") ?: 0;
$total_expenses  = $wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}control_fin_expenses") ?: 0;
$total_payroll   = $wpdb->get_var("SELECT SUM(net_salary) FROM {$wpdb->prefix}control_fin_payroll WHERE payment_status = 'paid'") ?: 0;

$pending_collect = $total_invoiced - $total_collected;
$net_balance     = $total_collected - ($total_expenses + $total_payroll);
$ratio = ($total_invoiced > 0) ? round(($total_collected / $total_invoiced) * 100) : 0;
?>

<!-- 1. Forced UI Layout Rebuild: 6 Cards in ONE row -->
<div class="financial-summary-grid">

    <!-- Card 1: Invoiced -->
    <div class="stat-card-refined" style="border-right: 4px solid #10b981; background: #f0fdf4 !important;">
        <div class="label"><?php _e('المفوتر', 'control'); ?></div>
        <div class="value" style="color:#047857;"><?php echo number_format($total_invoiced, 0); ?></div>
        <div class="sub" style="color:#059669;"><?php _e('إجمالي الفواتير', 'control'); ?></div>
    </div>

    <!-- Card 2: Collected -->
    <div class="stat-card-refined" style="border-right: 4px solid #059669; background: #ecfdf5 !important;">
        <div class="label"><?php _e('المحصل', 'control'); ?></div>
        <div class="value" style="color:#064e3b;"><?php echo number_format($total_collected, 0); ?></div>
        <div class="sub" style="color:#059669;"><?php echo $ratio; ?>% <?php _e('نجاح التحصيل', 'control'); ?></div>
    </div>

    <!-- Card 3: Pending -->
    <div class="stat-card-refined" style="border-right: 4px solid #f59e0b; background: #fffbeb !important;">
        <div class="label"><?php _e('المتبقي', 'control'); ?></div>
        <div class="value" style="color:#b45309;"><?php echo number_format($pending_collect, 0); ?></div>
        <div class="sub" style="color:#d97706;"><?php _e('مستحقات معلقة', 'control'); ?></div>
    </div>

    <!-- Card 4: Payroll -->
    <div class="stat-card-refined" style="border-right: 4px solid #8b5cf6; background: #f5f3ff !important;">
        <div class="label"><?php _e('الرواتب', 'control'); ?></div>
        <div class="value" style="color:#6d28d9;"><?php echo number_format($total_payroll, 0); ?></div>
        <div class="sub" style="color:#7c3aed;"><?php _e('رواتب الكادر', 'control'); ?></div>
    </div>

    <!-- Card 5: Expenses -->
    <div class="stat-card-refined" style="border-right: 4px solid #ef4444; background: #fef2f2 !important;">
        <div class="label"><?php _e('المصروفات', 'control'); ?></div>
        <div class="value" style="color:#b91c1c;"><?php echo number_format($total_expenses, 0); ?></div>
        <div class="sub" style="color:#dc2626;"><?php _e('تشغيلية وعامة', 'control'); ?></div>
    </div>

    <!-- Card 6: Net Balance -->
    <div class="stat-card-refined" style="border-right: 4px solid #3b82f6; background: #eff6ff !important;">
        <div class="label"><?php _e('السيولة', 'control'); ?></div>
        <div class="value" style="color:#1d4ed8;"><?php echo number_format($net_balance, 0); ?></div>
        <div class="sub" style="color:#2563eb;"><?php _e('الرصيد المتاح', 'control'); ?></div>
    </div>
</div>

<div class="control-grid" style="grid-template-columns: 1.8fr 1.2fr; gap:25px;">
    <!-- Recent Activities Re-build -->
    <div class="control-card" style="padding:0; overflow:hidden; border-radius:16px; border:1px solid #eef2f6;">
        <div style="padding:18px 25px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; background:#fff;">
            <h3 style="margin:0; font-size:0.95rem; font-weight:900; color:var(--control-primary);"><?php _e('سجل العمليات الأخير', 'control'); ?></h3>
            <button onclick="location.reload()" class="control-btn" style="padding:4px 12px; font-size:0.65rem; background:#f8fafc; color:var(--control-primary) !important; border:1.5px solid #eee;"><?php _e('تحديث', 'control'); ?></button>
        </div>
        <table class="control-table-refined">
            <thead>
                <tr>
                    <th><?php _e('العملية', 'control'); ?></th>
                    <th><?php _e('التاريخ', 'control'); ?></th>
                    <th><?php _e('المبلغ', 'control'); ?></th>
                    <th><?php _e('الحالة', 'control'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $activities = $wpdb->get_results("
                    (SELECT 'payment' as type, amount, payment_date as entry_date, 'collected' as status_label FROM {$wpdb->prefix}control_fin_payments)
                    UNION ALL
                    (SELECT 'expense' as type, amount, expense_date as entry_date, 'paid' as status_label FROM {$wpdb->prefix}control_fin_expenses)
                    ORDER BY entry_date DESC LIMIT 8
                ");
                if($activities): foreach($activities as $act): ?>
                    <tr>
                        <td class="font-bold"><?php echo $act->type === 'payment' ? __('تحصيل دفعة', 'control') : __('صرف مصروفات', 'control'); ?></td>
                        <td><?php echo date('Y/m/d', strtotime($act->entry_date)); ?></td>
                        <td class="font-bold" style="color:<?php echo $act->type === 'payment' ? '#059669' : '#dc2626'; ?>;"><?php echo number_format($act->amount, 2); ?></td>
                        <td><span class="badge-status-refined status-<?php echo $act->status_label; ?>"><?php echo $act->status_label; ?></span></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="4" style="text-align:center; padding:30px; color:var(--control-muted);"><?php _e('لا توجد بيانات.', 'control'); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Collection KPI Re-build -->
    <div class="control-card" style="padding:25px; border-radius:16px; border:1px solid #eef2f6;">
        <h3 style="margin:0 0 25px 0; font-size:1rem; font-weight:900; color:var(--control-primary);"><?php _e('أداء التحصيل المالي', 'control'); ?></h3>
        <div style="text-align:center; margin-bottom:25px;">
            <div style="position:relative; width:110px; height:110px; margin:0 auto;">
                <svg viewBox="0 0 36 36" style="width:100%; height:100%; transform: rotate(-90deg);">
                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#f1f5f9" stroke-width="2.5" />
                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#10b981" stroke-width="2.5" stroke-dasharray="<?php echo $ratio; ?>, 100" />
                </svg>
                <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); font-size:1.6rem; font-weight:900; color:var(--control-primary);"><?php echo $ratio; ?>%</div>
            </div>
            <p style="margin:15px 0 0 0; font-size:0.8rem; color:var(--control-muted); font-weight:700;"><?php _e('نسبة التحصيل الفعلي من المفوتر', 'control'); ?></p>
        </div>
        <div style="display:flex; flex-direction:column; gap:12px; background:#f8fafc; padding:15px; border-radius:12px;">
             <div style="display:flex; justify-content:space-between; font-size:0.8rem;"><span><?php _e('بانتظار التحصيل', 'control'); ?></span><strong class="text-danger"><?php echo number_format($pending_collect, 2); ?></strong></div>
             <div style="display:flex; justify-content:space-between; font-size:0.8rem;"><span><?php _e('إجمالي المطالبات', 'control'); ?></span><strong class="font-bold"><?php echo number_format($total_invoiced, 2); ?></strong></div>
        </div>
    </div>
</div>
