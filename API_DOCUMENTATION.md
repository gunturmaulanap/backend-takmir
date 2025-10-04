# 📚 API Documentation - Takmir Masjid

Dokumentasi lengkap untuk semua endpoint API yang tersedia di sistem manajemen takmir masjid.

## 🔐 Authentication

### Login

```http
POST /api/login
Content-Type: application/json

{
  "login": "email@example.com",  // atau username
  "password": "password123"
}
```

**Response:**

```json
{
  "success": true,
  "user": { ... },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

### Logout

```http
POST /api/logout
Authorization: Bearer <token>
```

---

## 🏛️ Admin Routes (Requires: admin, takmir roles)

**Base URL:** `/api/admin`
**Headers:** `Authorization: Bearer <token>`

### 📂 Categories

```http
# Get all categories (simple list)
GET /api/admin/categories/all

# Get categories with pagination
GET /api/admin/categories

# Create category
POST /api/admin/categories
{
  "name": "Keagamaan",
  "color": "#FF5733"
}

# Get single category
GET /api/admin/categories/{id}

# Update category
PUT /api/admin/categories/{id}
{
  "name": "Updated Name",
  "color": "#33FF57"
}

# Delete category
DELETE /api/admin/categories/{id}
```

### 💰 Transaksi Keuangan

```http
# Dashboard keuangan (summary + chart)
GET /api/admin/transactions/dashboard

# Chart data untuk tahun tertentu
GET /api/admin/transactions/chart-data?year=2024

# Monthly summary
GET /api/admin/transactions/monthly-summary?year=2024&month=12

# List transaksi dengan filter
GET /api/admin/transactions?type=income&year=2024&month=12&search=donasi

# Create transaksi
POST /api/admin/transactions
{
  "type": "income",
  "kategori": "Donasi Jamaah",
  "jumlah": 2500000,
  "tanggal": "2024-12-22",
  "keterangan": "Donasi jamaah untuk pembangunan masjid"
}

# Get single transaksi
GET /api/admin/transactions/{id}

# Update transaksi
PUT /api/admin/transactions/{id}

# Delete transaksi
DELETE /api/admin/transactions/{id}
```

### 👥 Jamaah

```http
# List jamaah
GET /api/admin/jamaahs?search=nama&page=1

# Create jamaah
POST /api/admin/jamaahs
{
  "nama": "Ahmad Sulaiman",
  "no_handphone": "081234567890",
  "alamat": "Jl. Masjid No. 123",
  "umur": 35,
  "jenis_kelamin": "Laki-laki",
  "aktivitas_jamaah": "Takmir"
}

# Get single jamaah
GET /api/admin/jamaahs/{id}

# Update jamaah
PUT /api/admin/jamaahs/{id}

# Delete jamaah
DELETE /api/admin/jamaahs/{id}
```

### 🎯 Events

```http
# List events
GET /api/admin/events?search=pengajian&page=1

# Create event
POST /api/admin/events
{
  "category_id": 1,
  "nama": "Pengajian Rutin",
  "tanggal_event": "2024-12-25",
  "waktu_event": "19:30",
  "deskripsi": "Pengajian rutin setiap minggu"
}

# Get single event
GET /api/admin/events/{id}

# Update event
PUT /api/admin/events/{id}

# Delete event
DELETE /api/admin/events/{id}
```

### 📅 Kalender Event Views

```http
# Get calendar data (Events + Jadwal Khutbah)
GET /api/admin/event-views?month=9&year=2025&type=event

# Parameters:
# - month: 1-12 (optional, default: current month)
# - year: YYYY (optional, default: current year)
# - type: event|jadwal_khutbah (optional, default: all)
```

**Response Calendar:**

```json
{
    "success": true,
    "message": "Data kalender berhasil dimuat",
    "data": {
        "calendar": [
            {
                "date": "2025-09-01",
                "day": 1,
                "events": [
                    {
                        "id": 1,
                        "title": "Pengajian Rutin",
                        "time": "19:30",
                        "type": "event",
                        "description": "Pengajian rutin mingguan",
                        "related_data": {
                            "category": "Keagamaan",
                            "image": null
                        }
                    }
                ]
            },
            {
                "date": "2025-09-06",
                "day": 6,
                "events": [
                    {
                        "id": 2,
                        "title": "Khatib: Ustadz Ahmad",
                        "time": "12:00",
                        "type": "jadwal_khutbah",
                        "description": "Khutbah Jumat",
                        "related_data": {
                            "khatib": "Ustadz Ahmad",
                            "imam": "Ustadz Budi",
                            "muadzin": "Ustadz Candra",
                            "tema_khutbah": "Pentingnya Sholat Berjamaah"
                        }
                    }
                ]
            }
        ],
        "summary": {
            "year": 2025,
            "month": 9,
            "month_name": "September 2025",
            "total_events": 5,
            "total_jadwal_khutbah": 4,
            "total_items": 9
        }
    }
}
```

### 🕌 Takmir

```http
# List takmir
GET /api/admin/takmirs

# Create takmir
POST /api/admin/takmirs
{
  "nama": "Ahmad Takmir",
  "jabatan": "Ketua",
  "no_handphone": "081234567890",
  "alamat": "Jl. Takmir No. 1"
}

# Get single takmir
GET /api/admin/takmirs/{id}

# Update takmir
PUT /api/admin/takmirs/{id}

# Delete takmir
DELETE /api/admin/takmirs/{id}
```

### 🕋 Imam

```http
# List imam
GET /api/admin/imams?search=nama&is_active=true

# Create imam
POST /api/admin/imams
{
  "nama": "Ustadz Ahmad",
  "no_handphone": "081234567890",
  "alamat": "Jl. Imam No. 1",
  "is_active": true
}

# Get single imam
GET /api/admin/imams/{id}

# Update imam
PUT /api/admin/imams/{id}

# Delete imam
DELETE /api/admin/imams/{id}
```

### 🎤 Khatib

```http
# List khatib
GET /api/admin/khatibs?search=nama&is_active=true

# Create khatib
POST /api/admin/khatibs
{
  "nama": "Ustadz Budi",
  "no_handphone": "081234567891",
  "alamat": "Jl. Khatib No. 1",
  "is_active": true
}

# Get single khatib
GET /api/admin/khatibs/{id}

# Update khatib
PUT /api/admin/khatibs/{id}

# Delete khatib
DELETE /api/admin/khatibs/{id}
```

### 📢 Muadzin

```http
# List muadzin
GET /api/admin/muadzins?search=nama&is_active=true

# Create muadzin
POST /api/admin/muadzins
{
  "nama": "Ustadz Candra",
  "no_handphone": "081234567892",
  "alamat": "Jl. Muadzin No. 1",
  "is_active": true
}

# Get single muadzin
GET /api/admin/muadzins/{id}

# Update muadzin
PUT /api/admin/muadzins/{id}

# Delete muadzin
DELETE /api/admin/muadzins/{id}
```

### 📋 Jadwal Khutbah

```http
# List jadwal khutbah
GET /api/admin/jadwal-khutbahs?search=tema&month=12&year=2024

# Create jadwal khutbah
POST /api/admin/jadwal-khutbahs
{
  "tanggal": "2024-12-27",
  "hari": "Jumat",
  "imam_id": 1,
  "khatib_id": 1,
  "muadzin_id": 1,
  "tema_khutbah": "Pentingnya Sholat Berjamaah",
  "is_active": true
}

# Get single jadwal khutbah
GET /api/admin/jadwal-khutbahs/{id}

# Update jadwal khutbah
PUT /api/admin/jadwal-khutbahs/{id}

# Delete jadwal khutbah
DELETE /api/admin/jadwal-khutbahs/{id}
```

---

## 👨‍💼 Superadmin Routes (Requires: superadmin role)

**Base URL:** `/api/superadmin`

### 📊 Dashboard

```http
GET /api/superadmin/dashboard
```

### 👥 Users Management

```http
# List users
GET /api/superadmin/users

# Create user
POST /api/superadmin/users

# Get single user
GET /api/superadmin/users/{id}

# Update user
PUT /api/superadmin/users/{id}

# Delete user
DELETE /api/superadmin/users/{id}

# Toggle user active status
PUT /api/superadmin/users/{id}/toggle-active
```

### 🔐 Roles & Permissions

```http
# Get permissions
GET /api/superadmin/permissions
GET /api/superadmin/permissions/all

# Roles CRUD
GET /api/superadmin/roles
POST /api/superadmin/roles
GET /api/superadmin/roles/{id}
PUT /api/superadmin/roles/{id}
DELETE /api/superadmin/roles/{id}
```

### 📂 Categories (Global)

```http
# Get all categories
GET /api/superadmin/categories/all

# Categories CRUD
GET /api/superadmin/categories
POST /api/superadmin/categories
GET /api/superadmin/categories/{id}
PUT /api/superadmin/categories/{id}
DELETE /api/superadmin/categories/{id}
```

---

## 🚀 Frontend Usage Examples

### React/Vue.js Implementation

```javascript
// API Service
class ApiService {
    constructor() {
        this.baseURL = "http://localhost:8000/api";
        this.token = localStorage.getItem("token");
    }

    async request(method, endpoint, data = null) {
        const config = {
            method,
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
            },
        };

        if (this.token) {
            config.headers.Authorization = `Bearer ${this.token}`;
        }

        if (data) {
            config.body = JSON.stringify(data);
        }

        const response = await fetch(`${this.baseURL}${endpoint}`, config);
        return response.json();
    }

    // Calendar Methods
    async getCalendar(month, year, type = null) {
        let query = `?month=${month}&year=${year}`;
        if (type) query += `&type=${type}`;

        return this.request("GET", `/admin/event-views${query}`);
    }

    // Financial Methods
    async getFinancialDashboard() {
        return this.request("GET", "/admin/transactions/dashboard");
    }

    async getTransactions(filters = {}) {
        const query = new URLSearchParams(filters).toString();
        return this.request("GET", `/admin/transactions?${query}`);
    }

    async createTransaction(data) {
        return this.request("POST", "/admin/transactions", data);
    }

    // Events Methods
    async getEvents(filters = {}) {
        const query = new URLSearchParams(filters).toString();
        return this.request("GET", `/admin/events?${query}`);
    }

    async createEvent(data) {
        return this.request("POST", "/admin/events", data);
    }
}

// Usage in Component
const api = new ApiService();

// Get calendar for current month
const calendarData = await api.getCalendar(9, 2025);

// Get events only
const eventsOnly = await api.getCalendar(9, 2025, "event");

// Get financial dashboard
const dashboard = await api.getFinancialDashboard();

// Create new transaction
const newTransaction = await api.createTransaction({
    type: "income",
    kategori: "Donasi",
    jumlah: 500000,
    tanggal: "2024-12-22",
    keterangan: "Donasi jamaah",
});
```

### Calendar Component Example

```javascript
// Calendar.vue / Calendar.jsx
export default {
    data() {
        return {
            currentMonth: 9,
            currentYear: 2025,
            selectedType: "", // 'event', 'jadwal_khutbah', or ''
            calendarData: null,
            loading: false,
        };
    },

    methods: {
        async loadCalendar() {
            this.loading = true;
            try {
                const response = await api.getCalendar(
                    this.currentMonth,
                    this.currentYear,
                    this.selectedType
                );
                this.calendarData = response.data;
            } catch (error) {
                console.error("Error loading calendar:", error);
            } finally {
                this.loading = false;
            }
        },

        nextMonth() {
            if (this.currentMonth === 12) {
                this.currentMonth = 1;
                this.currentYear++;
            } else {
                this.currentMonth++;
            }
            this.loadCalendar();
        },

        prevMonth() {
            if (this.currentMonth === 1) {
                this.currentMonth = 12;
                this.currentYear--;
            } else {
                this.currentMonth--;
            }
            this.loadCalendar();
        },
    },

    mounted() {
        this.loadCalendar();
    },
};
```

---

## 🔧 Error Handling

### Standard Error Responses

```json
// Validation Error (422)
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "nama": ["The nama field is required."],
    "email": ["The email field must be a valid email address."]
  }
}

// Unauthorized (401)
{
  "success": false,
  "message": "Unauthenticated."
}

// Forbidden (403)
{
  "success": false,
  "message": "You don't have permission to access this resource."
}

// Not Found (404)
{
  "success": false,
  "message": "Resource not found."
}

// Server Error (500)
{
  "success": false,
  "message": "Internal server error."
}
```

---

## 📝 Notes

-   Semua endpoint admin otomatis difilter berdasarkan `profile_masjid_id` user yang login
-   Superadmin dapat mengakses data semua masjid dengan parameter `profile_masjid_id`
-   Token JWT expired dalam 1 jam, perlu refresh atau login ulang
-   Upload file menggunakan `multipart/form-data` content type
-   Pagination default 10-15 items per page
-   Semua tanggal menggunakan format `YYYY-MM-DD`
-   Semua waktu menggunakan format `HH:mm`
