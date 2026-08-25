# NTTI Teacher Attendance System - Database Schema & ER Diagram Prototype

This document presents the complete database schema design, entity relationship analysis, and Mermaid ER diagram prototype for the NTTI Teacher Attendance System.

---

## 1. Entity Relationship (ER) Diagram

```mermaid
erDiagram
    DEPARTMENT ||--o{ TEACHER : "has (1:N)"
    TEACHER ||--o| RFID_CARD : "assigned (1:1)"
    TEACHER ||--o{ ATTENDANCE : "logs (1:N)"
    TEACHER ||--o{ ATTENDANCE_CORRECTION : "submits (1:N)"
    TEACHER ||--o{ SECURITY_LOG : "generates (1:N)"

    DEPARTMENT {
        bigint id PK
        string name "Department Name (EN)"
        string name_kh "Department Name (KH)"
        string description
        bigint head_id FK "Head of Department (Teacher ID)"
        timestamp created_at
        timestamp updated_at
    }

    TEACHER {
        bigint id PK
        string employee_id UK "Unique Teacher ID (e.g. T0001)"
        string name "Full Name (EN)"
        string name_kh "Full Name (KH)"
        string department "Department Name"
        string email UK
        string phone UK
        longtext photo "Base64 or Image Path"
        string position "Job Title / Role"
        string status "active / inactive"
        string telegram_chat_id "Linked Telegram Chat ID"
        string portal_pin "Hashed 6-digit Portal Access PIN"
        timestamp created_at
        timestamp updated_at
    }

    RFID_CARD {
        bigint id PK
        string uid UK "Hardware Hex Card UID"
        bigint teacher_id FK "Assigned Teacher ID"
        string status "active / unassigned / blocked"
        timestamp assigned_at
        timestamp created_at
        timestamp updated_at
    }

    ATTENDANCE {
        bigint id PK
        bigint teacher_id FK "Teacher ID"
        date date "Attendance Date (YYYY-MM-DD)"
        string rfid_uid "RFID Card UID used"
        time morning_in "Morning Check-In Time"
        time morning_out "Morning Check-Out Time"
        string morning_status "present / late / absent"
        time afternoon_in "Afternoon Check-In Time"
        time afternoon_out "Afternoon Check-Out Time"
        string afternoon_status "present / late / absent"
        time evening_in "Evening Check-In Time"
        time evening_out "Evening Check-Out Time"
        string evening_status "present / late / absent"
        text manual_note "Admin / System Note"
        timestamp created_at
        timestamp updated_at
    }

    ATTENDANCE_CORRECTION {
        bigint id PK
        bigint teacher_id FK "Teacher ID"
        date date "Target Date for Dispute"
        string shift "morning / afternoon / both"
        text reason "Explanation by Teacher"
        string status "pending / approved / rejected"
        timestamp created_at
        timestamp updated_at
    }

    SECURITY_LOG {
        bigint id PK
        bigint teacher_id FK "Teacher ID (nullable)"
        string action "Scan attempt / PIN change / Auth"
        string ip_address "Client IP Address"
        string user_agent "Browser / Hardware User Agent"
        timestamp created_at
    }
```

---

## 2. Table Structures & Primary / Foreign Keys

### Table 1: `teachers`
- **Primary Key:** `id`
- **Unique Keys:** `employee_id`, `email`, `phone`
- **Description:** Stores core profiles of teachers, contact details, Telegram notifications chat ID, and encrypted PIN for portal login.

### Table 2: `attendance`
- **Primary Key:** `id`
- **Foreign Key:** `teacher_id` $\rightarrow$ `teachers(id)` (ON DELETE CASCADE)
- **Unique Constraint:** `(teacher_id, date)` - Ensures only **one row per teacher per calendar date**.
- **Description:** Tracks morning, afternoon, and evening shift scans (`check-in` and `check-out` timestamps) along with status flags (`present`, `late`, `absent`).

### Table 3: `rfid_cards`
- **Primary Key:** `id`
- **Foreign Key:** `teacher_id` $\rightarrow$ `teachers(id)` (ON DELETE SET NULL / CASCADE)
- **Unique Key:** `uid`
- **Description:** Maps physical 13.56MHz RFID card hex UIDs to registered teachers.

### Table 4: `departments`
- **Primary Key:** `id`
- **Foreign Key:** `head_id` $\rightarrow$ `teachers(id)` (Nullable)
- **Description:** Stores department master data (in English and Khmer).

### Table 5: `attendance_corrections`
- **Primary Key:** `id`
- **Foreign Key:** `teacher_id` $\rightarrow$ `teachers(id)`
- **Description:** Handles missing scan disputes and manual correction requests submitted via the Teacher Portal.

---

## 3. Detailed Relationship Summary

| Primary Table | Related Table | Relationship Type | Key Connection | Description |
| :--- | :--- | :--- | :--- | :--- |
| **Teacher** | **Attendance** | **One-to-Many (1:N)** | `teachers.id` = `attendance.teacher_id` | One teacher accumulates multiple daily attendance records over time. |
| **Teacher** | **RFID Card** | **One-to-One (1:1)** | `teachers.id` = `rfid_cards.teacher_id` | One active RFID card is assigned to one specific teacher. |
| **Department**| **Teacher** | **One-to-Many (1:1/N)**| `departments.name` = `teachers.department` | One department has many teachers. |
| **Teacher** | **Correction** | **One-to-Many (1:N)** | `teachers.id` = `attendance_corrections.teacher_id` | One teacher can submit multiple correction requests for missed scans. |

---

## 4. Defense Presentation Speaking Script (Database Section)

> *"Honorable Committee Members,*
>
> *Our system utilizes a **Relational Database Model (RDBMS)** built on MySQL. The core database schema is structured around **One-to-Many (1:N)** and **One-to-One (1:1)** relationships.*
>
> *1. **Data Consistency:** By placing strict foreign key constraints between `teachers` and `attendance`, we eliminate orphan records and ensure data integrity.*
> *2. **Single Row Per Day Optimization:** Instead of inserting new rows for every single tap on the RFID reader, our schema updates shift columns (`morning_in`, `morning_out`, `afternoon_in`, `afternoon_out`) within a single daily record per teacher. This drastically reduces query execution time when generating monthly reports.*
> *3. **Security:** Sensitive fields like `portal_pin` are encrypted, and hardware card `uid` fields are uniquely indexed to prevent duplicate badge assignments."*
