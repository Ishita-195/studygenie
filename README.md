# StudyGenie — AI-Powered Document Learning Platform

StudyGenie turns any PDF or Word document into an interactive learning experience. Upload your study material and instantly get **AI-generated summaries**, a **document-aware chatbot**, and **auto-generated quizzes** — all grounded in the actual content of your document.

Built on a **Retrieval-Augmented Generation (RAG)** pipeline with **semantic search** and **Groq-hosted LLM inference**, StudyGenie understands meaning — not just keywords — and never makes up answers that aren't in your document.

> Originally started as a 6th-semester Applications Development Lab group project, then rebuilt and significantly enhanced with a full RAG pipeline, semantic retrieval, and conversational memory.

---

## 🌐 Live Demo

**[▶ Try StudyGenie live](https://studygenie-zv93.onrender.com)**

Deployed free on **Render** as a single Docker container (PHP + Flask + MariaDB).

> **Heads up (free tier):** the instance sleeps when idle, so the **first load can take ~30–50 seconds** to wake up. Uploaded files and accounts reset when the instance restarts — it's demo-grade hosting. Just **register a free account** to try it out.

---

## Features

| Feature | Description |
|---------|-------------|
| **Smart Document Extraction** | Extracts text from text-based PDFs, scanned PDFs (via OCR), and Word `.docx` files |
| **Document-Aware Chatbot** | Ask questions in natural language; get accurate, structured answers grounded in your document — with conversation memory for follow-ups |
| **AI Summaries** | Instant academic summary with key topics and difficulty rating |
| **Auto-Generated Quizzes** | Multiple-choice questions generated from any document, with instant scoring and explanations |
| **Study Plan & Concept Map** | Day-by-day study schedule and a visual concept graph of the document's key ideas |
| **Analytics Dashboard** | Track quiz scores, performance trends, and per-document progress with charts |
| **Secure Auth** | Bcrypt-hashed passwords, prepared statements, session management |

---

## 🧠 How It Works (RAG Pipeline)

```
Upload (PDF / scanned PDF / DOCX)
        │
        ▼
┌──────────────────────┐
│  Text Extraction     │  PyMuPDF · Tesseract OCR · python-docx
└──────────┬───────────┘
           ▼
┌──────────────────────┐
│  Chunking            │  Overlapping ~300-word chunks
└──────────┬───────────┘
           ▼
┌──────────────────────┐
│  Embeddings          │  Sentence-Transformers (all-MiniLM-L6-v2)
│  + TF-IDF index      │  cached to disk for instant restarts
└──────────┬───────────┘
           ▼
┌──────────────────────┐
│  Semantic Retrieval  │  Cosine similarity → top-K relevant chunks
└──────────┬───────────┘
           ▼
┌──────────────────────┐
│  Answer Generation   │  Groq LLM — grounded, structured, no hallucination
└──────────────────────┘
```

**Why it's different from basic "PDF chat" tools:** StudyGenie uses **semantic embeddings** to find relevant content by *meaning*, retrieves across the whole document, and forces the LLM to answer **only** from retrieved context — so answers are accurate and never fabricated.

---

## 🛠️ Tech Stack

**Backend (AI):** Python · Flask · Sentence-Transformers · scikit-learn · Groq LLM inference · PyMuPDF · Tesseract OCR
**Frontend:** PHP · HTML5 · CSS3 (glassmorphism UI) · JavaScript · Chart.js
**Database:** MySQL (MariaDB)
**Architecture:** PHP serves the UI and proxies requests to a Flask AI microservice (RAG engine)

> **LLM model is configurable.** The app calls Groq's OpenAI-compatible API using the model set in `GROQ_MODEL` (default: `openai/gpt-oss-120b`) with `GROQ_MODEL_FAST` (default: `openai/gpt-oss-20b`) as a backup. If your Groq key doesn't have access to the default, set these to any chat model your key supports (e.g. a `llama-3.x` or `qwen` model) — the code adapts automatically.

---

## 🚀 Getting Started (Local — XAMPP)

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP)
- [Python 3.11+](https://www.python.org/downloads/)
- A free [Groq API key](https://console.groq.com/keys)
- *(Optional, for scanned PDFs)* [Tesseract OCR](https://github.com/UB-Mannheim/tesseract/wiki)

### 1. Clone into your XAMPP htdocs
```bash
git clone https://github.com/Ishita-195/studygenie.git
```

### 2. Set up the database
- Start **Apache** and **MySQL** in the XAMPP Control Panel
- Open phpMyAdmin → import `database.sql`
- Copy `config.example.php` to `config.php` and adjust credentials if needed

### 3. Configure the AI server
```bash
cd python
pip install -r requirements.txt
```
Create a `.env` file in the `python/` folder:
```
GROQ_API_KEY=your_real_groq_api_key
# Optional — override the models if your key needs different ones:
# GROQ_MODEL=openai/gpt-oss-120b
# GROQ_MODEL_FAST=openai/gpt-oss-20b
```
> ⚠️ Use a **real** key here, not the placeholder — without a valid key (and a model your key can access), the app silently falls back to lower-quality non-AI output.

### 4. Start the AI server
```bash
# From the python/ folder
start_server.bat
```
The server runs at `http://localhost:5000` and auto-indexes your documents on startup (loads from disk cache in seconds).

### 5. Open the app
```
http://localhost/studygenie/authentication.php
```
Register an account, upload a document, and start learning! 🎉

---

## ☁️ Deploy for Free

StudyGenie ships with a one-container Docker setup (Apache/PHP + Flask + MariaDB) that runs on any free container host. The live demo above runs on **Render's free tier** with no credit card required.

See **[DEPLOY.md](DEPLOY.md)** for the full click-by-click guide. In short:
1. Push this repo to GitHub.
2. On [Render](https://render.com): **New → Blueprint** → select the repo (it reads `render.yaml`).
3. Set the `GROQ_API_KEY` environment variable (and `GROQ_MODEL` if your key needs a specific model).
4. Deploy — Render builds the Docker image and gives you a public HTTPS URL.

---

## Project Structure

```
studygenie/
├── authentication.php      # Login / register
├── dashboard.php           # Main dashboard with stats + document list
├── pdfupflow.php           # Upload (PDF / DOCX)
├── docdetail.php           # Document overview + inline summary
├── ai.php                  # Full AI summary + key topics
├── qa.php                  # 🤖 Chatbot (conversational Q&A)
├── quiz.php                # Auto-generated MCQ quiz
├── analysis.php            # Analytics dashboard (Chart.js)
├── pf.php                  # Profile + settings
├── theme.php               # Shared design system (glassmorphism, sidebar)
├── *_bridge.php            # PHP → Python API proxies
├── Dockerfile              # One-container deploy (Apache + Flask + MariaDB)
├── render.yaml             # Render Blueprint
├── DEPLOY.md               # Free deployment guide
└── python/
    ├── app.py              # Flask RAG engine (extraction, retrieval, generation)
    ├── requirements.txt
    └── start_server.bat    # Server launcher
```

---

## Security

- Passwords hashed with **bcrypt** (`password_hash`)
- All database queries use **prepared statements** (SQL-injection safe)
- Output escaped with `htmlspecialchars` (XSS safe)
- Secrets (`config.php`, `.env`, API keys) kept out of version control

---

## License

This project is for educational and portfolio purposes.

---

*Built with 💚 by Ishita Anand*
