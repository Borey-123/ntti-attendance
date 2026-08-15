- NTTI Teacher Attendance System

A professional, bilingual (English/Khmer) attendance management suite designed for educational institutions. The system features real-time RFID scanning, live monitoring, automated reporting, and a teacher portal.


- What Each Feature is Used For

1. Admin Dashboard (`/dashboard`)
The central hub for administrators. It provides a high-level overview of today's attendance, system health, and quick actions.
- Used for:Viewing real-time attendance rates, monitoring the "Scan Pulse" (live check-ins/outs), and seeing overall performance trends for the month.

2. Scan Station (`/scan`)
The primary interface for manual attendance scanning.
- Used for: Allowing teachers to tap their RFID cards (or allowing admins to manually search and scan them) to record morning and afternoon check-ins and check-outs. It includes visual and audio feedback, and tracks total scans for the day.

3. Live Monitor (`/live-monitor`)
A dedicated, real-time display meant to be cast to a TV or large screen in a lobby or teacher's lounge.
- Used for: Displaying a rolling feed of the most recent check-ins/outs, showing which departments are currently present, and providing a clean, auto-updating "flight-board" style view of daily attendance.

4. Teacher Directory & Management (`/teachers`)
The master list of all teachers in the institution.
- Used for: Adding new teachers, editing profiles, assigning departments, deactivating accounts, and viewing detailed individual attendance insights and histories.

5. RFID Management (`/rfid-cards`)
The pairing center for physical smart cards.
- Used for: Linking physical RFID card UIDs to specific teacher accounts. Also used to monitor the status of the physical ESP32 scanner hardware.

6. Reports & Analytics (`/reports`)
The data extraction tool for HR and administration.
- Used for: Filtering attendance records by date range, department, or status, and exporting the data as formatted Excel (.xlsx) or PDF files for payroll and compliance.

7. Settings Hub (`/settings`)
The configuration center for system logic.
- Used for: Setting shift hours (morning/afternoon), defining "late" cutoffs, configuring Telegram Bot Webhooks for real-time notifications, updating the public portal banner/announcements, and managing IP restrictions.

8. Public Teacher Portal (`/portal`)
A self-service portal accessible without admin credentials.
- Used for: Allowing teachers to search for their own profile to view their personal attendance history, check their monthly on-time rates, and read institutional announcements.


- How to Run the Project (Step-by-Step)

Follow these steps to set up and run the project from scratch on a new machine.

Prerequisites
Before you begin, ensure you have the following installed:
- PHP (v8.1 or higher)
- Composer (PHP dependency manager)
- Node.js & npm (For compiling frontend assets)
- MySQL / MariaDB (via XAMPP, Laragon, or standalone)

1. Clone or Extract the Project
Place the project folder inside your web server directory (e.g., `C:\xampp\htdocs\ntti-attendance`).

2. Install Backend Dependencies
Open your terminal, navigate to the project directory, and run:
```bash
composer install
```

3. Install Frontend Dependencies
To install the required JavaScript and CSS packages:
```bash
npm install
```
Then, compile the frontend assets (Tailwind, Vite, etc.):
```bash
npm run build
```
*(If you are actively developing, you can run `npm run dev` instead).*

4. Environment Configuration
Copy the example environment file to create your local `.env` file:
```bash
cp .env.example .env
```
Generate the application encryption key:
```bash
php artisan key:generate
```

5. Database Setup
1. Open your database manager (e.g., phpMyAdmin) and create an empty database named `ntti_attendance` (or your preferred name).
2. Open your `.env` file and update the database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ntti_attendance
   DB_USERNAME=root
   DB_PASSWORD=
   ```
3. Run the database migrations and seed the initial data (this creates the admin account and default settings):
   ```bash
   php artisan migrate --seed
   ```

6. Link Storage for Images
To ensure teacher avatars and portal banners are displayed correctly, link the storage directory to the public folder:
```bash
php artisan storage:link
```

7. Start the Application
Run the Laravel development server:
```bash
php artisan serve
```
By default, the application will be available at: http://localhost:8000

(Note: To make it accessible across your local network for the ESP32 scanner or Live Monitor TVs, you can run `php artisan serve --host=0.0.0.0 --port=8000`).


Default Access
- Admin Login Page: `http://localhost:8000/login`
- Default Email: `admin@ntti.edu.kh`
- Default Password: `password` (Change this immediately in production)


- Hardware Integration (ESP32)
If you are using the physical ESP32 scanner:
1. Ensure the ESP32 is connected to the same Wi-Fi network as the server.
2. In the ESP32 code, set the server IP address to the IPv4 address of the computer running `php artisan serve`.
3. The ESP32 sends POST requests to `/api/attendance/scan`.
4. Check the Settings Hub to ensure the scanner's IP is authorized.

Custom Enterprise License for NTTI.
