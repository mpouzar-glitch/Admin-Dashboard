# Admin-Dashboard

A centralized administration dashboard for managing various administrative tools through a customizable web interface. This PHP-based solution provides a secure, hierarchical navigation system for organizing links to different administrative applications and services.

## Features

### Core Functionality
- **Customizable Dashboard**: Create hierarchical pages with custom buttons linking to external tools or internal subpages
- **Drag-and-Drop Interface**: Reorder dashboard elements with intuitive drag-and-drop functionality
- **Multi-level Navigation**: Organize tools into categories with unlimited nesting levels and breadcrumb navigation
- **Visual Customization**: Configure button colors, icons (FontAwesome), sizes, and spacing

### Security
- **Dual Authentication System**: Support for both username/password authentication and IP-based whitelisting
- **IP Range Management**: Define allowed IP ranges using CIDR notation (e.g., 192.168.1.0/24)
- **Session Management**: Secure session handling with login tracking and activity monitoring
- **User Management**: Multi-user support with account activation/deactivation controls

### Administration
- **User Management**: Create, edit, and manage dashboard users with email and password controls
- **IP Whitelist Management**: Add, edit, and toggle IP ranges that bypass authentication
- **Settings Panel**: Customize appearance parameters including button dimensions and spacing
- **Session Logging**: Track user logins, IP addresses, and user agents for security auditing

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (vanilla)
- **Icons**: FontAwesome 6.x
- **Dependencies**: PDO for database operations

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Web server (Apache, Nginx)

### Setup Steps

1. **Clone the repository**
```bash
git clone https://github.com/yourusername/Admin-Dashboard.git
cd Admin-Dashboard
```

2. **Create database**
```bash
mysql -u root -p < database.sql
```

3. **Configure database connection**
Create a `config.php` file with your database credentials:
```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'admin_dashboard');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

4. **Create initial admin user**
```sql
INSERT INTO dashboard_users (username, password_hash, email) 
VALUES ('admin', '$2y$10$YourHashedPasswordHere', 'admin@example.com');
```

To generate a password hash:
```php
<?php echo password_hash('your_password', PASSWORD_DEFAULT); ?>
```

5. **Set file permissions**
```bash
chmod 755 *.php
```

6. **Access the dashboard**
Navigate to `http://yourserver/index.php` in your web browser.

## Database Schema

### Tables
- **dashboard_users**: User accounts with authentication credentials
- **dashboard_pages**: Hierarchical page structure
- **dashboard_buttons**: Configurable buttons/links on pages
- **dashboard_settings**: System-wide appearance settings
- **allowed_ip_ranges**: IP whitelist with CIDR support
- **login_sessions**: User session tracking and audit log

## Usage

### Creating Dashboard Elements

1. **Add a New Page**
   - Navigate to admin panel
   - Click "Create New Subpage"
   - Enter page name and select parent page

2. **Add a Button**
   - Select button type (External URL, Page Link, or Separator)
   - Choose parent page location
   - Configure title, URL, icon, and colors
   - Save and reorder using drag-and-drop

3. **Configure IP Whitelist**
   - Access IP Manager
   - Add IP ranges in CIDR notation (e.g., `192.168.1.0/24` or `10.0.0.1/32`)
   - Toggle active/inactive status as needed

### API Endpoints

The application provides a REST API (`api.php`) for programmatic access:

- `GET /api.php?action=get_buttons&page_id=1` - Retrieve buttons for a page
- `POST /api.php?action=add` - Add new button
- `POST /api.php?action=update` - Update existing button
- `POST /api.php?action=delete` - Remove button
- `POST /api.php?action=update_order` - Reorder buttons
- `GET /api.php?action=get_settings` - Get appearance settings
- `POST /api.php?action=update_settings` - Update settings

## Security Considerations

- All database queries use prepared statements to prevent SQL injection
- Password hashing uses PHP's `password_hash()` with bcrypt
- Session regeneration on successful login
- IP validation using CIDR matching
- Optional IP-based authentication bypass for trusted networks
- HTTPS recommended for production deployment

## File Structure

```
Admin-Dashboard/
├── index.php           # Main dashboard interface
├── admin.php           # Administration panel
├── login.php           # Login interface
├── logout.php          # Session termination
├── api.php             # REST API endpoints
├── auth.php            # Authentication functions
├── user_manager.php    # User management interface
├── ip_manager.php      # IP whitelist management
├── database.sql        # Database schema
└── README.md           # This file
```

## Configuration

### Appearance Settings
Customize the dashboard appearance through the admin panel:
- Minimum button width (default: 200px)
- Minimum button height (default: 150px)
- Icon size (default: 3em)
- Title size (default: 1.1em)
- Button spacing (default: 15px)

### Authentication Modes
1. **IP-based**: Access from whitelisted IPs bypasses login
2. **Username/Password**: Standard authentication for non-whitelisted IPs
3. **Hybrid**: Combination of both methods

## Contributing

Contributions are welcome! Please follow these guidelines:
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/YourFeature`)
3. Commit your changes (`git commit -m 'Add some feature'`)
4. Push to the branch (`git push origin feature/YourFeature`)
5. Open a Pull Request

## License

This project is open source and available under the [MIT License](LICENSE).

## Support

For issues, questions, or contributions, please open an issue on GitHub.

## Changelog

### Version 1.0.0 (2026-01-17)
- Initial release
- Hierarchical dashboard structure
- User authentication and management
- IP whitelist functionality
- Customizable appearance
- REST API support
- Session logging

## Author

Developed for centralized infrastructure administration and tool organization.
