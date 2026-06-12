# iZi GP Admin System

An elegant, professional-grade web application for managing NFC-based security guard patrols, incident tracking, and site management.

## Features

### 1. **Dashboard**
- Live statistics: total guards, active patrols today, incidents reported, sites covered
- Recent activity feed showing latest patrol logs
- Quick overview of system status

### 2. **Guard Management**
- Add, edit, and deactivate security guards
- Track guard information: name, badge number, contact info, assigned sites
- Guard status management (active, inactive, on leave)

### 3. **NFC Tags & Checkpoints**
- Register NFC tags with unique UIDs
- Link tags to specific patrol sites and locations
- Manage checkpoint labels and descriptions

### 4. **Patrol Tracking**
- Log NFC scan events from guard patrols
- Track which guard scanned which checkpoint and when
- Filter patrol logs by guard, site, or date
- View complete patrol history

### 5. **Patrol Charts & Analytics**
- Bar charts showing patrol count by guard
- Doughnut charts showing patrol distribution by site
- Time-series line charts showing activity over 7 days
- Visual patrol coverage analysis

### 6. **Incident Management**
- Create and manage security incidents
- Track severity levels: low, medium, high, critical
- Manage incident status: open or resolved
- Assign incidents to guards or sites

### 7. **Sites Management**
- Add and manage patrol sites/yards
- Store site address and description
- Track GPS coordinates for each site
- View associated checkpoints

### 8. **Interactive Map**
- Google Maps integration showing all patrol sites
- NFC checkpoint markers
- Site and checkpoint information popups
- Visual patrol route planning

### 9. **Admin User Management**
- Create and manage admin accounts
- Assign roles: admin or user
- Track user login history
- Enable/disable user accounts

## Project Structure

```
nfc-patrol-admin/
├── public/                 # Web root directory
│   ├── dashboard.php       # Dashboard page
│   ├── guards.php          # Guard management
│   ├── sites.php           # Site management
│   ├── nfc-tags.php        # NFC tag registration
│   ├── patrol-tracking.php # Patrol logs
│   ├── patrol-charts.php   # Analytics charts
│   ├── incidents.php       # Incident management
│   ├── map.php             # Interactive map
│   ├── admin-users.php     # User management
│   └── layout/
│       ├── header.php      # Navigation header
│       └── footer.php      # Footer
├── src/
│   ├── classes/            # PHP model classes
│   │   ├── Database.php    # Database connection
│   │   ├── Guard.php       # Guard model
│   │   ├── Site.php        # Site model
│   │   ├── NfcTag.php      # NFC tag model
│   │   ├── PatrolLog.php   # Patrol log model
│   │   ├── Incident.php    # Incident model
│   │   └── User.php        # User model
│   └── api/                # API endpoints (optional)
├── includes/
│   └── config.php          # Configuration file
├── assets/
│   ├── css/
│   │   └── styles.css      # Global styles
│   ├── js/
│   │   └── main.js         # JavaScript utilities
│   └── images/             # Image assets
├── database/
│   └── schema.sql          # Database schema
└── README.md               # This file
```

## Database Schema

### Tables

1. **users** - Admin user accounts
   - id, username, email, password_hash, role, is_active, last_login, created_at, updated_at

2. **sites** - Patrol sites/yards
   - id, name, address, description, is_active, created_at, updated_at

3. **guards** - Security guards
   - id, name, badge_number, phone, email, assigned_site_ids, status, notes, created_at, updated_at

4. **nfc_tags** - NFC checkpoints
   - id, tag_uid, label, site_id, description, is_active, created_at, updated_at

5. **patrol_logs** - Patrol scan events
   - id, guard_id, nfc_tag_id, site_id, scanned_at, notes, created_at

6. **incidents** - Security incidents
   - id, title, description, severity, status, site_id, guard_id, location, reported_at, resolved_at, created_at, updated_at

## Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache, Nginx, etc.)
- Modern web browser

### Step 1: Database Setup

1. Create a new MySQL database:
```sql
CREATE DATABASE nfc_patrol_admin;
```

2. Import the schema:
```bash
mysql -u root -p nfc_patrol_admin < database/schema.sql
```

### Step 2: Configuration

1. Edit `includes/config.php` with your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'nfc_patrol_admin');
```

### Step 3: Web Server Setup

1. Point your web server to the `public/` directory
2. Ensure the `public/` directory is accessible via HTTP

### Step 4: Access the Application

1. Navigate to `http://localhost/nfc-patrol-admin/public/dashboard.php`
2. Default admin credentials (from schema):
   - Username: `admin`
   - Password: `admin123` (change immediately in production)

## Usage Guide

### Adding a Guard

1. Navigate to **Guards** section
2. Click **Add New Guard**
3. Fill in guard details:
   - Name
   - Badge Number (unique)
   - Phone & Email
   - Assign to sites
   - Set status
4. Click **Save Guard**

### Registering NFC Tags

1. Navigate to **NFC Tags** section
2. Click **Register NFC Tag**
3. Enter NFC tag details:
   - NFC Tag UID (from the physical tag)
   - Checkpoint label
   - Assigned site
   - Description
4. Click **Register Tag**

### Logging Patrol Scans

Patrol scans are typically logged via NFC reader hardware that sends scan events to the system. The system records:
- Guard ID
- NFC Tag ID
- Site ID
- Timestamp
- Optional notes

### Reporting Incidents

1. Navigate to **Incidents** section
2. Click **Report Incident**
3. Fill in incident details:
   - Title
   - Description
   - Severity level
   - Status
   - Associated site/guard
   - Location
4. Click **Save Incident**

### Viewing Analytics

1. Navigate to **Charts** section
2. View patrol coverage by guard and site
3. Analyze activity trends over the last 7 days
4. Export data for reports (optional)

### Managing Admin Users

1. Navigate to **Admin Users** section
2. Create new users or manage existing ones
3. Assign roles: admin or user
4. Track user login history

## Design & Styling

The application features an elegant, professional design with:

- **Color Palette**: Professional blue, green, red, and neutral tones
- **Typography**: Clean, modern sans-serif fonts
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Accessibility**: WCAG compliant with proper contrast and keyboard navigation
- **Animations**: Smooth transitions and micro-interactions for better UX

### CSS Variables

Key design tokens are defined in `assets/css/styles.css`:

```css
--color-primary: #1e40af
--color-success: #10b981
--color-danger: #ef4444
--color-warning: #f59e0b
--shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1)
```

## API Integration

The system is designed to work with:

- **NFC Readers**: Send scan events via HTTP POST to patrol logging endpoints
- **GPS Services**: Integrate with GPS APIs for location tracking
- **Google Maps**: Display sites and checkpoints on interactive map

## Security Considerations

1. **Database**: Use prepared statements to prevent SQL injection
2. **Authentication**: Implement proper user authentication and session management
3. **Authorization**: Enforce role-based access control (admin vs user)
4. **Data**: Encrypt sensitive data in transit and at rest
5. **HTTPS**: Always use HTTPS in production
6. **Passwords**: Hash passwords using bcrypt (PHP's `password_hash()`)

## Troubleshooting

### Database Connection Error
- Check database credentials in `includes/config.php`
- Verify MySQL service is running
- Ensure database user has proper permissions

### Page Not Found (404)
- Verify web server is pointing to `public/` directory
- Check file permissions
- Ensure `.php` files are executable

### Charts Not Displaying
- Verify Chart.js library is loaded (CDN)
- Check browser console for JavaScript errors
- Ensure data is available in the database

### Map Not Showing
- Add your Google Maps API key to `public/map.php`
- Verify API key has Maps JavaScript API enabled
- Check browser console for API errors

## Future Enhancements

- Real-time patrol tracking dashboard
- Mobile app for guards
- Advanced reporting and export features
- Integration with access control systems
- Automated alert notifications
- Machine learning for anomaly detection
- Multi-language support
- Dark mode theme

## Support & Maintenance

For issues or questions:
1. Check the troubleshooting section
2. Review database schema and ensure all tables exist
3. Check PHP error logs
4. Verify file permissions

## License

This project is proprietary and confidential.

## Version

**Version**: 1.0.0  
**Last Updated**: June 2026

---

**Built with elegance and precision for professional security management.**
