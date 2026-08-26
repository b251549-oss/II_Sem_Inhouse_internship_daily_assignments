# AI Meeting Minutes Summarizer
### No XAMPP required

**Stack:** HTML + CSS + PHP + SQLite + jQuery

Runs with only the PHP built-in server. No Apache, no MySQL, no XAMPP.

---

## Requirements

- PHP 8.0+ (with PDO SQLite + cURL enabled — standard on most installs)

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

That’s it. The SQLite database is created automatically in the `data/` folder on first request.

---

## Demo Login

- Email: `demo@example.com`
- Password: `password123`

---

## Features

- Login / Register
- Paste notes → Generate prompt **or** live AI summarize (OpenAI / Grok / Groq compatible)
- Save structured minutes + action items
- Search meetings
- Manage action items (status updates)
- Export Markdown or HTML (print to PDF)

---

## Project Structure

```
meeting-minutes-web/
├── index.html
├── css/style.css
├── js/app.js
├── api/
│   ├── auth.php
│   ├── meetings.php
│   ├── actions.php
│   ├── ai.php
│   └── export.php
├── config/db.php      ← SQLite connection + auto schema
├── data/              ← SQLite file appears here automatically
└── README.md
```

---

## Live AI Setup

1. Login → **Settings**
2. API Base examples:
   - OpenAI → `https://api.openai.com/v1`
   - xAI Grok → `https://api.x.ai/v1`
3. Paste your API key → Save
4. Click **✨ Summarize with AI**

---

## Stop the server

Press `Ctrl + C` in the terminal.
