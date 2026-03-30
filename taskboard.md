# 🗂️ PROMPT VAULT — Task Board

**Project:** La Knowledge Base des Prompts Performants  
**Agency:** DevGenius Solutions

---

## 📅 Suggested Day-by-Day Plan

| Day | Focus | Status |
| :--- | :--- | :---: |
| **Day 1 (Mar 24)** | Epics 1 + 2 + 3 — Setup, DB schema, PHP connection | ✅ |
| **Day 2 (Mar 25)** | Epic 4 + 5 — Auth system + Prompts CRUD | [ ] |
| **Day 3 (Mar 26)** | Epic 6 + 7 — Admin/user panel + UI polish | [ ] |
| **Day 4 (Mar 27)** | Epic 8 + Bonus — Final testing, README, GitHub final push | [ ] |

---

## 🟣 EPIC 1 — Project Setup & Environment

| Done | Task Description | Priority | Est. |
| :---: | :--- | :--- | :--- |
| ✅ | Create project folder structure (`app`, `/dashboard`, `/config`, `/database`, `/style`) | 🔴 High | 15 min |
| ✅ | Start XAMPP — enable Apache + MySQL services; verify via `localhost` | 🔴 High | 5 min |
| ✅ | Open phpMyAdmin and create database `prompt_repository` with UTF-8 charset | 🔴 High | 10 min |
| ✅ | Initialize local Git repo with `.gitignore`; create GitHub remote repo | 🔴 High | 15 min |
| ✅ | Create `README.md` with project title, description, tech stack, and setup instructions | 🟡 Medium | 10 min |
| ✅ | First commit: "init: project structure and documentation" | 🔴 High | 5 min |

---

## 🟣 EPIC 2 — Database Design & Schema

| Done | Task Description | Priority | Est. |
| :---: | :--- | :--- | :--- |
| ✅ | Design ERD showing `users`, `categories`, `prompts` relationships and FK constraints | 🔴 High | 20 min |
| ✅ | Write SQL to create `users` table (id, name, email, password, created_at) | 🔴 High | 15 min |
| ✅ | Write SQL to create `categories` table (id, name, description, created_at) | 🔴 High | 10 min |
| ✅ | Write SQL to create `prompts` table (id, title, content, category_id, user_id, rating, created_at, updated_at) | 🔴 High | 15 min |
| ✅ | Add Foreign Key constraints between `prompts.category_id` and `categories.id`; `prompts.user_id` and `users.id` | 🔴 High | 10 min |
| ✅ | Write seed data: 3 categories, 2 users, 5 sample prompts with realistic content | 🟡 Medium | 20 min |
| ✅ | Export full SQL script as `database.sql` in project root | 🔴 High | 10 min |
| ✅ | Commit: "feat(db): schema, constraints, and seed data" | 🔴 High | 5 min |

---

## 🟣 EPIC 3 — Backend Foundation (PHP)

| Done | Task Description | Priority | Est. |
| :---: | :--- | :--- | :--- |
| ✅ | Create `config/db.php` with PDO connection using `mysql:host=localhost;dbname=prompt_vault` | 🔴 High | 20 min |
| ✅ | Test PDO connection with error handling in a test file; verify database connectivity, ... | 🔴 High | 10 min |
| ✅ | Create `includes/header.php` with navigation and `includes/footer.php` with copyright info | 🟡 Medium | 15 min |
| ✅ | Create `index.php` as homepage with basic HTML structure and style imports | 🔴 High | 15 min |
| ✅ | Commit: "feat(backend): PDO configuration, database connection, and base layout" | 🔴 High | 5 min |

---

## 🟣 EPIC 4 — Authentication & User Sessions

| Done | Task Description | Priority | Est. |
| :---: | :--- | :--- | :--- |
| ✅ | Create `register.php` with form validation (unique username/email) | 🔴 High | 30 min |
| ✅ | Implement Password Hashing (BCRYPT) and secure database insertion | 🔴 High | 15 min |
| ✅ | Create `login.php` to verify credentials and start PHP Session | 🔴 High | 25 min |
| ✅ | Middleware: Create `auth.php` to protect private routes/dashboard | 🟡 Medium | 15 min |
| ✅ | Create `logout.php` to destroy session and redirect to homepage | 🟢 Low | 5 min |

---

## 🟣 EPIC 5 — Prompts Management (CRUD)

| Done | Task Description | Priority | Est. |
| :---: | :--- | :--- | :--- |
| ✅ | Create `create-prompt.php` with dynamic category selection from DB | 🔴 High | 30 min |
| ✅ | Create detailed `view-prompt.php` page using `GET` ID parameters | 🔴 High | 20 min |
| ✅ | Implement Edit functionality (ensure user owns the prompt first) | 🔴 High | 30 min |
| ✅ | Implement Delete functionality with confirmation dialog | 🟡 Medium | 15 min |
| ✅ | Commit: "feat(crud): full prompt lifecycle implementation" | 🟡 Medium | 5 min |

---

## 🟣 EPIC 6 — Browse, Search & Filter

| Done | Task Description | Priority | Est. |
| :---: | :--- | :--- | :--- |
| ✅ | Add Search bar to homepage for keyword-based filtering | 🔴 High | 20 min |
| ✅ | Implement Category filters on the sidebar/header | 🟡 Medium | 15 min |
| ✅ | Add Sorting options (Newest vs oldest) | 🟢 Low | 10 min |
| ✅ | Pagination: Limit results per page (e.g., 10 prompts per page) | 🟡 Medium | 25 min |

---

## 🟣 EPIC 7 — Dashboards (User & Admin)

| Done | Task Description | Priority | Est. |
| :---: | :--- | :--- | :--- |
| ✅ | `app/dashboard.php`: List user's active posts with edit/delete quick links | 🔴 High | 20 min |
| ✅ | Admin Panel: List all users and categories (Admin only access) | 🟡 Medium | 30 min |
| ✅ | Implement "Download Prompt" as `.txt` feature | 🟢 Low | 15 min |

---

## 🟣 EPIC 8 — UI/UX & Final Polish

| Done | Task Description | Priority | Est. |
| :---: | :--- | :--- | :--- |
| ✅ | Design base CSS system in `style/main.css` (Colors, Typography) | 🔴 High | 40 min |
| ✅ | Implement responsive design (Mobile first) | 🔴 High | 30 min |
|  | Add success/error alerts using simple CSS components | 🟡 Medium | 15 min |
| ✅ | Final verification of all DB constraints and security (XSS/SQLi) | 🔴 High | 30 min |
| ✅ | Final Commit & Documentation update (README.md) | 🟢 Low | 10 min |
