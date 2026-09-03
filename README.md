# StudyGenie - AI-Powered Document Learning Platform

A document-aware learning platform that turns any PDF or Word file into AI-generated summaries, a grounded chatbot, and auto-generated quizzes — powered by a Retrieval-Augmented Generation (RAG) pipeline with semantic search and Groq LLM inference.

<div align="center">
  <a href="https://studygenie-zv93.onrender.com">
    <img src="https://img.shields.io/badge/▶_Try_the_Live_Demo-2EA44F?style=for-the-badge&logo=render&logoColor=white" alt="Live Demo"/>
  </a>
</div>

<p align="center"><i>Hosted free on Render — the first request may take ~30–50s to wake the instance. Just register a free account to explore.</i></p>

## Project Overview

StudyGenie is an AI study assistant that:
- ✅ Extracts text from PDFs, scanned PDFs (OCR), and Word `.docx` files
- ✅ Generates grounded academic summaries with key topics and difficulty
- ✅ Answers questions via a document-aware chatbot with conversation memory
- ✅ Auto-generates multiple-choice quizzes with explanations and scoring
- ✅ Builds day-by-day study plans and visual concept maps
- ✅ Tracks progress with an analytics dashboard (Chart.js)

### Architecture

```
Document (PDF / scanned PDF / DOCX)
    ↓
[Extraction] → PyMuPDF · Tesseract OCR · python-docx
    ↓
[Chunking] → Overlapping ~300-word chunks
    ↓
[Indexing] → Sentence-Transformers embeddings + TF-IDF (cached to disk)
    ↓
[Retrieval] → Cosine similarity → top-K relevant chunks
    ↓
[Generation] → Groq LLM — grounded, structured, no hallucination
    ↓
[Frontend] → PHP UI (summaries · chatbot · quiz · analytics)
```

The PHP frontend serves the UI and proxies requests to a Flask AI microservice; the LLM is forced to answer **only** from retrieved context, so responses stay accurate and never fabricated.

---

## Quick Setup (Local — XAMPP)

### Step 1: Clone into your XAMPP htdocs

```bash
git clone https://github.com/Ishita-195/studygenie.git
```

### Step 2: Set up the database

- Start **Apache** and **MySQL** in the XAMPP Control Panel
- Open phpMyAdmin → import `database.sql`
- Copy `config.example.php` to `config.php` and adjust credentials if needed

### Step 3: Install the AI server dependencies

```bash
cd python
pip install -r requirements.txt
```

### Step 4: Configure your Groq key

Create a `.env` file in the `python/` folder:

```
GROQ_API_KEY=your_real_groq_api_key
# Optional — override if your key needs different models:
# GROQ_MODEL=openai/gpt-oss-120b
# GROQ_MODEL_FAST=openai/gpt-oss-20b
```

> ⚠️ Use a **real** key and a model your key can access. Without a valid key/model, the app silently falls back to lower-quality non-AI output.

### Step 5: Start the AI server

```bash
# From the python/ folder
start_server.bat
```

Expected output:

```
============================================================
  StudyGenie AI Server
  Server ready at http://0.0.0.0:5000
  [STARTUP] ✅ Groq client ready
============================================================
```

### Step 6: Open the app

```
http://localhost/studygenie/authentication.php
```

Register an account, upload a document, and start learning.

---

## Deploy for Free (Render)

StudyGenie ships with a one-container Docker setup (Apache/PHP + Flask + MariaDB) so the PHP UI and Python AI service share the same uploads folder — exactly like the XAMPP setup, but hosted.

1. Push this repo to GitHub.
2. On [Render](https://render.com): **New → Blueprint** → select the repo (it reads `render.yaml`).
3. Set `GROQ_API_KEY` (and `GROQ_MODEL` if your key needs a specific model).
4. Deploy — Render builds the image and returns a public HTTPS URL.

Full walkthrough: **[DEPLOY.md](DEPLOY.md)**

---

## Tech Stack

| Layer | Technologies |
|-------|--------------|
| **AI Backend** | Python · Flask · Sentence-Transformers · scikit-learn · Groq LLM · PyMuPDF · Tesseract OCR · python-docx |
| **Frontend** | PHP · HTML5 · CSS3 (glassmorphism) · JavaScript · Chart.js |
| **Database** | MySQL (MariaDB) |
| **Deployment** | Docker · Render (single-container: Apache + Flask + MariaDB) |

> **Model is configurable** via `GROQ_MODEL` / `GROQ_MODEL_FAST` (defaults: `openai/gpt-oss-120b` and `openai/gpt-oss-20b`). Set them to any chat model your Groq key supports.

---

## File Structure

```
studygenie/
├── authentication.php      # Login / register
├── dashboard.php           # Dashboard with stats + document list
├── pdfupflow.php           # Upload (PDF / DOCX)
├── docdetail.php           # Document overview + inline summary
├── ai.php                  # Full AI summary + key topics
├── qa.php                  # Chatbot (conversational Q&A)
├── quiz.php                # Auto-generated MCQ quiz
├── analysis.php            # Analytics dashboard (Chart.js)
├── pf.php                  # Profile + settings
├── theme.php               # Shared design system (glassmorphism)
├── *_bridge.php            # PHP → Python API proxies
│
├── Dockerfile              # One-container deploy (Apache + Flask + MariaDB)
├── render.yaml             # Render Blueprint
├── DEPLOY.md               # Free deployment guide
├── database.sql            # Schema + seed data
│
└── python/
    ├── app.py              # Flask RAG engine (extraction, retrieval, generation)
    ├── requirements.txt    # Python dependencies
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

For educational and portfolio purposes.

---

*Built by Ishita Anand*
