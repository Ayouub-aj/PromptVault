# 🗂️ PROMPT VAULT — Task Board

**Project:** La Knowledge Base des Prompts Performants  
**Agency:** DevGenius Solutions

---

## 📅 Suggested Day-by-Day Plan

| Day | Focus | Status |
| :--- | :--- | :---: |
| **Day 1 (Mar 24)** | Epics 1 + 2 + 3 — Setup, DB schema, PHP foundation | ✅ |
| **Day 2 (Mar 25)** | Epic 4 + 5 — Auth system + Prompts CRUD | [ ] |
| **Day 3 (Mar 26)** | Epic 6 + 7 — Admin panel + UI polish | [ ] |
| **Day 4 (Mar 27)** | Epic 8 + Bonus — Final testing, README, GitHub push | [ ] |

---

## 🟣 EPIC 1 — Project Setup & Environment

| Done | Task Description | Priority | Est. |
| :---: | :--- | :--- | :--- |
| ✅ | Create project folder structure (`/dashboard`, `/config`, `/database`, `/style`) | 🔴 High | 15 min |
| [ ] | Start XAMPP — enable Apache + MySQL services; verify via `localhost` | 🔴 High | 5 min |
| [ ] | Open phpMyAdmin and create database `prompt_repository` with UTF-8 charset | 🔴 High | 10 min |
| [ ] | Initialize local Git repo with `.gitignore`; create GitHub remote repo | 🔴 High | 15 min |
| [ ] | Create `README.md` with project title, description, tech stack, and setup instructions | 🟡 Medium | 10 min |
| [ ] | First commit: "init: project structure and documentation" | 🔴 High | 5 min |

---

## 🟣 EPIC 2 — Database Design & Schema

| Done | Task Description | Priority | Est. |
| :---: | :--- | :--- | :--- |
| [ ] | Design ERD showing `users`, `categories`, `prompts` relationships and FK constraints | 🔴 High | 20 min |
| [ ] | Write SQL to create `users` table (id, name, email, password, created_at) | 🔴 High | 15 min |
| [ ] | Write SQL to create `categories` table (id, name, description, created_at) | 🔴 High | 10 min |
| [ ] | Write SQL to create `prompts` table (id, title, content, category_id, user_id, rating, created_at, updated_at) | 🔴 High | 15 min |
| [ ] | Add Foreign Key constraints between `prompts.category_id` and `categories.id`; `prompts.user_id` and `users.id` | 🔴 High | 10 min |
| [ ] | Write seed data: 3 categories, 2 users, 5 sample prompts with realistic content | 🟡 Medium | 20 min |
| [ ] | Export full SQL script as `database.sql` in project root | 🔴 High | 10 min |
| [ ] | Commit: "feat(db): schema, constraints, and seed data" | 🔴 High | 5 min |

---

## 🟣 EPIC 3 — Backend Foundation (PHP)

| Done | Task Description | Priority | Est. |
| :---: | :--- | :--- | :--- |
| [ ] | Create `config/db.php` with PDO connection using `mysql:host=localhost;dbname=prompt_repository` | 🔴 High | 20 min |
| [ ] | Test PDO connection with error handling in a test file; verify database connectivity | 🔴 High | 10 min |
| [ ] | Create `includes/header.php` with navigation and `includes/footer.php` with copyright info | 🟡 Medium | 15 min |
| [ ] | Create `index.php` as homepage with basic HTML structure and style imports | 🔴 High | 15 min |
| [ ] | Commit: "feat(backend): PDO configuration, database connection, and base layout" | 🔴 High | 5 min |
