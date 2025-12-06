# Laravel Backend - Certificate Management System

A well-organized Laravel 11 backend with a modern certificate management system featuring PDF generation using Spatie Laravel PDF.

## Features

- ✅ Fully public certificate management (no authentication required)
- ✅ Modern certificate template design
- ✅ Editable certificate fields (student name, subject, manager name, teacher name, logo)
- ✅ PDF generation and download
- ✅ Embeddable certificate view for Flutter app integration
- ✅ Organized MVC structure for easy feature addition
- ✅ Service layer for business logic separation

## Project Structure

```
laravel-backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/              # API controllers for Flutter app
│   │   │   └── Web/              # Web/MVC controllers
│   │   ├── Requests/             # Form requests
│   │   └── Services/             # Business logic services
│   └── Models/
├── database/
│   └── migrations/
├── resources/
│   └── views/
│       ├── layouts/
│       └── certificates/
└── config/
```

## Installation

1. **Install dependencies:**
   ```bash
   composer install
   ```

2. **Configure environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Set up database:**
   - Update `.env` with your database credentials
   - Run migrations:
     ```bash
     php artisan migrate
     ```

4. **Create storage link:**
   ```bash
   php artisan storage:link
   ```

5. **Install Node.js dependencies (if using Vite):**
   ```bash
   npm install
   ```

## Certificate Fields

- **Student Name** - Name of the certificate recipient
- **Subject** - Course/subject name
- **Manager Name** - Manager's name
- **Teacher Name** - Teacher's name
- **Logo** - Optional logo image (uploaded to storage)
- **Certificate Number** - Unique certificate identifier (auto-generated if not provided)
- **Issue Date** - Date when certificate was issued

## Routes

### Public Routes (No Authentication Required)

- `GET /` - Redirects to certificates index
- `GET /certificates` - List all certificates
- `GET /certificates/create` - Create new certificate form
- `POST /certificates` - Store new certificate
- `GET /certificates/{id}` - View certificate (embeddable)
- `GET /certificates/{id}/edit` - Edit certificate form
- `PUT /certificates/{id}` - Update certificate
- `DELETE /certificates/{id}` - Delete certificate
- `GET /certificates/{id}/download` - Download certificate as PDF

## Usage

### Creating a Certificate

1. Navigate to `/certificates/create`
2. Fill in all required fields
3. Optionally upload a logo
4. Certificate number will be auto-generated if left blank
5. Submit the form

### Viewing a Certificate

- Public view: `/certificates/{id}` - Can be embedded in Flutter app
- Download PDF: `/certificates/{id}/download`

### Editing a Certificate

1. Navigate to `/certificates/{id}/edit`
2. Update any fields
3. Optionally upload a new logo (replaces existing)
4. Submit the form

## PDF Generation

The system uses Spatie Laravel PDF with Browsershot for high-quality PDF generation. The certificate template is optimized for A4 format.

**Note:** Browsershot requires Node.js and Puppeteer. Make sure they are installed on your server.

## Integration with Flutter App

The certificate view at `/certificates/{id}` is designed to be embeddable in your Flutter app. You can:

1. Use a WebView to display the certificate
2. Fetch the certificate data via API (to be implemented)
3. Download the PDF directly from the Flutter app

## Configuration

Certificate settings can be configured in `config/certificates.php`:

- Default logo path
- Logo storage disk and path
- PDF format
- Certificate number prefix and length

## Storage

Uploaded logos are stored in `storage/app/public/certificates/logos/` and are accessible via the public storage link.

## Development

### Running the Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

### Code Structure

- **Controllers**: Handle HTTP requests and responses
- **Services**: Business logic (PDF generation, file handling)
- **Requests**: Form validation
- **Models**: Database models with relationships
- **Views**: Blade templates for rendering

## Future Enhancements

- API endpoints for Flutter app integration
- Multiple certificate templates
- Batch certificate generation
- Email certificate delivery
- Certificate verification system

## License

This project is part of the Almajd Academy multi-features system.
