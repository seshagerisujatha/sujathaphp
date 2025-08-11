
# Leave Management System - Frontend-Backend Architecture

A modern leave management system built with Angular frontend and PHP backend, featuring a RESTful API architecture.

## 🏗️ Architecture

- **Frontend**: Angular (Latest) with TypeScript
- **Backend**: PHP 8+ with PDO for database operations
- **Database**: MySQL 8.0+
- **API**: RESTful endpoints with JSON responses
- **Security**: Prepared statements, CORS enabled, password hashing

## 📋 Prerequisites

- XAMPP (Apache, MySQL, PHP 8.0+)
- Node.js (v16+)
- Angular CLI
- VS Code (recommended)

## 🚀 Setup Instructions

### 1. Install XAMPP

1. Download and install [XAMPP](https://www.apachefriends.org/download.html)
2. Start Apache and MySQL services from XAMPP Control Panel
3. Verify installation by visiting `http://localhost` in your browser

### 2. Database Setup

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create a new database named `leavedb`
3. Import the database schema:
   - Click on `leavedb` database
   - Go to "Import" tab
   - Select `database/schema.sql` file
   - Click "Go" to import

### 3. PHP Backend Setup

1. Copy the project to XAMPP's htdocs directory:
   ```bash
   # On Windows
   C:\xampp\htdocs\leave-management\
   
   # On macOS/Linux
   /Applications/XAMPP/htdocs/leave-management/
   ```

2. Project structure:
   ```
   leave-management/
   ├── api/
   │   ├── employees.php
   │   ├── leaves.php
   │   ├── companies.php
   │   ├── departments.php
   │   └── leave-types.php
   ├── config/
   │   └── database.php
   └── database/
       └── schema.sql
   ```

3. Update database configuration in `config/database.php` if needed:
   ```php
   private $host = "localhost";
   private $database_name = "leavedb";
   private $username = "root";
   private $password = ""; // Usually empty for XAMPP
   ```

4. Test API endpoints:
   - `http://localhost/leave-management/api/employees.php`
   - `http://localhost/leave-management/api/companies.php`

### 4. Angular Frontend Setup

1. Install Angular CLI globally:
   ```bash
   npm install -g @angular/cli
   ```

2. Create new Angular project:
   ```bash
   ng new leave-management-frontend
   cd leave-management-frontend
   ```

3. Install required dependencies:
   ```bash
   npm install @angular/common@latest @angular/forms@latest
   ```

4. Copy the service files to your Angular project:
   ```
   src/app/services/
   ├── api.service.ts
   ├── employee.service.ts
   ├── leave.service.ts
   ├── company.service.ts
   ├── department.service.ts
   └── leave-type.service.ts
   ```

5. Import HttpClientModule in `app.module.ts`:
   ```typescript
   import { HttpClientModule } from '@angular/common/http';
   
   @NgModule({
     imports: [
       // ... other imports
       HttpClientModule
     ],
     // ...
   })
   ```

6. Update API base URL in `api.service.ts` for production:
   ```typescript
   // Development
   private baseUrl = 'http://localhost/leave-management/api';
   
   // Production
   private baseUrl = 'https://yourdomain.com/api';
   ```

7. Start development server:
   ```bash
   ng serve
   ```

8. Access the application: `http://localhost:4200`

## 🔧 Configuration

### API Base URLs

#### Development (Local)
```typescript
// In api.service.ts
private baseUrl = 'http://localhost/leave-management/api';
```

#### Production
```typescript
// In api.service.ts
private baseUrl = 'https://yourdomain.com/api';
```

### Database Configuration

Update `config/database.php` for different environments:

```php
// Development
private $host = "localhost";
private $database_name = "leavedb";
private $username = "root";
private $password = "";

// Production
private $host = "your-production-host";
private $database_name = "your-production-db";
private $username = "your-username";
private $password = "your-secure-password";
```

### CORS Configuration

The PHP backend includes CORS headers for development. Update allowed origins in `config/database.php`:

```php
$allowed_origins = [
    'http://localhost:4200',      // Angular dev server
    'http://127.0.0.1:4200',     // Alternative localhost
    'https://yourdomain.com'      // Production domain
];
```

## 📚 API Documentation

### Base URL
```
http://localhost/leave-management/api
```

### Response Format
All API responses follow this format:
```json
{
  "status": "success|error",
  "message": "Descriptive message",
  "data": { ... },
  "timestamp": "2024-01-20T10:30:00+00:00"
}
```

### Endpoints

#### Employees
- `GET /employees.php` - Get all employees
- `GET /employees.php/{id}` - Get employee by ID
- `POST /employees.php` - Create new employee
- `PUT /employees.php/{id}` - Update employee
- `DELETE /employees.php/{id}` - Delete employee

#### Leaves
- `GET /leaves.php` - Get all leave requests
- `GET /leaves.php/{id}` - Get leave request by ID
- `POST /leaves.php` - Create new leave request
- `PUT /leaves.php/{id}` - Update leave request
- `DELETE /leaves.php/{id}` - Delete leave request

#### Companies
- `GET /companies.php` - Get all companies
- `GET /companies.php/{id}` - Get company by ID
- `POST /companies.php` - Create new company
- `PUT /companies.php/{id}` - Update company
- `DELETE /companies.php/{id}` - Delete company

#### Departments
- `GET /departments.php` - Get all departments
- `GET /departments.php/{id}` - Get department by ID
- `POST /departments.php` - Create new department
- `PUT /departments.php/{id}` - Update department
- `DELETE /departments.php/{id}` - Delete department

#### Leave Types
- `GET /leave-types.php` - Get all leave types
- `GET /leave-types.php/{id}` - Get leave type by ID
- `POST /leave-types.php` - Create new leave type
- `PUT /leave-types.php/{id}` - Update leave type
- `DELETE /leave-types.php/{id}` - Delete leave type

## 🔒 Security Features

- **SQL Injection Protection**: All queries use prepared statements
- **Password Security**: Passwords are hashed using PHP's `password_hash()`
- **CORS Protection**: Configured for specific allowed origins
- **Input Validation**: Server-side validation for all inputs
- **Error Handling**: Proper error responses without sensitive information

## 🧪 Testing

### Test API Endpoints

1. Using curl:
```bash
# Get all employees
curl -X GET http://localhost/leave-management/api/employees.php

# Create new company
curl -X POST http://localhost/leave-management/api/companies.php \
  -H "Content-Type: application/json" \
  -d '{"COMPANYNAME":"Test Company","COMPANYADDRESS":"Test Address","COMPANYCONTACTNO":"123-456-7890"}'
```

2. Using Postman:
   - Import the API endpoints
   - Test all CRUD operations
   - Verify response formats

### Test Angular Services

```typescript
// In your component
constructor(private employeeService: EmployeeService) {}

ngOnInit() {
  this.employeeService.getAllEmployees().subscribe({
    next: (employees) => console.log('Employees:', employees),
    error: (error) => console.error('Error:', error)
  });
}
```

## 📦 Deployment

### Development Deployment on Replit

1. Upload your PHP backend files to Replit
2. Configure the database connection for Replit's MySQL
3. Update CORS settings for Replit's domain
4. Deploy Angular app on Replit or Netlify

### Production Deployment

1. **Backend (PHP)**:
   - Upload to web hosting with PHP 8+ and MySQL
   - Update database configuration
   - Configure SSL certificate
   - Update CORS settings

2. **Frontend (Angular)**:
   - Build for production: `ng build --prod`
   - Deploy to hosting service (Netlify, Vercel, etc.)
   - Update API base URL

## 🐛 Troubleshooting

### Common Issues

1. **CORS Errors**:
   - Check allowed origins in `config/database.php`
   - Ensure preflight requests are handled

2. **Database Connection Failed**:
   - Verify XAMPP MySQL is running
   - Check database credentials
   - Ensure database exists

3. **API 404 Errors**:
   - Verify file paths in htdocs
   - Check URL routing
   - Ensure Apache is running

4. **Angular HTTP Errors**:
   - Check API base URL
   - Verify CORS configuration
   - Check browser console for errors

### Debug Mode

Enable error reporting in PHP for development:
```php
// Add to top of API files for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📄 Sample Data

The database includes sample data:
- 3 Companies
- 8 Departments
- 6 Leave Types
- 5 Employees
- 4 Sample Leave Requests

Default login credentials:
- Username: `jdoe`
- Password: `password123`

## 🤝 Contributing

1. Fork the repository
2. Create feature branch
3. Make changes
4. Test thoroughly
5. Submit pull request

## 📞 Support

For issues and questions:
- Check the troubleshooting section
- Review API documentation
- Check browser console for errors
- Verify database configuration

---

## 📝 Notes

- This system is designed for educational and small business use
- Always use HTTPS in production
- Regularly backup your database
- Keep dependencies updated
- Follow security best practices
