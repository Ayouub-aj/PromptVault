# 🔐 PromptVault — The High-Performance Prompt Knowledge Base

> A full-stack internal platform for developer teams to save, organize, and reuse their best LLM prompts — so no "perfect prompt" ever gets lost in a chat history again.


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

```markdown

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

## 📊 schema visualisation

![schema visualisation](/includes/img/tables.png)

### Tables & their columns

- users — stores accounts. role (user/admin) is an ENUM so no separate admin table is needed.

- categories — simple lookup table for themes like Code, SQL, DevOps...

- prompts — the core table, with user_id and category_id as Foreign Keys linking to the other two.

#### Relationships (the crow's foot notation)

- users → prompts : one user can write zero or many prompts (||--o{)

- categories → prompts : one category can tag zero or many prompts (||--o{)

<font color="dark green">This is exactly what your INNER JOIN query in Step 10 exploits — it walks those FK links to replace raw IDs with human-readable names!</font>

---

## 📊 visualisation

![schema visualisation](/includes/img/diagram.png)

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

1. **Import the database**
   - Open phpMyAdmin (or your MySQL client)
   - Create a new database: `prompt_vault`
   - Import the file: `database/schema.sql` (includes seed data)

2. **Configure the connection**
   - Open `config/db.php`
   - Update with your local credentials:

```php
     $host = 'localhost';
     $db   = 'prompt_vault';
     $user = 'root';
     $pass = '';
```

1. **Run the project**
   - Place the folder in your server's `htdocs` or `www` directory
   - Visit: `http://localhost/prompt-vault`

---

## 📁 Project Structure

```markdown
prompt-vault/
├── app/
│   ├── auth/               # Authentication logic (Login/Register/Logout)
│   │   ├── auth.php
│   │   ├── login.php
│   │   ├── logout.php
│   │   └── register.php
│   ├── prompt/             # Prompt management (CRUD + View)
│   │   ├── create-prompt.php
│   │   ├── delete-prompt.php
│   │   ├── download.php
│   │   ├── edit-prompt.php
│   │   └── view-prompt.php
│   ├── admin.php           # Admin control center
│   ├── dashboard.php       # User dashboard
│   ├── index.php           # Main repository view
│   ├── edit-category.php   # Category management
│   └── delete-category.php
├── config/
│   └── db.php              # Centralized PDO connection & BASE_URL
├── database/
│   └── schema.sql          # DB creation + seed data
├── includes/
│   ├── header.php          # Global navigation
│   ├── footer.php
│   └── img/                # Screenshots and UI assets
├── style/
│   └── main.css            # Custom CSS
├── tests/
│   ├── unit_tests.php      # Functional test logic
│   └── TestPdoConnection.php
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

## 🖼️ Screenshots

![login](/includes/img/login.png)
![register](/includes/img/register.png)
![dashboard](/includes/img/full_command_center.png)
![admin](/includes/img/admin.png)
![create](/includes/img/create.png)
![edit](/includes/img/edit.png)
![delete](/includes/img/delete.png)


## 👤 Author

**[IDBELHAJ ayoub]** — Built as part of a Full-Stack PHP/MySQL project at DevGenius Solutions.
