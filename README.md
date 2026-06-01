# StudyGenie - AI-Powered Academic Learning Platform

An AI-driven academic learning platform implementing OCR, vector embeddings, and Retrieval-Augmented Generation (RAG) for intelligent document-based Q&A and automated quiz generation.

[![Python](https://img.shields.io/badge/Python-3.11+-blue.svg)](https://python.org)
[![Flask](https://img.shields.io/badge/Flask-2.0+-green.svg)](https://flask.palletsprojects.com)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

## Overview

StudyGenie transforms how students interact with academic materials by leveraging cutting-edge AI technologies to extract, understand, and generate insights from educational documents.

### Key Capabilities

- **Intelligent Document Processing** - Extract text from PDFs using advanced OCR and text extraction
- **Semantic Search** - Find relevant content using TF-IDF vectorization and cosine similarity
- **RAG-based Q&A** - Answer questions using Retrieval-Augmented Generation with LLM integration
- **Automated Quiz Generation** - Generate MCQ assessments from document content
- **Real-time Streaming** - Stream AI responses for better user experience

## Architecture

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   Frontend      │────▶│   PHP Bridge    │────▶│   Flask API     │
│   (PHP/JS)      │     │   (REST)        │     │   (Python)      │
└─────────────────┘     └─────────────────┘     └─────────────────┘
                                                        │
                        ┌───────────────────────────────┼───────────────────────────────┐
                        │                               │                               │
                        ▼                               ▼                               ▼
                ┌───────────────┐             ┌───────────────┐             ┌───────────────┐
                │  PDF Parser   │             │  TF-IDF Index │             │   Groq LLM    │
                │  (PyMuPDF)    │             │  (sklearn)    │             │   (LLaMA 3)   │
                └───────────────┘             └───────────────┘             └───────────────┘
```

## Tech Stack

| Layer | Technology | Purpose |
|-------|------------|---------|
| **Frontend** | PHP 8.x, JavaScript | User interface & routing |
| **API Server** | Flask, Flask-CORS | RESTful API endpoints |
| **Document Processing** | PyMuPDF (fitz) | PDF text extraction |
| **Text Vectorization** | scikit-learn TF-IDF | Document indexing & similarity |
| **LLM Integration** | Groq API (LLaMA 3) | Natural language generation |
| **Database** | MySQL | User data & document metadata |

## Features

### 1. Document Upload & Processing
- Supports PDF format up to 100MB
- Fast text extraction using PyMuPDF
- Automatic chunking with configurable overlap
- Real-time indexing status

### 2. RAG-based Question Answering
- Semantic search using TF-IDF cosine similarity
- Top-k chunk retrieval for context
- LLM-powered answer generation
- Fallback to raw context if LLM unavailable

### 3. Automated Quiz Generation
- MCQ generation from document content
- Configurable number of questions
- JSON-formatted quiz output
- Answer validation support

### 4. User Management
- Secure authentication system
- Document history tracking
- Quiz score persistence

## Installation

### Prerequisites

- PHP 8.0+ with Apache (XAMPP recommended)
- Python 3.11+
- MySQL 8.0+
- Groq API Key ([Get free key](https://console.groq.com))

### Step 1: Clone Repository

```bash
git clone https://github.com/yourusername/studygenie.git
cd studygenie
```

### Step 2: Database Setup

```sql
CREATE DATABASE studygenie_db;
USE studygenie_db;

-- Run the provided SQL schema
SOURCE database.sql;
```

### Step 3: Configure PHP

```bash
cp config.example.php config.php
# Edit config.php with your database credentials
```

### Step 4: Install Python Dependencies

```bash
pip install -r requirements.txt
```

### Step 5: Set Environment Variables

```bash
# Windows PowerShell
$env:GROQ_API_KEY="your_api_key_here"

# Linux/Mac
export GROQ_API_KEY="your_api_key_here"
```

### Step 6: Start Services

```bash
# Terminal 1: Start Apache/MySQL (via XAMPP)

# Terminal 2: Start Flask API
cd python
python app.py
```

### Step 7: Access Application

Open http://localhost/ad_lab in your browser

## API Reference

### Health Check
```http
GET /status
```
**Response:**
```json
{
  "status": "running",
  "indexed_files": ["document.pdf"],
  "groq_available": true
}
```

### Index Document
```http
POST /upload-and-index
Content-Type: application/json

{"filename": "document.pdf"}
```
**Response:**
```json
{
  "status": "indexed",
  "filename": "document.pdf",
  "chunks": 1675,
  "time_seconds": 3.8
}
```

### Ask Question
```http
POST /ask
Content-Type: application/json

{
  "question": "What is machine learning?",
  "filename": "document.pdf"
}
```
**Response:**
```json
{
  "status": "ok",
  "answer": "Machine learning is...",
  "sources": ["document.pdf"],
  "confidence": 85
}
```

### Generate Quiz
```http
POST /generate-quiz
Content-Type: application/json

{
  "filename": "document.pdf",
  "num_questions": 5
}
```

## Project Structure

```
studygenie/
├── python/
│   ├── app.py                 # Flask API server
│   └── __pycache__/
├── uploads/                   # User uploaded PDFs (gitignored)
├── authentication.php         # User login/registration
├── dashboard.php              # Main dashboard
├── pdfupflow.php             # PDF upload handler
├── qa.php                    # Q&A interface
├── quiz.php                  # Quiz interface
├── ask_bridge.php            # PHP-Flask bridge for Q&A
├── quiz_bridge.php           # PHP-Flask bridge for Quiz
├── config.php                # Database config (gitignored)
├── config.example.php        # Config template
├── navbar.php                # Navigation component
├── requirements.txt          # Python dependencies
├── .gitignore
└── README.md
```

## Performance

| Metric | Value |
|--------|-------|
| PDF Indexing Speed | ~4 seconds for 7MB PDF |
| Chunks per Document | ~1,500-2,000 |
| Query Response Time | <2 seconds |
| Supported File Size | Up to 100MB |

## Future Enhancements

- [ ] Integration with Google Gemini API
- [ ] Supabase for vector storage
- [ ] FastAPI migration for async support
- [ ] OCR for scanned documents (Tesseract)
- [ ] Multi-language support
- [ ] Document summarization
- [ ] Flashcard generation

## Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

- [Groq](https://groq.com) - Fast LLM inference
- [PyMuPDF](https://pymupdf.readthedocs.io) - PDF processing
- [scikit-learn](https://scikit-learn.org) - Machine learning utilities
- [Flask](https://flask.palletsprojects.com) - Web framework

---

**Built with passion for education and AI**
