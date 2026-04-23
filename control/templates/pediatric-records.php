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
            <button class="control-btn" onclick="openPatientModal()" style="background:var(--control-accent); color: var(--control-primary) !important; border:none; padding:12px 25px; font-weight:800;">
                <span class="dashicons dashicons-plus-alt" style="margin-left:8px;"></span><?php _e('تسجيل طفل جديد', 'control'); ?>
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
                    <a href="<?php echo add_query_arg(array('resume_id' => $p->id), get_permalink(get_page_by_path('kiosk-registration'))); ?>" class="control-btn" style="background:var(--control-primary); border:none; padding:6px 20px; font-size:0.85rem;">
                        <?php _e('إكمال ملف الطفل', 'control'); ?>
                    </a>
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
<div class="control-card" style="padding:15px 20px; margin-bottom:25px; border:none; background:#fff; box-shadow:0 4px 15px rgba(0,0,0,0.05);">
    <div style="display:flex; gap:12px; align-items: center; flex-wrap: nowrap; overflow-x: auto; padding-bottom: 5px;">
        <div style="flex:2; position:relative; min-width: 250px;">
            <span class="dashicons dashicons-search" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); color:var(--control-muted); font-size: 18px;"></span>
            <input type="text" id="patient-search-input" placeholder="<?php _e('بحث شامل (الاسم، الهاتف، الرقم)...', 'control'); ?>" style="padding:10px 40px 10px 12px; border-radius:10px; border:1.5px solid #f1f5f9; width:100%; font-size: 0.9rem;">
        </div>
        <select id="patient-status-filter" style="flex:1; padding:10px; border-radius:10px; border:1.5px solid #f1f5f9; min-width:140px; font-size: 0.85rem; font-weight: 600;">
            <option value=""><?php _e('كل الحالات', 'control'); ?></option>
            <?php foreach($status_labels as $val => $label): ?>
                <option value="<?php echo $val; ?>"><?php echo $label; ?></option>
            <?php endforeach; ?>
        </select>
        <select id="patient-diag-filter" style="flex:1; padding:10px; border-radius:10px; border:1.5px solid #f1f5f9; min-width:140px; font-size: 0.85rem; font-weight: 600;">
            <option value=""><?php _e('كل التشخيصات', 'control'); ?></option>
            <option value="autism">ASD</option>
            <option value="adhd">ADHD</option>
            <option value="speech">Speech Delay</option>
            <option value="cp">Cerebral Palsy</option>
        </select>
        <select id="patient-priority-filter" style="flex:1; padding:10px; border-radius:10px; border:1.5px solid #f1f5f9; min-width:140px; font-size: 0.85rem; font-weight: 600;">
            <option value=""><?php _e('كل الأولويات', 'control'); ?></option>
            <option value="normal">Normal</option>
            <option value="urgent">Urgent</option>
            <option value="critical">Critical</option>
        </select>
    </div>
</div>

<!-- Active Files Grid -->
<div id="patients-grid" class="control-grid" style="grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
    <?php if($active_records): foreach($active_records as $p):
        $dob = new DateTime($p->dob);
        $age = $dob->diff(new DateTime())->y;
        $nat = $p->nationality ?: 'SA';
    ?>
        <div class="control-card patient-card"
             data-status="<?php echo esc_attr($p->case_status); ?>"
             data-diag="<?php echo esc_attr(strtolower($p->initial_diagnosis)); ?>"
             data-priority="<?php echo esc_attr($p->priority_level ?: 'normal'); ?>"
             data-search="<?php echo esc_attr(strtolower($p->full_name . ' ' . $p->permanent_id . ' ' . $p->father_phone . ' ' . $p->initial_diagnosis)); ?>"
             style="padding:0; overflow:hidden; transition:0.3s; border:1px solid #f1f5f9; border-radius: 18px;">

            <div style="padding:20px;">
                <div style="display:flex; gap:15px; align-items:center; margin-bottom:18px;">
                    <div style="width:70px; height:70px; background:var(--control-bg); border-radius:18px; overflow:hidden; border:2px solid #fff; box-shadow:0 8px 20px rgba(0,0,0,0.06); flex-shrink:0;">
                        <?php if($p->profile_photo): ?>
                            <img src="<?php echo esc_url($p->profile_photo); ?>" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#f8fafc; color:#cbd5e1;">
                                <span class="dashicons dashicons-admin-users" style="font-size:40px; width:40px; height:40px;"></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <h3 style="margin:0 0 4px 0; font-size:1.1rem; font-weight:800; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color: var(--control-primary);"><?php echo esc_html($p->full_name); ?></h3>
                        <div style="display:flex; gap:6px; align-items:center; flex-wrap: wrap;">
                            <span class="patient-status-badge status-<?php echo esc_attr($p->case_status); ?>" style="font-size:0.65rem; padding:2px 8px; border-radius:6px; font-weight:800;">
                                <?php echo $status_labels[$p->case_status] ?? $p->case_status; ?>
                            </span>
                            <span style="background:#f1f5f9; color:#64748b; padding:2px 8px; border-radius:6px; font-size:0.65rem; font-weight:700;">
                                <?php echo $age; ?> <?php _e('سنة', 'control'); ?>
                            </span>
                            <span style="background:var(--control-accent-soft); color:var(--control-accent); padding:2px 8px; border-radius:6px; font-size:0.65rem; font-weight:800; text-transform:uppercase;">
                                <?php echo esc_html($p->preferred_lang ?: 'ar'); ?>
                            </span>
                            <span title="Nationality" style="font-size: 1.1rem;"><?php
                                $flags = array('SA'=>'🇸🇦','AE'=>'🇦🇪','EG'=>'🇪🇬','KW'=>'🇰🇼','QA'=>'🇶🇦','BH'=>'🇧🇭','OM'=>'🇴🇲','JO'=>'🇯🇴');
                                echo $flags[$nat] ?? '🏳️';
                            ?></span>
                        </div>
                    </div>
                </div>

                <div style="background:#f8fafc; padding:12px; border-radius:12px; margin-bottom: 5px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px; border-bottom: 1px dashed #e2e8f0; padding-bottom: 8px;">
                        <span style="color:var(--control-muted); font-size:0.75rem; font-weight:600;"><?php _e('التشخيص:', 'control'); ?></span>
                        <strong style="font-size: 0.8rem; color: var(--control-primary);"><?php echo esc_html($p->initial_diagnosis) ?: __('غير محدد', 'control'); ?></strong>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:var(--control-muted); font-size:0.75rem; font-weight:600;"><?php _e('رقم الملف:', 'control'); ?></span>
                        <strong style="font-size: 0.8rem;">#<?php echo esc_html($p->permanent_id ?: $p->id); ?></strong>
                    </div>
                </div>
            </div>

            <div style="background:#fff; padding:12px 20px; border-top:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; gap: 8px;">
                <a href="<?php echo add_query_arg(array('control_view' => 'patient_view', 'id' => $p->id)); ?>" class="control-btn" style="flex: 1; padding:6px 12px; font-size:0.75rem; background:#f8fafc; color:var(--control-primary) !important; border:1px solid #e2e8f0; font-weight:700; border-radius:10px; text-align:center;">
                    <?php _e('عرض الملف', 'control'); ?>
                </a>
                <?php if($can_manage && (empty($p->permanent_id) || $p->case_status === 'waiting_list')): ?>
                    <a href="<?php echo add_query_arg(array('resume_id' => $p->id), get_permalink(get_page_by_path('kiosk-registration'))); ?>" class="control-btn" style="flex: 1; padding:6px 12px; font-size:0.75rem; background:var(--control-primary); color:#fff !important; border:none; font-weight:700; border-radius:10px; text-align:center;">
                        <?php _e('إكمال الملف', 'control'); ?>
                    </a>
                <?php endif; ?>
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
    $('#patient-search-input, #patient-status-filter, #patient-diag-filter, #patient-priority-filter').on('keyup change', function() {
        const query = $('#patient-search-input').val().toLowerCase();
        const status = $('#patient-status-filter').val();
        const diag = $('#patient-diag-filter').val().toLowerCase();
        const priority = $('#patient-priority-filter').val();

        $('.patient-card').each(function() {
            const card = $(this);
            const searchVal = card.data('search');
            const cardStatus = card.data('status');
            const cardDiag = card.data('diag');
            const cardPriority = card.data('priority');

            const matchesSearch = !query || searchVal.includes(query);
            const matchesStatus = !status || cardStatus === status;
            const matchesDiag = !diag || cardDiag.includes(diag);
            const matchesPriority = !priority || cardPriority === priority;

            if (matchesSearch && matchesStatus && matchesDiag && matchesPriority) {
                card.fadeIn(200);
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
