# Iglesia — Backend
Cambios:
- Contraseñas **sin hash** (solo dev), login por comparación directa.
- Código de miembro **automático** (MAX(code)+1).
- Sincronización de **bautismos** (formularios y aprobaciones).
- **CSS por página** y `navbar.css` separado. Cada página llama a su CSS pasando la ruta a `render_head($title, $styles)`.

## CSS
- Global: `assets/css/global.css`
- Navbar: `assets/css/navbar.css`
- Login: `assets/css/login.css`
- Miembros: `assets/css/members_index.css`, `members_create.css`, `members_edit.css`
- Bautizados: `assets/css/baptisms_index.css`, `baptisms_create.css`, `baptisms_edit.css`
- Solicitudes: `assets/css/requests_index.css`, `requests_view.css`

