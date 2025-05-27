# Cashield: Real-Time Campus Crime Reporting System

**Cashield** (derived from Campus Shield) is a modern, real-time crime reporting and safety platform designed for Nigerian school campuses. It empowers students, staff, and security personnel to report incidents, respond to emergencies, and foster a safer academic environment through technology.

---

## 🚨 Project Background

- **Project Title:** Campus Shield (Cashield)
- **Purpose:** Final Year Project
- **Author:** Abdullateef Babatunde, 400 Level Computer Science
- **Institution:** Air Force Institute of Technology, Kaduna (AFIT.) (Faculty of Computing | Department of Computer Science)

---

## 🌟 Key Features

### 1. **Real-Time Crime Reporting**
- Instantly report incidents (theft, assault, suspicious activity, etc.) with location, description, and media attachments.
- Panic button for emergencies: triggers alerts, countdown, and optional live chat with responders.
- Anonymous reporting option for sensitive cases.

### 2. **Live Chat & Incident Collaboration**
- Real-time chat between reporters and campus security after a panic alert or on report details.
- Incident timeline: aggregates comments, chat, and status changes for each report.

### 3. **Community Watch & Area Alerts**
- Users can subscribe to area-based alerts (e.g., hostel, faculty, car park).
- Manage alert subscriptions in user profile.
- Receive push notifications for incidents in subscribed areas.

### 4. **Gamification & Badges**
- Earn badges for first report, five reports, first panic alert, and more.
- Badges displayed on user profiles to encourage positive engagement.

### 5. **Admin Dashboard & Analytics**
- Visual analytics: charts for incident severity, frequency, and time trends.
- Real-time incident feed for admins and security.
- Interactive heatmap (Leaflet.js) showing incident hotspots on campus.

### 6. **User Profiles & Preferences**
- Upload avatar, manage notification preferences, and view report/alert history.
- See earned badges and community contributions.

### 7. **Modern UI/UX**
- Beautiful, responsive design with Tailwind CSS.
- Dark mode, smooth feedback, and accessible forms.
- Intuitive navigation with quick actions, notification bell, and user avatar.

---

## 🛠️ Technology Stack
- **Backend:** Laravel (PHP)
- **Frontend:** Blade, Tailwind CSS, Alpine.js
- **Real-Time:** Laravel Echo, Pusher/WebSockets
- **Maps:** Leaflet.js
- **PDF/Export:** dompdf
- **Database:** MySQL

---

## 🚀 Getting Started

1. **Clone the repository:**
   ```bash
   git clone <repo-url>
   cd cashield
   ```
2. **Install dependencies:**
   ```bash
   composer install
   npm install && npm run build
   ```
3. **Environment setup:**
   - Copy `.env.example` to `.env` and set your database and Pusher credentials.
   - Run `php artisan key:generate`
4. **Database migration & seeding:**
   ```bash
   php artisan migrate --seed
   ```
5. **Set permissions:**
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```
6. **Serve the app:**
   ```bash
   php artisan serve
   ```
   Or configure Apache/Nginx as needed.

---

## 📸 Screenshots
> _We can add screenshots of the dashboard, report form, chat, admin analytics, and mobile view here._

---

## 🤝 Contributing
This project is a final year academic work. For suggestions, improvements, or bug reports, please open an issue or contact the author.

---

## 👨‍💻 Author
**Abdullateef Babatunde**  
400 Level Computer Science  
[LinkedIn/GitHub/Email if desired]

---

## 📄 License
This project is for academic purposes. For other uses, please contact the author.

---

## 💡 Inspiration
Cashield is inspired by the need for safer campuses in Nigeria, leveraging technology to empower students and staff to take an active role in campus security.
