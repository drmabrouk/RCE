<?php
global $wpdb;
$can_manage = Control_Auth::has_permission('finance_manage');

// Simple stats for the dashboard
$total_revenue = $wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}control_fin_payments") ?: 0;
$total_expenses = $wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}control_fin_expenses") ?: 0;
$total_payroll = $wpdb->get_var("SELECT SUM(net_salary) FROM {$wpdb->prefix}control_fin_payroll WHERE payment_status = 'paid'") ?: 0;
$outstanding = $wpdb->get_var("SELECT SUM(total_amount - paid_amount) FROM {$wpdb->prefix}control_fin_invoices") ?: 0;

$net_profit = $total_revenue - ($total_expenses + $total_payroll);
?>

<div style="display:flex; justify-content: flex-end; margin-bottom: 20px;">
    <button onclick="window.print()" class="control-btn" style="background:#fff; color:var(--control-text-dark) !important; border:1px solid var(--control-border);">
        <span class="dashicons dashicons-printer" style="margin-left:5px;"></span><?php _e('طباعة التقرير', 'control'); ?>
    </button>
</div>

<!-- Stats Cards -->
<div class="control-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:20px; margin-bottom:30px;">
    <div class="control-card" style="border-right: 5px solid #10b981;">
        <div style="color:var(--control-muted); font-size:0.85rem; font-weight:700;"><?php _e('إجمالي الإيرادات', 'control'); ?></div>
        <div style="font-size:1.8rem; font-weight:800; color:#059669; margin:10px 0;"><?php echo number_format($total_revenue, 2); ?> <small style="font-size:0.9rem;">EGP</small></div>
        <div style="font-size:0.75rem; color:#059669; background:#ecfdf5; display:inline-block; padding:2px 8px; border-radius:10px;">+12% <?php _e('عن الشهر الماضي', 'control'); ?></div>
    </div>

    <div class="control-card" style="border-right: 5px solid #ef4444;">
        <div style="color:var(--control-muted); font-size:0.85rem; font-weight:700;"><?php _e('إجمالي المصروفات (تشغيل + رواتب)', 'control'); ?></div>
        <div style="font-size:1.8rem; font-weight:800; color:#dc2626; margin:10px 0;"><?php echo number_format($total_expenses + $total_payroll, 2); ?> <small style="font-size:0.9rem;">EGP</small></div>
        <div style="font-size:0.75rem; color:#dc2626;"><?php echo number_format($total_expenses, 2); ?> <?php _e('عامة', 'control'); ?> | <?php echo number_format($total_payroll, 2); ?> <?php _e('رواتب', 'control'); ?></div>
    </div>

    <div class="control-card" style="border-right: 5px solid #3b82f6;">
        <div style="color:var(--control-muted); font-size:0.85rem; font-weight:700;"><?php _e('صافي الربح', 'control'); ?></div>
        <div style="font-size:1.8rem; font-weight:800; color:#2563eb; margin:10px 0;"><?php echo number_format($net_profit, 2); ?> <small style="font-size:0.9rem;">EGP</small></div>
        <div style="font-size:0.75rem; color:#2563eb; background:#eff6ff; display:inline-block; padding:2px 8px; border-radius:10px;"><?php echo ($total_revenue > 0) ? round(($net_profit / $total_revenue) * 100, 1) : 0; ?>% <?php _e('هامش الربح', 'control'); ?></div>
    </div>

    <div class="control-card" style="border-right: 5px solid #f59e0b;">
        <div style="color:var(--control-muted); font-size:0.85rem; font-weight:700;"><?php _e('مبالغ مستحقة (لم تدفع)', 'control'); ?></div>
        <div style="font-size:1.8rem; font-weight:800; color:#d97706; margin:10px 0;"><?php echo number_format($outstanding, 2); ?> <small style="font-size:0.9rem;">EGP</small></div>
        <div style="font-size:0.75rem; color:#d97706;"><?php _e('فواتير معلقة الدفع', 'control'); ?></div>
    </div>
</div>

<div class="control-grid" style="grid-template-columns: 2fr 1fr; gap:25px;">
    <!-- Recent Invoices -->
    <div class="control-card" style="padding:0;">
        <div style="padding:20px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; font-size:1.1rem;"><?php _e('آخر الفواتير الصادرة', 'control'); ?></h3>
            <a href="<?php echo add_query_arg('control_view', 'fin_invoices'); ?>" style="font-size:0.85rem; color:var(--control-primary); text-decoration:none; font-weight:700;"><?php _e('عرض الكل', 'control'); ?></a>
        </div>
        <table class="control-table">
            <thead>
                <tr>
                    <th><?php _e('رقم الفاتورة', 'control'); ?></th>
                    <th><?php _e('الطفل', 'control'); ?></th>
                    <th><?php _e('التاريخ', 'control'); ?></th>
                    <th><?php _e('المبلغ', 'control'); ?></th>
                    <th><?php _e('الحالة', 'control'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $recent_invoices = $wpdb->get_results("SELECT i.*, p.full_name FROM {$wpdb->prefix}control_fin_invoices i JOIN {$wpdb->prefix}control_patients p ON i.patient_id = p.id ORDER BY i.created_at DESC LIMIT 5");
                if($recent_invoices): foreach($recent_invoices as $inv): ?>
                    <tr>
                        <td><strong><?php echo $inv->invoice_number; ?></strong></td>
                        <td><?php echo esc_html($inv->full_name); ?></td>
                        <td><?php echo $inv->invoice_date; ?></td>
                        <td><?php echo number_format($inv->total_amount, 2); ?></td>
                        <td><span class="fin-status-badge status-<?php echo $inv->status; ?>"><?php echo $inv->status; ?></span></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="5" style="text-align:center; padding:20px; color:var(--control-muted);"><?php _e('لا توجد فواتير مؤخراً.', 'control'); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Expense Breakdown -->
    <div class="control-card" style="padding:20px;">
        <h3 style="margin:0 0 20px 0; font-size:1.1rem;"><?php _e('توزيع المصروفات', 'control'); ?></h3>
        <div style="display:flex; flex-direction:column; gap:20px;">
            <?php
            $exp_categories = $wpdb->get_results("SELECT category, SUM(amount) as total FROM {$wpdb->prefix}control_fin_expenses GROUP BY category");
            $cat_labels = array('rent' => __('الإيجار', 'control'), 'equipment' => __('الأجهزة والمعدات', 'control'), 'utilities' => __('المرافق', 'control'), 'misc' => __('متنوع', 'control'));

            $max_total = 0;
            foreach($exp_categories as $c) if($c->total > $max_total) $max_total = $c->total;
            if($total_payroll > $max_total) $max_total = $total_payroll;

            // Manual bar chart
            $categories_to_show = $exp_categories;
            ?>

            <div class="expense-bar-item">
                <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.85rem;">
                    <span><?php _e('رواتب الأخصائيين', 'control'); ?></span>
                    <span style="font-weight:700;"><?php echo number_format($total_payroll, 2); ?></span>
                </div>
                <div style="height:8px; background:#f1f5f9; border-radius:4px; overflow:hidden;">
                    <div style="height:100%; background:#8b5cf6; width:<?php echo ($max_total > 0) ? ($total_payroll / $max_total * 100) : 0; ?>%;"></div>
                </div>
            </div>

            <?php foreach($exp_categories as $ec): ?>
                <div class="expense-bar-item">
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.85rem;">
                        <span><?php echo $cat_labels[$ec->category] ?? $ec->category; ?></span>
                        <span style="font-weight:700;"><?php echo number_format($ec->total, 2); ?></span>
                    </div>
                    <div style="height:8px; background:#f1f5f9; border-radius:4px; overflow:hidden;">
                        <div style="height:100%; background:var(--control-primary); width:<?php echo ($max_total > 0) ? ($ec->total / $max_total * 100) : 0; ?>%;"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
.fin-status-badge { font-size: 0.65rem; padding: 2px 8px; border-radius: 10px; font-weight: 700; text-transform: uppercase; }
.fin-status-badge.status-paid { background: #ecfdf5; color: #059669; }
.fin-status-badge.status-pending { background: #fff7ed; color: #d97706; }
.fin-status-badge.status-partial { background: #eff6ff; color: #2563eb; }
.fin-status-badge.status-overdue { background: #fef2f2; color: #ef4444; }
</style>
