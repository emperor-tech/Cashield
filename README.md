# Cashield: Comprehensive Campus Security & Incident Management System

**Cashield** (derived from Campus Shield) is a robust, real-time security and incident management platform designed for Nigerian educational institutions. It empowers students, staff, and security personnel to report incidents, coordinate emergency responses, and create a safer academic environment through advanced technology and structured security protocols.

---

## 🚨 Project Background

- **Project Title:** Campus Shield (Cashield)
- **Purpose:** Final Year Project
- **Author:** Abdullateef Babatunde, 400 Level Computer Science
- **Institution:** Air Force Institute of Technology, Kaduna (AFIT.) (Faculty of Computing | Department of Computer Science)

---

## 🌟 Key Features

### 1. **Advanced Incident Reporting System**
- Categorized incident reporting with customizable report types and severity levels
- Evidence collection with media attachments and location tracking
- Panic button for emergencies with instant alerts and countdown timer
- Anonymous reporting option with tracking codes for follow-up
- Incident date and time recording with detailed status history

### 2. **Security Team Management**
- Comprehensive security team organization with team leaders and members
- Zone-based security assignments with campus mapping
- Security shift scheduling and management
- Checkpoint scanning and patrol route tracking
- Shift incident logging and reporting

### 3. **Response Protocol System**
- Customizable response protocols based on incident categories and severity
- Step-by-step response procedures for different severity levels
- Escalation triggers and automated escalation timelines
- Integration with external agencies (police, medical, fire services)
- Required documentation and follow-up action tracking

### 4. **Campus Zone Management**
- Geographic mapping of campus zones with boundaries
- Zone-specific security teams and checkpoints
- Location-based incident analysis and hotspot identification
- Checkpoint scanning for security patrol verification

### 5. **Real-Time Communication & Collaboration**
- Live chat between reporters, security teams, and administrators
- Incident timeline with comments, status changes, and response actions
- Broadcast messaging for campus-wide alerts
- Team-based communication channels

### 6. **Advanced Admin Dashboard & Analytics**
- Comprehensive analytics with incident trends, response times, and resolution rates
- Security team performance metrics and patrol coverage analysis
- Interactive heatmap showing incident hotspots and security coverage
- Customizable reports and data exports
- System audit logs for accountability and compliance

### 7. **User Management & Permissions**
- Role-based access control with customizable permissions
- User activity tracking and audit logging
- Account status management (active, inactive, suspended)
- Soft delete functionality for data retention compliance

### 8. **Notification & Alert System**
- Multi-channel notifications (in-app, email, SMS)
- Customizable notification preferences by incident type and zone
- Subscription-based alerts for specific campus areas
- Priority-based notification delivery

### 9. **Community Engagement Features**
- Gamification with badges and achievements
- Community watch subscriptions
- Anonymous tip submission
- Public safety resources and guidelines

### 10. **System Configuration & Integration**
- Comprehensive settings management
- Backup and restore functionality
- External system integrations (emergency services, campus systems)
- Customizable security protocols and response procedures

---

## 🛠️ Enhanced Technology Stack
- **Backend:** Laravel (PHP) with advanced service architecture
- **Frontend:** Blade templates, Tailwind CSS, Alpine.js, Vue components
- **Real-Time:** Laravel Echo, Pusher/WebSockets for live updates
- **Maps:** Leaflet.js with custom overlays for campus mapping
- **Data Visualization:** Chart.js for analytics and reporting
- **PDF/Export:** dompdf, CSV exports for reports and data
- **Database:** MySQL with soft deletes and audit logging
- **Security:** Role-based access control, audit trails, data encryption
- **Notifications:** Multi-channel delivery (in-app, email, SMS)

---

## 🚀 Getting Started

1. **Clone the repository:**
   ```bash
   git clone https://github.com/emperor-tech/Cashield.git
   cd Cashield
   ```
2. **Install dependencies:**
   ```bash
   composer install
   npm install && npm run build
   ```
3. **Environment setup:**
   - Copy `.env.example` to `.env` and configure:
     - Database credentials
     - Pusher/WebSockets for real-time features
     - Mail settings for notifications
     - Map API keys (if applicable)
   - Generate application key
   ```bash
   php artisan key:generate
   ```
4. **Database migration & seeding:**
   ```bash
   php artisan migrate --seed
   ```
5. **Set permissions:**
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```
6. **Serve the application:**
   ```bash
   php artisan serve
   ```
   Or configure Apache/Nginx as needed.

---

## 📸 Screenshots
> _We would soon add screenshots of the dashboard, report form, security management, response protocols, analytics, and mobile views here._

---

## 🤝 Contributing
This project is a final year academic work. For suggestions, improvements, or bug reports, please open an issue or contact the author.

---

## 👨‍💻 Author(s)
- **Abdullateef Babatunde (Project Owner)**  
400 Level Computer Science  
https://github.com/emperor-tech || https://x.com/big_babsss || oladejoabdullateef2005@gmail.com

- **Amuda Rasheed (Contributor)**
400 Level Computer Science
https://github.com/techsalaf || https://linkedin.com/in/techsalaf || https://x.com/techsalaf || rasheed@my360school.com
---

## 📄 License
This project is for academic purposes. For other uses, please contact the author.

---

## 💡 Inspiration
Cashield is inspired by the need for safer campuses in Nigeria, especially at AFIT Kaduna. The idea evolved from personal experiences and observations during my time at the institute. The project aims to create a comprehensive security ecosystem where students feel protected while pursuing their education, leveraging technology to empower all campus stakeholders to take an active role in maintaining safety and security.
