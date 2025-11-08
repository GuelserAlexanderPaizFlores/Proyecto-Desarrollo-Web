# Iglesia — Backend (v3 con CSS por página)
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

## Instalación
1. Importa `sql/schema.sql` en MySQL.
2. Copia la carpeta `iglesia` a tu servidor (ajusta `config/config.php` → `base_url`).
3. Abre `http://localhost/iglesia/public/` y entra con **admin / admin123**.
