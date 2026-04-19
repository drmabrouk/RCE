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
?>

<div class="control-header-flex" style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="font-weight:800; font-size:1.3rem; margin:0; color:var(--control-text-dark);"><?php _e('سجلات الأطفال (إدارة الحالات)', 'control'); ?></h2>
    <div style="display:flex; gap:10px;">
        <?php if($can_manage): ?>
            <button id="add-patient-btn" class="control-btn" style="background:var(--control-primary); border:none;">
                <span class="dashicons dashicons-plus-alt" style="margin-left:5px;"></span><?php _e('تسجيل طفل جديد', 'control'); ?>
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="control-card" style="padding:15px; margin-bottom:20px; border:none; background:rgba(0,0,0,0.02);">
    <div style="display:flex; gap:12px; align-items: center; flex-wrap: wrap;">
        <div style="flex:1; position:relative; min-width: 250px;">
            <span class="dashicons dashicons-search" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); color:var(--control-muted);"></span>
            <input type="text" id="patient-search-input" placeholder="<?php _e('ابحث باسم الطفل أو هاتف ولي الأمر...', 'control'); ?>" style="padding:10px 40px 10px 12px;">
        </div>

        <select id="patient-status-filter" style="width:180px; padding:10px;">
            <option value=""><?php _e('كل الحالات', 'control'); ?></option>
            <?php foreach($status_labels as $val => $label): ?>
                <option value="<?php echo $val; ?>"><?php echo $label; ?></option>
            <?php endforeach; ?>
            <option value="pending"><?php _e('طلبات الانتظار (Kiosk)', 'control'); ?></option>
        </select>
    </div>
</div>

<div id="patients-grid" class="control-grid" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
    <?php if($patients): foreach($patients as $p): ?>
        <div class="control-card patient-card" data-status="<?php echo ($p->intake_status === 'pending') ? 'pending' : esc_attr($p->case_status); ?>" data-search="<?php echo esc_attr(strtolower($p->full_name . ' ' . $p->father_phone . ' ' . $p->mother_phone)); ?>" style="padding:0; overflow:hidden;">
            <div style="padding:20px;">
                <div style="display:flex; gap:15px; align-items:center;">
                    <div style="width:60px; height:60px; background:var(--control-bg); border-radius:12px; overflow:hidden; border:1px solid var(--control-border); flex-shrink:0;">
                        <?php if($p->profile_photo): ?>
                            <img src="<?php echo esc_url($p->profile_photo); ?>" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#f1f5f9; color:#94a3b8;">
                                <span class="dashicons dashicons-admin-users" style="font-size:30px; width:30px; height:30px;"></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <h3 style="margin:0 0 5px 0; font-size:1rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?php echo esc_html($p->full_name); ?></h3>
                        <div class="patient-status-badge status-<?php echo esc_attr($p->case_status); ?>" style="display:inline-block; font-size:0.7rem; padding:2px 8px; border-radius:10px; font-weight:700;">
                            <?php echo $status_labels[$p->case_status] ?? $p->case_status; ?>
                        </div>
                    </div>
                </div>

                <div style="margin-top:15px; border-top:1px solid #f1f5f9; padding-top:15px; display:grid; grid-template-columns: 1fr 1fr; gap:10px; font-size:0.8rem;">
                    <div>
                        <span style="color:var(--control-muted); display:block; font-size:0.7rem;"><?php _e('تاريخ الميلاد', 'control'); ?></span>
                        <span style="font-weight:600;"><?php echo $p->dob ?: '---'; ?></span>
                    </div>
                    <div>
                        <span style="color:var(--control-muted); display:block; font-size:0.7rem;"><?php _e('الجنس', 'control'); ?></span>
                        <span style="font-weight:600;"><?php echo $p->gender === 'male' ? __('ذكر', 'control') : __('أنثى', 'control'); ?></span>
                    </div>
                </div>
            </div>

            <div style="background:#f8fafc; padding:12px 20px; border-top:1px solid var(--control-border); display:flex; justify-content:space-between; align-items:center;">
                <a href="<?php echo add_query_arg(array('control_view' => 'patient_view', 'id' => $p->id)); ?>" class="control-btn" style="padding:6px 15px; font-size:0.8rem; background:#fff; color:var(--control-primary) !important; border:1px solid var(--control-border);">
                    <?php _e('الملف الكامل', 'control'); ?>
                </a>
                <?php if($can_manage): ?>
                    <div style="display:flex; gap:10px; align-items:center;">
                        <?php if($p->intake_status === 'pending'): ?>
                            <button class="approve-intake-btn" data-id="<?php echo $p->id; ?>" title="<?php _e('قبول الحالة', 'control'); ?>" style="background:none; border:none; color:#10b981; cursor:pointer;"><span class="dashicons dashicons-yes"></span></button>
                            <button class="reject-intake-btn" data-id="<?php echo $p->id; ?>" title="<?php _e('رفض الحالة', 'control'); ?>" style="background:none; border:none; color:#f59e0b; cursor:pointer;"><span class="dashicons dashicons-no"></span></button>
                        <?php endif; ?>
                        <button class="delete-patient-btn" data-id="<?php echo $p->id; ?>" style="background:none; border:none; color:#ef4444; cursor:pointer;">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; else: ?>
        <div style="grid-column: 1 / -1; text-align:center; padding:50px; background:#fff; border-radius:15px; border:1px dashed var(--control-border);">
            <p style="color:var(--control-muted);"><?php _e('لا يوجد أطفال مسجلين حالياً.', 'control'); ?></p>
        </div>
    <?php endif; ?>
</div>

<div id="pediatric-toast" style="display:none; position:fixed; bottom:30px; left:50%; transform:translateX(-50%); background:#1e293b; color:#fff; padding:12px 30px; border-radius:50px; z-index:100000; box-shadow:0 10px 30px rgba(0,0,0,0.2); font-weight:700;"></div>

<!-- Add Patient Modal Placeholder - Implementation in patient-forms.php -->
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
        if(!confirm('<?php _e("هل أنت متأكد من حذف ملف هذا الطفل نهائياً؟ سيتم حذف كافة السجلات والتقارير المرتبطة به.", "control"); ?>')) return;
        const id = $(this).data('id');
        const $btn = $(this);
        $btn.prop('disabled', true).css('opacity', '0.5');

        $.post(control_ajax.ajax_url, {
            action: 'control_delete_patient',
            id: id,
            nonce: control_ajax.nonce
        }, function(res) {
            if(res.success) {
                showToast('<?php _e("تم حذف ملف الطفل بنجاح.", "control"); ?>');
                setTimeout(() => location.reload(), 1500);
            } else {
                alert(res.data);
                $btn.prop('disabled', false).css('opacity', '1');
            }
        });
    });

    $('#add-patient-btn').on('click', function() {
        openPatientModal();
    });

    $('.approve-intake-btn').on('click', function() {
        const id = $(this).data('id');
        $.post(control_ajax.ajax_url, { action: 'control_update_intake_status', id: id, status: 'approved', nonce: control_ajax.nonce }, () => location.reload());
    });

    $('.reject-intake-btn').on('click', function() {
        const id = $(this).data('id');
        if(!confirm('<?php _e('هل أنت متأكد من رفض هذا الطلب؟', 'control'); ?>')) return;
        $.post(control_ajax.ajax_url, { action: 'control_update_intake_status', id: id, status: 'rejected', nonce: control_ajax.nonce }, () => location.reload());
    });
});
</script>

<style>
.patient-status-badge.status-active { background: #ecfdf5; color: #059669; }
.patient-status-badge.status-waiting_list { background: #fff7ed; color: #d97706; }
.patient-status-badge.status-dropped_out { background: #fef2f2; color: #ef4444; }
.patient-status-badge.status-completed { background: #eff6ff; color: #2563eb; }
</style>
