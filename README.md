# 🔐 PromptVault — The High-Performance Prompt Knowledge Base

> A full-stack internal platform for developer teams to save, organize, and reuse their best LLM prompts — so no "perfect prompt" ever gets lost in a chat history again.

---

## 🖼️ Screenshots

> *()*

---

## 🛠️ Tech Stack

| Layer      | Technology                        |
|------------|-----------------------------------|
| Backend    | PHP 8+                            |
| Database   | MySQL                             |
| DB Access  | PDO + Prepared Statements         |
| Auth       | PHP Sessions + `password_hash()`  |
| Frontend   | HTML5 / CSS3                      |

---

## ✨ Features

- 🔐 **Secure Authentication** — Register/Login with hashed passwords
- ✍️ **Prompt Management** — Create, edit, delete your prompts (full CRUD)
- 🗂️ **Categories** — Tag prompts by theme (Code, SQL, DevOps, Marketing...)
- 🔍 **Smart Filtering** — Browse prompts by category
- 👑 **Admin Panel** — Manage categories & view top contributors
- 🛡️ **Zero SQL Injection** — All queries use Prepared Statements

---

## 🗄️ Database Schema
```
users
├── id (PK)
├── username
├── email
├── password_hash
└── created_at

categories
├── id (PK)
└── name

prompts
├── id (PK)
├── title
├── content
├── user_id (FK → users.id)
├── category_id (FK → categories.id)
└── created_at
```

---

## 🚀 Installation

### Prerequisites
- PHP 8+ with PDO extension enabled
- MySQL 5.7+ or MariaDB
- A local server (XAMPP, Laragon, WAMP, or similar)

### Steps

1. **Clone the repository**
```bash
   git clone https://github.com/your-username/prompt-vault.git
   cd prompt-vault
```

2. **Import the database**
   - Open phpMyAdmin (or your MySQL client)
   - Create a new database: `prompt_vault`
   - Import the file: `database/schema.sql` (includes seed data)

3. **Configure the connection**
   - Open `config/db.php`
   - Update with your local credentials:
```php
     $host = 'localhost';
     $db   = 'prompt_vault';
     $user = 'root';
     $pass = '';
```

4. **Run the project**
   - Place the folder in your server's `htdocs` or `www` directory
   - Visit: `http://localhost/prompt-vault`

---

## 📁 Project Structure
```
prompt-vault/
├── config/
│   └── db.php              # Centralized PDO connection
├── database/
│   └── schema.sql          # DB creation + seed data
├── auth/
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── prompts/
│   ├── index.php           # List & filter prompts
│   ├── create.php
│   ├── edit.php
│   └── delete.php
├── admin/
│   └── dashboard.php       # Category management + stats
├── includes/
│   └── header.php
└── README.md
```

---

## 🔒 Security Highlights

- Passwords hashed with `password_hash()` / verified with `password_verify()`
- All DB queries use **PDO Prepared Statements** — no raw user input in SQL
- Server-side form validation on all inputs
- Session-based access control on protected pages

---

## 🏆 Bonus Features (Advanced)

- [ ] OOP refactor — `Database` and `Prompt` classes
- [ ] Multi-criteria search — filter by title **and** category simultaneously

---

## 👤 Author

**[IDBELHAJ ayoub]** — Built as part of a Full-Stack PHP/MySQL project at DevGenius Solutions.

