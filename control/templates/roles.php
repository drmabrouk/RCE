<div class="view-section-container">
<?php
global $wpdb;
$roles = $wpdb->get_results( "SELECT r.*, (SELECT COUNT(*) FROM {$wpdb->prefix}control_staff WHERE role = r.role_key) as user_count FROM {$wpdb->prefix}control_roles r" );
$available_permissions = Control_Auth::get_permissions_registry();
?>

<div class="control-header-flex" style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h2 style="font-weight:800; font-size:1.4rem; margin:0; color:var(--control-text-dark);"><?php _e('إدارة الأدوار والصلاحيات', 'control'); ?></h2>
    <div style="display:flex; gap:10px;">
        <button id="add-role-btn" class="control-btn" style="background:var(--control-primary); border:none;">
            <span class="dashicons dashicons-plus-alt" style="margin-left:5px;"></span><?php _e('إضافة دور جديد', 'control'); ?>
        </button>
        <button id="add-permission-btn" class="control-btn" style="background:#8b5cf6; border:none;">
            <span class="dashicons dashicons-shield" style="margin-left:5px;"></span><?php _e('إضافة صلاحية مخصصة', 'control'); ?>
        </button>
    </div>
</div>

<!-- Search Bar -->
<div class="control-card" style="padding:15px; margin-bottom:25px; border:none; background:rgba(0,0,0,0.02);">
    <div style="position:relative;">
        <span class="dashicons dashicons-search" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); color:var(--control-muted);"></span>
        <input type="text" id="role-search-input" placeholder="<?php _e('ابحث عن دور أو صلاحية...', 'control'); ?>" style="padding:10px 40px 10px 12px; width:100%; border-radius:12px;">
    </div>
</div>

<div id="roles-grid" class="control-grid" style="grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 15px;">
    <?php foreach($roles as $role):
        $perms = json_decode($role->permissions, true) ?: array();
        $perm_count = count($perms);
    ?>
        <div class="control-card role-card-simplified" data-role='<?php echo json_encode($role); ?>' data-search="<?php echo esc_attr(strtolower($role->role_name . ' ' . $role->role_key)); ?>">
            <div style="text-align:center; margin-bottom:15px;">
                <div style="width:50px; height:50px; background:var(--control-bg); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 12px; color:var(--control-primary);">
                    <span class="dashicons dashicons-admin-users" style="font-size:24px; width:24px; height:24px;"></span>
                </div>
                <h3 style="margin:0; font-size:1rem; color:var(--control-text-dark);"><?php echo esc_html($role->role_name); ?></h3>
                <code style="font-size:0.65rem; opacity:0.6;"><?php echo esc_html($role->role_key); ?></code>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.75rem; margin-bottom:15px; padding:0 5px;">
                <span style="color:var(--control-muted);"><?php _e('مستخدمين:', 'control'); ?> <strong><?php echo $role->user_count; ?></strong></span>
                <span style="color:var(--control-muted);"><?php _e('صلاحيات:', 'control'); ?> <strong><?php echo $perm_count; ?></strong></span>
            </div>

            <div style="display:flex; gap:8px;">
                <button class="control-btn edit-role-btn" style="flex:1; background:var(--control-bg); color:var(--control-text-dark) !important; border:none; font-size:0.75rem; padding:6px;"><?php _e('تعديل', 'control'); ?></button>
                <button class="control-btn delete-role-btn" data-id="<?php echo $role->id; ?>" style="background:#fef2f2; color:#ef4444 !important; border:none; width:34px; padding:0;"><span class="dashicons dashicons-trash" style="font-size:16px;"></span></button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Add Permission Modal -->
<div id="permission-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10002; align-items:center; justify-content:center; backdrop-filter: blur(4px);">
    <div class="control-card" style="width:100%; max-width:450px; padding:30px;">
        <h3><?php _e('إضافة صلاحية مخصصة جديدة', 'control'); ?></h3>
        <form id="permission-form">
            <div class="control-form-group">
                <input type="text" name="perm_label" required placeholder=" ">
                <label><?php _e('اسم الصلاحية (بالعربية)', 'control'); ?></label>
            </div>
            <div class="control-form-group">
                <input type="text" name="perm_key" required placeholder=" ">
                <label><?php _e('مفتاح الصلاحية (English Key)', 'control'); ?></label>
            </div>
            <div class="control-form-group">
                <input type="text" name="perm_category" placeholder=" ">
                <label><?php _e('التصنيف (اختياري)', 'control'); ?></label>
            </div>
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="control-btn" style="flex:1; background:var(--control-primary); border:none;"><?php _e('حفظ الصلاحية', 'control'); ?></button>
                <button type="button" onclick="jQuery('#permission-modal').hide()" class="control-btn" style="flex:1; background:var(--control-bg); color:var(--control-text-dark) !important; border:none;"><?php _e('إلغاء', 'control'); ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Role Modal -->
<div id="role-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10001; align-items:center; justify-content:center; backdrop-filter: blur(4px);">
    <div class="control-card" style="width:100%; max-width:650px; padding:0; border-radius:24px; overflow:hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);">
        <div style="background:var(--control-primary); color:#fff; padding:20px 30px; display:flex; justify-content:space-between; align-items:center;">
            <h3 id="role-modal-title" style="color:#fff; margin:0; font-size:1.1rem;"><?php _e('إعدادات الدور', 'control'); ?></h3>
            <button onclick="jQuery('#role-modal').hide()" style="background:none; border:none; color:#fff; cursor:pointer;"><span class="dashicons dashicons-no-alt"></span></button>
        </div>

        <form id="role-form" style="padding:30px;">
            <input type="hidden" name="id" id="role-db-id">
            <div class="control-grid" style="grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:25px;">
                <div class="control-form-group">
                    <input type="text" name="role_name" id="role-name-input" required placeholder=" ">
                    <label><?php _e('اسم الدور', 'control'); ?></label>
                </div>
                <div class="control-form-group">
                    <input type="text" name="role_key" id="role-key-input" required placeholder=" ">
                    <label><?php _e('مفتاح الدور (Unique ID)', 'control'); ?></label>
                </div>
            </div>

            <h4 style="margin:0 0 15px 0; font-size:0.9rem; font-weight:800; color:var(--control-primary);"><?php _e('تحديد الصلاحيات المتاحة', 'control'); ?></h4>
            <div style="max-height:40vh; overflow-y:auto; padding-right:5px; background:#f8fafc; border-radius:15px; padding:15px; border:1px solid #e2e8f0;">
                <?php
                $categories = array();
                foreach($available_permissions as $key => $p) {
                    $categories[$p['category']][$key] = $p['label'];
                }
                foreach($categories as $cat => $perms): ?>
                    <div style="margin-bottom:20px;">
                        <div style="font-weight:800; font-size:0.7rem; color:var(--control-accent); text-transform:uppercase; margin-bottom:10px;"><?php echo $cat; ?></div>
                        <div class="control-grid" style="grid-template-columns: 1fr 1fr; gap:10px;">
                            <?php foreach($perms as $k => $l): ?>
                                <label style="display:flex; align-items:center; gap:8px; background:#fff; padding:8px 12px; border-radius:10px; cursor:pointer; font-size:0.8rem; border:1px solid #e2e8f0;">
                                    <input type="checkbox" name="permissions[<?php echo $k; ?>]" value="1" class="perm-checkbox">
                                    <?php echo $l; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="margin-top:30px; display:flex; gap:15px;">
                <button type="submit" class="control-btn" style="flex:2; background:var(--control-primary); border:none; font-weight:800;"><?php _e('حفظ واعتماد', 'control'); ?></button>
                <button type="button" onclick="jQuery('#role-modal').hide()" class="control-btn" style="flex:1; background:var(--control-bg); color:var(--control-text-dark) !important; border:none;"><?php _e('إلغاء', 'control'); ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Role Delete Confirmation Modal -->
<div id="role-delete-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:100002; align-items:center; justify-content:center; backdrop-filter: blur(8px); direction: rtl;">
    <div class="control-card" style="width:100%; max-width:450px; padding:35px; text-align:center; border-radius:24px;">
        <h3 style="margin-bottom:15px;"><?php _e('حذف الدور', 'control'); ?></h3>
        <p style="color:var(--control-muted); font-size:0.9rem; margin-bottom:25px;"><?php _e('يرجى اختيار دور بديل للمستخدمين الحاليين.', 'control'); ?></p>
        <div class="control-form-group" style="text-align:right; margin-bottom:30px;">
            <select id="replacement-role-select">
                <?php foreach($roles as $r): ?>
                    <option value="<?php echo $r->role_key; ?>"><?php echo $r->role_name; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:flex; gap:15px;">
            <button id="confirm-role-delete-btn" class="control-btn" style="flex:1; background:#ef4444; border:none;"><?php _e('تأكيد الحذف', 'control'); ?></button>
            <button type="button" onclick="jQuery('#role-delete-modal').hide()" class="control-btn" style="flex:1; background:var(--control-bg); color:var(--control-text-dark) !important; border:none;"><?php _e('إلغاء', 'control'); ?></button>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#role-search-input').on('keyup', function() {
        const query = $(this).val().toLowerCase();
        $('.role-card-simplified').each(function() {
            const searchVal = $(this).data('search');
            $(this).toggle(searchVal.includes(query));
        });
    });

    $('#add-role-btn').on('click', function() {
        $('#role-form')[0].reset();
        $('#role-db-id').val('');
        $('#role-key-input').prop('readonly', false);
        $('#role-modal-title').text('<?php _e("إضافة دور جديد", "control"); ?>');
        $('#role-modal').css('display', 'flex');
    });

    $(document).on('click', '.edit-role-btn', function() {
        const r = $(this).closest('.role-card-simplified').data('role');
        $('#role-db-id').val(r.id);
        $('#role-name-input').val(r.role_name);
        $('#role-key-input').val(r.role_key).prop('readonly', r.is_system == 1);
        $('#role-form .perm-checkbox').prop('checked', false);
        const perms = JSON.parse(r.permissions);
        if (perms) {
            for (const [key, val] of Object.entries(perms)) {
                if (val) $(`.perm-checkbox[name="permissions[${key}]"]`).prop('checked', true);
            }
        }
        $('#role-modal-title').text('<?php _e("تعديل الدور", "control"); ?>');
        $('#role-modal').css('display', 'flex');
    });

    $('#role-form').on('submit', function(e) {
        e.preventDefault();
        $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_save_role&nonce=' + control_ajax.nonce, () => location.reload());
    });

    $('#add-permission-btn').on('click', function() { $('#permission-modal').css('display', 'flex'); });

    $('#permission-form').on('submit', function(e) {
        e.preventDefault();
        $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_add_custom_permission&nonce=' + control_ajax.nonce, () => location.reload());
    });

    let roleToDelete = null;
    $(document).on('click', '.delete-role-btn', function() {
        const role = $(this).closest('.role-card-simplified').data('role');
        if (role.is_system == 1) return alert('<?php _e("لا يمكن حذف دور أساسي في النظام", "control"); ?>');
        roleToDelete = role.id;
        $('#replacement-role-select option').show();
        $(`#replacement-role-select option[value="${role.role_key}"]`).hide();
        $('#role-delete-modal').css('display', 'flex');
    });

    $('#confirm-role-delete-btn').on('click', function() {
        $.post(control_ajax.ajax_url, {
            action: 'control_delete_role',
            id: roleToDelete,
            replacement_role_key: $('#replacement-role-select').val(),
            nonce: control_ajax.nonce
        }, () => location.reload());
    });
});
</script>

<style>
.role-card-simplified { padding:20px; transition:0.3s; border:1px solid var(--control-border); }
.role-card-simplified:hover { border-color:var(--control-accent); transform:translateY(-5px); box-shadow:0 10px 20px rgba(0,0,0,0.05); }
</style>
</div>
