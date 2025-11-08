<?php
require_once __DIR__ . '/auth.php';
function require_admin() { require_role('ADMIN'); }
function nav_visible_for(string $item): bool {
  $u = current_user(); if (!$u) return false;
  switch ($item) { case 'Miembros': return true; case 'Bautizados': return true; case 'Solicitudes': return $u['role']==='ADMIN'; default: return false; }
}
