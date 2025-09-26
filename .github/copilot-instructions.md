# Copilot Instructions for web_generator_surat

## Project Overview
This is a PHP-based web application for generating official letters ("surat tugas" and "surat undangan") with user/admin roles. The backend is organized in an MVC-like structure, and the frontend uses HTML, CSS, and JavaScript. The project is designed for use with XAMPP and stores data in a MySQL database.

## Key Components
- `backend/controllers/`: Handles business logic for authentication, letter generation, and user management.
- `backend/models/`: Contains data models for `User` and `Pegawai`.
- `backend/templates/`: Stores PHP templates for letter formats (e.g., `surat_tugas.php`).
- `public/`: Entry point for web access, contains main pages and assets.
- `public/staff/`: Staff dashboard and user-facing features.
- `backend/config/database.php`: Database connection settings.

## Core Patterns & Conventions
- **Routing:** Entry via `public/index.php`, which loads controllers based on request parameters.
- **Template Usage:** Letter templates are PHP files rendered with dynamic data from forms.
- **Role-based Access:**
  - Admin: Manages templates and employee data (CRUD via dashboard).
  - Staff/User: Can generate, preview, and download letters (no template management).
- **Document Generation:** Uses `phpoffice/phpword` (see `vendor/`) to export letters as `.docx`.
- **Real-time Preview:** JavaScript updates letter previews as users fill out forms.
- **Search & Filter:** Users can search/filter their generated letters (see staff dashboard).

## Developer Workflows
- **Run Locally:** Place project in XAMPP `htdocs`, start Apache/MySQL, and access via `http://localhost/web_generator_surat/public/`.
- **Database:** Import `backend/config/generator_surat (1).sql` to set up schema.
- **Dependencies:** Managed via Composer (`composer install` in `backend/`).
- **Debugging:** Use `public/index.php` and controller logs for tracing requests.

## Integration Points
- **PHPWord:** For DOCX export, see usage in controllers and templates.
- **Authentication:** Handled in `AuthController.php` and `UserController.php`.

## Examples
- To add a new letter template: Place a PHP file in `backend/templates/` and update admin dashboard logic.
- To add a new employee: Use the admin dashboard (CRUD in `Pegawai.php` and related controllers).

## Notes
- Follow the existing MVC-like separation for new features.
- Keep user/admin role checks consistent with current controllers.
- Use Composer for any new PHP dependencies.
