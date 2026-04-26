<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$can_manage = Control_Auth::has_permission('finance_manage');

// Aggregated Data Sources
$total_invoiced = $wpdb->get_var("SELECT SUM(total_amount) FROM {$wpdb->prefix}control_fin_invoices") ?: 0;
$total_collected = $wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}control_fin_payments") ?: 0;
$pending_payments = $total_invoiced - $total_collected;

$total_expenses = $wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}control_fin_expenses") ?: 0;
$total_payroll = $wpdb->get_var("SELECT SUM(net_salary) FROM {$wpdb->prefix}control_fin_payroll WHERE payment_status = 'paid'") ?: 0;

$net_balance = $total_collected - ($total_expenses + $total_payroll);
?>

<!-- Financial Professional Header -->
<div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h3 style="margin:0; font-weight:800; color:var(--control-primary);"><?php _e('نظرة عامة على البيانات المالية', 'control'); ?></h3>
    <button onclick="window.print()" class="control-btn" style="background:#fff; color:var(--control-primary) !important; border:1.5px solid #e2e8f0; font-weight:800;">
        <span class="dashicons dashicons-printer" style="margin-left:5px;"></span><?php _e('تصدير تقرير مالي', 'control'); ?>
    </button>
</div>

<!-- Professional Stats Grid -->
<div class="control-grid" style="grid-template-columns: repeat(3, 1fr); gap:20px; margin-bottom:30px;">

    <!-- Row 1: Income -->
    <div class="control-card stat-card" style="border-right: 6px solid #10b981; background: #f0fdf4;">
        <div style="color:#065f46; font-size:0.8rem; font-weight:800;"><?php _e('إجمالي الإيرادات (المفوترة)', 'control'); ?></div>
        <div style="font-size:1.8rem; font-weight:900; color:#047857; margin:10px 0;"><?php echo number_format($total_invoiced, 2); ?> <small>AED</small></div>
        <div style="font-size:0.75rem; color:#059669;"><?php _e('إجمالي مبالغ الفواتير المصدرة', 'control'); ?></div>
    </div>

    <div class="control-card stat-card" style="border-right: 6px solid #059669; background: #ecfdf5;">
        <div style="color:#064e3b; font-size:0.8rem; font-weight:800;"><?php _e('المبالغ المحصلة فعلياً', 'control'); ?></div>
        <div style="font-size:1.8rem; font-weight:900; color:#064e3b; margin:10px 0;"><?php echo number_format($total_collected, 2); ?> <small>AED</small></div>
        <div style="font-size:0.75rem; color:#059669;">
            <div style="height:6px; background:#d1fae5; border-radius:3px; margin-top:5px; overflow:hidden;">
                <div style="height:100%; background:#10b981; width:<?php echo ($total_invoiced > 0) ? ($total_collected / $total_invoiced * 100) : 0; ?>%;"></div>
            </div>
        </div>
    </div>

    <div class="control-card stat-card" style="border-right: 6px solid #f59e0b; background: #fffbeb;">
        <div style="color:#92400e; font-size:0.8rem; font-weight:800;"><?php _e('مدفوعات معلقة', 'control'); ?></div>
        <div style="font-size:1.8rem; font-weight:900; color:#b45309; margin:10px 0;"><?php echo number_format($pending_payments, 2); ?> <small>AED</small></div>
        <div style="font-size:0.75rem; color:#d97706;"><?php _e('مبالغ لم يتم تحصيلها بعد', 'control'); ?></div>
    </div>

    <!-- Row 2: Expenses & Balance -->
    <div class="control-card stat-card" style="border-right: 6px solid #8b5cf6; background: #f5f3ff;">
        <div style="color:#5b21b6; font-size:0.8rem; font-weight:800;"><?php _e('رواتب الكادر', 'control'); ?></div>
        <div style="font-size:1.8rem; font-weight:900; color:#6d28d9; margin:10px 0;"><?php echo number_format($total_payroll, 2); ?> <small>AED</small></div>
        <div style="font-size:0.75rem; color:#7c3aed;"><?php _e('إجمالي الرواتب المدفوعة', 'control'); ?></div>
    </div>

    <div class="control-card stat-card" style="border-right: 6px solid #ef4444; background: #fef2f2;">
        <div style="color:#991b1b; font-size:0.8rem; font-weight:800;"><?php _e('المصروفات التشغيلية', 'control'); ?></div>
        <div style="font-size:1.8rem; font-weight:900; color:#b91c1c; margin:10px 0;"><?php echo number_format($total_expenses, 2); ?> <small>AED</small></div>
        <div style="font-size:0.75rem; color:#dc2626;"><?php _e('إجمالي المصاريف العامة', 'control'); ?></div>
    </div>

    <div class="control-card stat-card" style="border-right: 6px solid #3b82f6; background: #eff6ff;">
        <div style="color:#1e40af; font-size:0.8rem; font-weight:800;"><?php _e('صافي الرصيد (النقدي)', 'control'); ?></div>
        <div style="font-size:1.8rem; font-weight:900; color:#1d4ed8; margin:10px 0;"><?php echo number_format($net_balance, 2); ?> <small>AED</small></div>
        <div style="font-size:0.75rem; color:#2563eb;"><?php _e('التحصيلات - (المصاريف + الرواتب)', 'control'); ?></div>
    </div>
</div>

<div class="control-grid" style="grid-template-columns: 2fr 1fr; gap:25px;">
    <!-- Recent Financial Movements -->
    <div class="control-card" style="padding:0;">
        <div style="padding:25px; border-bottom:1.5px solid #f1f5f9;">
            <h3 style="margin:0; font-size:1.1rem; font-weight:800;"><?php _e('آخر العمليات المالية', 'control'); ?></h3>
        </div>
        <table class="control-table">
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
                // Union of recent payments and expenses
                $activities = $wpdb->get_results("
                    (SELECT 'payment' as type, amount, payment_date as entry_date, 'AED' as currency, 'collected' as status_label FROM {$wpdb->prefix}control_fin_payments)
                    UNION ALL
                    (SELECT 'expense' as type, amount, expense_date as entry_date, 'AED' as currency, 'paid' as status_label FROM {$wpdb->prefix}control_fin_expenses)
                    ORDER BY entry_date DESC LIMIT 8
                ");

                if($activities): foreach($activities as $act): ?>
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div style="width:30px; height:30px; border-radius:8px; display:flex; align-items:center; justify-content:center; background:<?php echo $act->type === 'payment' ? '#ecfdf5' : '#fef2f2'; ?>; color:<?php echo $act->type === 'payment' ? '#059669' : '#dc2626'; ?>;">
                                    <span class="dashicons dashicons-<?php echo $act->type === 'payment' ? 'arrow-down-alt' : 'arrow-up-alt'; ?>"></span>
                                </div>
                                <span style="font-weight:700;"><?php echo $act->type === 'payment' ? __('تحصيل دفعة', 'control') : __('صرف مصروفات', 'control'); ?></span>
                            </div>
                        </td>
                        <td><?php echo date('Y/m/d', strtotime($act->entry_date)); ?></td>
                        <td style="font-weight:800; color:<?php echo $act->type === 'payment' ? '#059669' : '#dc2626'; ?>;"><?php echo number_format($act->amount, 2); ?></td>
                        <td><span class="fin-status-badge status-<?php echo $act->status_label; ?>"><?php echo $act->status_label; ?></span></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="4" style="text-align:center; padding:30px; color:var(--control-muted);"><?php _e('لا توجد عمليات مسجلة.', 'control'); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Collection Performance -->
    <div class="control-card" style="padding:25px;">
        <h3 style="margin:0 0 25px 0; font-size:1.1rem; font-weight:800;"><?php _e('مؤشر التحصيل', 'control'); ?></h3>
        <div style="text-align:center; margin-bottom:30px;">
            <?php
            $ratio = ($total_invoiced > 0) ? round(($total_collected / $total_invoiced) * 100) : 0;
            ?>
            <div style="position:relative; width:120px; height:120px; margin:0 auto;">
                <svg viewBox="0 0 36 36" style="width:100%; height:100%; transform: rotate(-90deg);">
                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#f1f5f9" stroke-width="3" />
                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#10b981" stroke-width="3" stroke-dasharray="<?php echo $ratio; ?>, 100" />
                </svg>
                <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); font-size:1.5rem; font-weight:900; color:var(--control-primary);"><?php echo $ratio; ?>%</div>
            </div>
            <p style="margin:15px 0 0 0; font-size:0.85rem; color:var(--control-muted); font-weight:700;"><?php _e('نسبة المبالغ المحصلة من إجمالي الفواتير', 'control'); ?></p>
        </div>

        <div style="display:flex; flex-direction:column; gap:12px;">
             <div style="display:flex; justify-content:space-between; font-size:0.8rem;"><span><?php _e('المتبقي للتحصيل', 'control'); ?></span><strong style="color:#d97706;"><?php echo number_format($pending_payments, 2); ?></strong></div>
             <div style="display:flex; justify-content:space-between; font-size:0.8rem;"><span><?php _e('إجمالي الفواتير', 'control'); ?></span><strong><?php echo number_format($total_invoiced, 2); ?></strong></div>
        </div>
    </div>
</div>

<style>
.stat-card { transition: 0.3s; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
.stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 40px rgba(0,0,0,0.05); }
.fin-status-badge { font-size: 0.6rem; padding: 3px 10px; border-radius: 50px; font-weight: 800; text-transform: uppercase; }
.status-collected { background: #dcfce7; color: #166534; }
.status-paid { background: #fee2e2; color: #991b1b; }
</style>
