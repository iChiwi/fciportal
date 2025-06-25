# Academic Portal

A comprehensive academic platform designed specifically for FCI students, providing essential resources, administrative tools, and communication features.

## 🚀 Features

### Student Portal

- **Study Material**: Organized course materials by subject
- **Student Lookup**: Search and find student information by code
- **Student Results**: Access academic results and grades
- **Group Links**: Quick access to section and subject-specific WhatsApp groups
- **System Status**: Real-time monitoring of academic services
- **Responsive Design**: Optimized for desktop, tablet, and mobile devices

### Admin Panel

- **File Management**: Upload, organize, and manage academic materials by subject
- **Notification System**: Send announcements to all students with Markdown support
- **User Administration**: Manage admin accounts and permissions
- **Content Management**: Create folders, upload files, and maintain academic resources
- **Real-time Updates**: Instant notification delivery and file organization

## 📋 Prerequisites

### System Requirements

- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP**: 7.4 or higher with extensions:
  - `pdo_mysql`
  - `fileinfo`
  - `session`
  - `json`
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Python**: 3.8+ (for API services)

### Optional Dependencies

- **PM2**: For production Python process management

## 🛠️ Installation

### 1. Clone Repository

```bash
git clone https://github.com/iChiwi/fciportal.git
cd fciportal
```

### 2. Database Setup

#### Create Database and Tables

```sql
-- Create main database
CREATE DATABASE fci CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fci;

-- Admins table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Notifications table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message TEXT NOT NULL,
    admin VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin) REFERENCES admins(username) ON DELETE CASCADE
);

-- Links table
CREATE TABLE links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    url TEXT NOT NULL,
    category ENUM('official', 'section', 'subject') NOT NULL,
    group_code VARCHAR(10) NULL,
    subject_key VARCHAR(20) NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 3. Environment Configuration

#### Create `.env` file in root directory

```env
# MySQL Configuration
MYSQL_HOST=localhost
MYSQL_USER=your_username
MYSQL_PASSWORD=your_password
MYSQL_DATABASE=fci
```

#### Configure Database Connection

Update `/config.php`:

```php
<?php
$host    = 'localhost';           // Host Name
$db      = 'fci';               // Database Name  
$user    = 'your_username';      // Database Username
$pass    = 'your_password';      // Database Password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}
?>
```

### 4. Python Dependencies

#### Install API requirements

```bash
cd /api
pip install -r requirements.txt
```

### 5. Create Admin User

Edit `/admin/createadmin.php`:

```php
<?php
require '../config.php';

$username = 'admin';              // Your admin username
$password = 'secure_password';    // Your admin password
$name = 'Administrator';          // Admin display name

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

$sql = "INSERT INTO admins (username, password, name) VALUES (:username, :password, :name)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'username' => $username, 
    'password' => $hashedPassword,
    'name' => $name
]);

echo "Admin user created successfully!";
?>
```

Run once: `/admin/createadmin.php`

### 6. Configure File Upload Directory

Update `/admin/upload_handler.php`:

```php
$baseDir = "/var/www/domainname/material"; // Set your materials directory path
```

## 🚀 Running the Services

### Development Mode

#### Start Python API Services

```bash
# FCI API
cd /api
python app.py
```

### Production Mode

#### Using PM2 for Python APIs

```bash
# Install PM2 and create service
npm install -g pm2
pm2 start /api/app.py --interpreter python3 --name fci-api
```

#### Web Server Configuration (Apache) | Edit domainname

```apache
<VirtualHost *:80>
    ServerName domainname
    DocumentRoot /var/www/domainname
    
    <Directory /var/www/domainname>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## 🔧 Configuration

### File Permissions

```bash
# Set proper permissions for upload directories
chmod 755 /var/www/domainname/material
chown -R www-data:www-data /var/www/domainname/material

# Set permissions for cache and static files | Edit domainname
chmod -R 755 /var/www/domainname/static
```

### Firewall Configuration

```bash
# Allow HTTP/HTTPS
sudo ufw allow 80
sudo ufw allow 443
```

## 📊 API Endpoints

### FCI API (`/api/`)

- `GET /studentcode.py` - Student code lookup
- `GET /studentresults.py` - Student results retrieval

## 🛡️ Security

### Database Security

- Use strong passwords for database users
- Limit database user privileges
- Enable SSL for database connections in production

### File Security

- Validate all file uploads
- Restrict file types and sizes
- Store uploads outside web root when possible

### Session Security

- Use secure session settings
- Implement CSRF protection
- Regular session cleanup

## 📱 Mobile Support

The platform is fully responsive and supports:

- iOS Safari 12+
- Android Chrome 70+
- Mobile Firefox 68+

## 🎨 Styling

### CSS Build Process

```bash
# Install PostCSS dependencies
npm install

# Build CSS from SCSS
npm run build-css
```

### SCSS Structure

```
static/scss/
├── main.scss
├── components/
│   ├── _admin.scss
│   ├── _buttons.scss
│   ├── _forms.scss
│   └── _links.scss
├── core/
│   ├── _variables.scss
│   ├── _mixins.scss
│   └── _animations.scss
└── layout/
    ├── _body.scss
    ├── _header.scss
    └── _sections.scss
```

## 🔍 Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check database credentials in `config.php`
   - Verify MySQL service is running
   - Check firewall settings

2. **File Upload Errors**
   - Verify directory permissions
   - Check PHP upload limits
   - Ensure disk space availability

3. **API Not Responding**
   - Check Python service status
   - Verify virtual environment activation
   - Check error logs

### Log Locations

- Apache: `/var/log/apache2/error.log`
- PHP: `/var/log/php/error.log`
- Python: Check application logs

## 📦 Dependencies

### PHP Packages

- PDO MySQL driver
- FileInfo extension
- Session support
- JSON support

### Python Packages

```txt
Flask==2.3.3
requests==2.31.0
beautifulsoup4==4.12.2
gunicorn==21.2.0
flask_cors==4.0.0
Werkzeug==2.3.7
```

### PostCSS Packages

```json
{
  "devDependencies": {
    "@fullhuman/postcss-purgecss": "^7.0.2",
    "postcss": "^8.5.3",
    "postcss-cli": "^11.0.1"
  }
}
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 📞 Support

For support and questions:

- Create an issue on GitHub

## 🗂️ Project Structure

```
/
├── config.php
├── home.php
├── package.json
├── postcss.config.mjs
├── admin/               # Admin panel
│   ├── createadmin.php
│   ├── home.php
│   ├── login.php
│   └── upload_handler.php
├── api/                 # Python APIs
│   ├── app.py
│   ├── requirements.txt
│   ├── studentcode.py
│   └── studentresults.py
├── links/               # Group links
│   ├── getLinks.php
│   └── home.php
├── material/            # Study material
├── static/              # SCSS, JS, images
│   ├── js/
│   ├── img/
│   └── scss/
├── status/              # Status pages
│   └── home.php
├── studentcode/         # Student lookup
│   └── home.php
├── studentresults/      # Results system
│   └── home.php
└── study/               # Study resources
    ├── get_files.php
    └── home.php
```

---

Built with ❤️ for FCI students.
