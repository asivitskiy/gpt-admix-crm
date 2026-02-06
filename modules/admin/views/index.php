<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(240px,1fr));">
  <div class="card">
    <div style="font-weight:700;margin-bottom:6px;">System</div>
    <div class="muted">Placeholder for system info.</div>
  </div>
  <div class="card">
    <div style="font-weight:700;margin-bottom:6px;">Notifications</div>
    <div class="muted">Placeholder for admin announcements.</div>
  </div>
  <div class="card">
    <div style="font-weight:700;margin-bottom:6px;">Help</div>
    <div class="muted">Placeholder for internal instructions.</div>
  </div>
</div>

<?php if (!empty($is_admin)): ?>
  <div class="card" style="margin-top:12px;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
      <div>
        <div style="font-weight:700;">User Management</div>
        <div class="muted">Admin-only tools.</div>
      </div>
      <a class="btn" href="index.php?m=admin&p=users" style="max-width:220px;">Open Users</a>
    </div>
  </div>
<?php endif; ?>
