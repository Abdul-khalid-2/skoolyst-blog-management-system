<?php
/** Admin-only user management. $users from UserController@index. */
$roleBadge = ['admin' => 'danger', 'editor' => 'warning', 'author' => 'info', 'reader' => 'default'];
$roles = ['admin' => 'Admin', 'editor' => 'Editor', 'author' => 'Author', 'reader' => 'Reader'];
$currentUserId = (int) auth_user()['id'];
?>
<?php
component('table', [
    'headers' => ['Name', 'Email', 'Role', 'Status', 'Last Login', 'Actions'],
    'rows' => array_map(function ($u) use ($roleBadge, $roles, $currentUserId) {
        $isSelf = (int) $u['id'] === $currentUserId;
        $name = clean($u['name']) . ($isSelf ? ' <span class="stat-label">(You)</span>' : '');

        if ($isSelf) {
            $actions = '<span class="stat-label">Use Edit Profile</span>';
        } else {
            $actions = '<div class="admin-table-actions">';
            $actions .= '<button type="button" class="btn btn-sm btn-outline" data-modal-open="edit-user-' . $u['id'] . '">Edit</button>';
            $actions .= '<form method="post" action="' . url('/dashboard/users/' . $u['id'] . '/delete') . '" data-confirm="Delete this user? Their posts and comments stay, just unassigned.">' . csrf_field() . '<button type="submit" class="btn btn-sm btn-danger">Delete</button></form>';
            $actions .= '</div>';
        }

        return [
            $name,
            clean($u['email']),
            '<span class="badge badge-' . $roleBadge[$u['role']] . '">' . clean($roles[$u['role']] ?? $u['role']) . '</span>',
            (int) $u['active'] === 1
                ? '<span class="badge badge-success">Active</span>'
                : '<span class="badge badge-danger">Inactive</span>',
            $u['last_login_at'] ? format_date($u['last_login_at']) : '—',
            $actions,
        ];
    }, $users),
    'emptyMessage' => 'No users yet.',
]);

foreach ($users as $u) {
    if ((int) $u['id'] === $currentUserId) continue;
    ob_start();
    ?>
    <form method="post" action="<?= url('/dashboard/users/' . $u['id']) ?>">
      <?= csrf_field() ?>
      <?php component('input', [
          'type' => 'select', 'name' => 'role', 'label' => 'Role',
          'value' => $u['role'],
          'options' => $roles,
          'required' => true,
      ]); ?>
      <div class="form-group">
        <label class="admin-checkbox-label">
          <input type="checkbox" name="active" value="1"<?= (int) $u['active'] === 1 ? ' checked' : '' ?>>
          Active — unchecking blocks this account from logging in
        </label>
      </div>
      <?php component('button', ['label' => 'Save Changes', 'type' => 'submit']); ?>
    </form>
    <?php
    $modalBody = ob_get_clean();
    component('modal', ['id' => 'edit-user-' . $u['id'], 'title' => 'Edit ' . $u['name'], 'body' => $modalBody]);
}
?>
