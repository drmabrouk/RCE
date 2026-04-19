<div class="view-section-container">
<?php
global $wpdb;
$patients = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}control_patients ORDER BY created_at DESC" );
$can_manage = Control_Auth::has_permission('pediatric_manage');

$status_labels = array(
    'active'       => __('نشط', 'control'),
    'waiting_list' => __('قائمة الانتظار', 'control'),
    'dropped_out'  => __('منقطع', 'control'),
    'completed'    => __('تم التأهيل', 'control'),
);

$pending_requests = array_filter($patients, function($p) { return $p->intake_status === 'pending'; });
$active_records   = array_filter($patients, function($p) { return $p->intake_status !== 'pending'; });
?>

<div class="control-header-flex" style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h2 style="font-weight:800; font-size:1.6rem; margin:0; color:var(--control-primary);"><?php _e('إدارة سجلات الأطفال', 'control'); ?></h2>
        <p style="color:var(--control-muted); margin:5px 0 0 0;"><?php echo sprintf(__('إجمالي الحالات: %d | طلبات جديدة: %d', 'control'), count($patients), count($pending_requests)); ?></p>
    </div>
    <div style="display:flex; gap:12px;">
        <?php if($can_manage): ?>
            <button id="add-patient-btn" class="control-btn" style="background:var(--control-primary); border:none; padding:12px 25px; font-weight:800;">
                <span class="dashicons dashicons-plus-alt" style="margin-left:8px;"></span><?php _e('إضافة طفل جديد (النظام)', 'control'); ?>
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Pending Intake Queue -->
<?php if($pending_requests): ?>
<div style="margin-bottom:40px;">
    <h3 style="font-weight:800; color:#d97706; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
        <span class="dashicons dashicons-clock" style="font-size:24px; width:24px; height:24px;"></span>
        <?php _e('طلبات الالتحاق الجديدة (قيد المراجعة)', 'control'); ?>
    </h3>
    <div class="control-grid" style="grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">
        <?php foreach($pending_requests as $p): ?>
            <div class="control-card" style="border-right:5px solid #fbbf24; padding:0; overflow:hidden;">
                <div style="padding:20px;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:15px;">
                        <div>
                            <h4 style="margin:0; font-weight:800;"><?php echo esc_html($p->full_name); ?></h4>
                            <small style="color:var(--control-muted);">ID: <?php echo esc_html($p->temp_id ?: '#'.$p->id); ?></small>
                        </div>
                        <span style="background:#fef3c7; color:#92400e; padding:4px 10px; border-radius:10px; font-size:0.7rem; font-weight:800;"><?php _e('انتظار', 'control'); ?></span>
                    </div>
                    <p style="font-size:0.85rem; color:#475569; margin:0 0 15px 0; line-height:1.6; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                        <strong><?php _e('سبب الطلب:', 'control'); ?></strong> <?php echo esc_html($p->intake_reason); ?>
                    </p>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; font-size:0.8rem;">
                        <div><span style="color:var(--control-muted);"><?php _e('تاريخ الطلب:', 'control'); ?></span><br><strong><?php echo date('Y-m-d', strtotime($p->created_at)); ?></strong></div>
                        <div><span style="color:var(--control-muted);"><?php _e('هاتف التواصل:', 'control'); ?></span><br><strong><?php echo esc_html($p->father_phone); ?></strong></div>
                    </div>
                </div>
                <div style="background:#f8fafc; padding:12px 20px; border-top:1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
                    <button class="control-btn process-intake-btn" data-id="<?php echo $p->id; ?>" style="background:var(--control-primary); border:none; padding:6px 20px; font-size:0.85rem;">
                        <?php _e('بدء التقييم (مرحلة 5)', 'control'); ?>
                    </button>
                    <div style="display:flex; gap:10px;">
                        <button class="reject-intake-btn" data-id="<?php echo $p->id; ?>" style="color:#ef4444; background:none; border:none; cursor:pointer;" title="رفض الطلب"><span class="dashicons dashicons-no"></span></button>
                        <button class="delete-patient-btn" data-id="<?php echo $p->id; ?>" style="color:#64748b; background:none; border:none; cursor:pointer;" title="حذف نهائي"><span class="dashicons dashicons-trash"></span></button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Active Records Search & Filter -->
<div class="control-card" style="padding:20px; margin-bottom:25px; border:none; background:#fff; box-shadow:0 4px 15px rgba(0,0,0,0.05);">
    <div style="display:flex; gap:15px; align-items: center; flex-wrap: wrap;">
        <div style="flex:1; position:relative; min-width: 300px;">
            <span class="dashicons dashicons-search" style="position:absolute; right:15px; top:50%; transform:translateY(-50%); color:var(--control-muted);"></span>
            <input type="text" id="patient-search-input" placeholder="<?php _e('ابحث باسم الطفل، رقم الملف، أو هاتف ولي الأمر...', 'control'); ?>" style="padding:12px 45px 12px 15px; border-radius:12px; border:1.5px solid #eee; width:100%;">
        </div>
        <select id="patient-status-filter" style="padding:12px; border-radius:12px; border:1.5px solid #eee; min-width:180px;">
            <option value=""><?php _e('كل الحالات النشطة', 'control'); ?></option>
            <?php foreach($status_labels as $val => $label): ?>
                <option value="<?php echo $val; ?>"><?php echo $label; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<!-- Active Files Grid -->
<div id="patients-grid" class="control-grid" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
    <?php if($active_records): foreach($active_records as $p): ?>
        <div class="control-card patient-card" data-status="<?php echo esc_attr($p->case_status); ?>" data-search="<?php echo esc_attr(strtolower($p->full_name . ' ' . $p->permanent_id . ' ' . $p->father_phone)); ?>" style="padding:0; overflow:hidden; transition:0.3s; border:1px solid #f1f5f9;">
            <div style="padding:20px;">
                <div style="display:flex; gap:15px; align-items:center; margin-bottom:15px;">
                    <div style="width:65px; height:65px; background:var(--control-bg); border-radius:15px; overflow:hidden; border:2px solid #fff; box-shadow:0 5px 15px rgba(0,0,0,0.05); flex-shrink:0;">
                        <?php if($p->profile_photo): ?>
                            <img src="<?php echo esc_url($p->profile_photo); ?>" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#f1f5f9; color:#94a3b8;">
                                <span class="dashicons dashicons-admin-users" style="font-size:35px; width:35px; height:35px;"></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <h3 style="margin:0 0 5px 0; font-size:1.05rem; font-weight:800; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?php echo esc_html($p->full_name); ?></h3>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <span class="patient-status-badge status-<?php echo esc_attr($p->case_status); ?>" style="font-size:0.65rem; padding:3px 10px; border-radius:8px; font-weight:800;">
                                <?php echo $status_labels[$p->case_status] ?? $p->case_status; ?>
                            </span>
                            <small style="color:var(--control-muted); font-weight:600;">ID: <?php echo esc_html($p->permanent_id ?: '#'.$p->id); ?></small>
                        </div>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; font-size:0.8rem; background:#f8fafc; padding:12px; border-radius:12px;">
                    <div><span style="color:var(--control-muted); font-size:0.7rem;"><?php _e('تاريخ الميلاد', 'control'); ?></span><br><strong><?php echo $p->dob ?: '---'; ?></strong></div>
                    <div><span style="color:var(--control-muted); font-size:0.7rem;"><?php _e('درجة الأولوية', 'control'); ?></span><br><strong style="color:var(--control-primary);"><?php echo strtoupper($p->priority_level ?: 'normal'); ?></strong></div>
                </div>
            </div>

            <div style="background:#fff; padding:15px 20px; border-top:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center;">
                <a href="<?php echo add_query_arg(array('control_view' => 'patient_view', 'id' => $p->id)); ?>" class="control-btn" style="padding:7px 20px; font-size:0.85rem; background:#fff; color:var(--control-primary) !important; border:1.5px solid var(--control-primary); font-weight:800;">
                    <?php _e('الملف السريري', 'control'); ?>
                </a>
                <?php if($can_manage): ?>
                    <button class="delete-patient-btn" data-id="<?php echo $p->id; ?>" style="background:none; border:none; color:#cbd5e1; cursor:pointer; transition:0.2s;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#cbd5e1'">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; else: ?>
        <div style="grid-column: 1 / -1; text-align:center; padding:80px 40px; background:#fff; border-radius:20px; border:2px dashed #e2e8f0;">
            <div style="width:100px; height:100px; background:#f8fafc; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 25px; color:#cbd5e1;">
                <span class="dashicons dashicons-groups" style="font-size:50px; width:50px; height:50px;"></span>
            </div>
            <p style="color:var(--control-muted); font-size:1.2rem; font-weight:600;"><?php _e('لا توجد ملفات نشطة مسجلة حالياً.', 'control'); ?></p>
        </div>
    <?php endif; ?>
</div>

<div id="pediatric-toast" style="display:none; position:fixed; bottom:30px; left:50%; transform:translateX(-50%); background:#1e293b; color:#fff; padding:12px 30px; border-radius:50px; z-index:100000; box-shadow:0 10px 30px rgba(0,0,0,0.2); font-weight:700;"></div>

<!-- Add Patient Modal Placeholder -->
<?php include CONTROL_PATH . 'templates/patient-forms.php'; ?>

<script>
jQuery(document).ready(function($) {
    $('#patient-search-input, #patient-status-filter').on('keyup change', function() {
        const query = $('#patient-search-input').val().toLowerCase();
        const status = $('#patient-status-filter').val();

        $('.patient-card').each(function() {
            const card = $(this);
            const searchVal = card.data('search');
            const cardStatus = card.data('status');

            const matchesSearch = !query || searchVal.includes(query);
            const matchesStatus = !status || cardStatus === status;

            if (matchesSearch && matchesStatus) {
                card.show();
            } else {
                card.hide();
            }
        });
    });

    function showToast(message) {
        $('#pediatric-toast').text(message).fadeIn().delay(3000).fadeOut();
    }

    $(document).on('click', '.delete-patient-btn', function() {
        if(!confirm('<?php _e("هل أنت متأكد من حذف سجل هذا الطفل نهائياً؟ سيتم مسح كافة البيانات السريرية والمالية.", "control"); ?>')) return;
        const id = $(this).data('id');
        const $btn = $(this);
        $btn.prop('disabled', true).css('opacity', '0.5');

        $.post(control_ajax.ajax_url, {
            action: 'control_delete_patient',
            id: id,
            nonce: control_ajax.nonce
        }, function(res) {
            if(res.success) {
                showToast('<?php _e("تم حذف السجل بنجاح.", "control"); ?>');
                setTimeout(() => location.reload(), 1200);
            } else {
                alert(res.data);
                $btn.prop('disabled', false).css('opacity', '1');
            }
        });
    });

    $('#add-patient-btn').on('click', function() {
        openPatientModal();
    });

    $('.process-intake-btn').on('click', function() {
        const id = $(this).data('id');
        // Fetch full record and open modal at step 5
        $.post(control_ajax.ajax_url, {
            action: 'control_get_patient', // This action needs to be verified/added
            id: id,
            nonce: control_ajax.nonce
        }, function(res) {
            if(res.success) {
                openPatientModal(res.data);
                // Force jump to step 5
                jQuery('.wiz-step').hide();
                jQuery('#wiz-step-5').show();
                currentWizStep = 5;
                window.updateWizUI();
            }
        });
    });

    $('.reject-intake-btn').on('click', function() {
        const id = $(this).data('id');
        if(!confirm('<?php _e('هل أنت متأكد من رفض هذا الطلب؟ سيتم نقله للأرشيف.', 'control'); ?>')) return;
        $.post(control_ajax.ajax_url, { action: 'control_update_intake_status', id: id, status: 'rejected', nonce: control_ajax.nonce }, () => location.reload());
    });
});
</script>

<style>
.patient-status-badge.status-active { background: #ecfdf5; color: #059669; }
.patient-status-badge.status-waiting_list { background: #eff6ff; color: #1d4ed8; }
.patient-status-badge.status-dropped_out { background: #fef2f2; color: #ef4444; }
.patient-status-badge.status-completed { background: #f0fdf4; color: #166534; }
.patient-card:hover { transform: translateY(-5px); box-shadow: 0 12px 25px rgba(0,0,0,0.1); border-color: var(--control-primary); }
</style>
</div>
