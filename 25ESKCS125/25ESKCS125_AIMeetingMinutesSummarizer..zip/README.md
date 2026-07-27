# AI Meeting Minutes Summarizer
### No XAMPP required

**Stack:** HTML + CSS + PHP + SQLite + jQuery

Runs with only the PHP built-in server. No Apache, no MySQL, no XAMPP.

---

## Requirements

- PHP 8.0+ (with PDO SQLite + cURL enabled â€” standard on most installs)

Check:
```bash
php -v
php -m | grep -E "pdo_sqlite|curl"
```

---

## Run in 10 seconds

```bash
cd meeting-minutes-web
php -S localhost:8000
```

Then open: **http://localhost:8000**

Thatâ€™s it. The SQLite database is created automatically in the `data/` folder on first request.

---

## Demo Login

- Email: `demo@example.com`
- Password: `password123`

---

## Features

- Login / Register
- Paste notes â†’ Generate prompt **or** live AI summarize (OpenAI / Grok / Groq compatible)
- Save structured minutes + action items
- Search meetings
- Manage action items (status updates)
- Export Markdown or HTML (print to PDF)

---

## Project Structure

```
meeting-minutes-web/
â”œâ”€â”€ index.html
â”œâ”€â”€ css/style.css
â”œâ”€â”€ js/app.js
â”œâ”€â”€ api/
â”‚   â”œâ”€â”€ auth.php
â”‚   â”œâ”€â”€ meetings.php
â”‚   â”œâ”€â”€ actions.php
â”‚   â”œâ”€â”€ ai.php
â”‚   â””â”€â”€ export.php
â”œâ”€â”€ config/db.php      â† SQLite connection + auto schema
â”œâ”€â”€ data/              â† SQLite file appears here automatically
â””â”€â”€ README.md
```

---

## Live AI Setup

1. Login â†’ **Settings**
2. API Base examples:
   - OpenAI â†’ `https://api.openai.com/v1`
   - xAI Grok â†’ `https://api.x.ai/v1`
3. Paste your API key â†’ Save
4. Click **âœ¨ Summarize with AI**

---

## Stop the server

Press `Ctrl + C` in the terminal.