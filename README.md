# StudyGenie - AI-Powered Learning Platform

An intelligent document-based Q&A and quiz generation system built with PHP, Python Flask, and AI.

## Features

- **PDF Upload & Processing** - Upload study materials in PDF format
- **AI-Powered Q&A** - Ask questions about your uploaded documents
- **Quiz Generation** - Auto-generate MCQ quizzes from document content
- **User Authentication** - Secure login/registration system
- **Dashboard** - Track your uploaded documents and progress

## Tech Stack

### Frontend
- PHP 8.x
- HTML5/CSS3
- JavaScript (Vanilla)

### Backend
- Python 3.11+
- Flask (REST API)
- TF-IDF (Text similarity)
- PyMuPDF (PDF extraction)

### Database
- MySQL (via XAMPP)

### AI/ML
- Groq API (LLaMA 3 for text generation)
- scikit-learn (TF-IDF vectorization)

## Prerequisites

- XAMPP (Apache + MySQL)
- Python 3.11 or higher
- Groq API Key (free at https://console.groq.com)

## Installation

### 1. Clone the repository
```bash
git clone https://github.com/yourusername/studygenie.git
cd studygenie
```

### 2. Set up the database
1. Start XAMPP (Apache + MySQL)
2. Open phpMyAdmin (http://localhost/phpmyadmin)
3. Create database: `studygenie_db`
4. Import the SQL schema (if provided)

### 3. Configure PHP
1. Copy `config.example.php` to `config.php`
2. Update database credentials if needed

### 4. Install Python dependencies
```bash
cd python
pip install -r ../requirements.txt
```

### 5. Set up Groq API Key
```bash
# Windows PowerShell
$env:GROQ_API_KEY="your_groq_api_key_here"

# Windows CMD
set GROQ_API_KEY=your_groq_api_key_here

# Linux/Mac
export GROQ_API_KEY="your_groq_api_key_here"
```

### 6. Start the Flask server
```bash
cd python
python app.py
```

### 7. Access the application
Open http://localhost/ad_lab in your browser

## Project Structure

```
ad_lab/
├── python/
│   └── app.py              # Flask API server
├── uploads/                # User uploaded PDFs (gitignored)
├── authentication.php      # Login/Register
├── dashboard.php           # Main dashboard
├── pdfupflow.php          # PDF upload handler
├── qa.php                  # Q&A interface
├── quiz.php               # Quiz interface
├── ask_bridge.php         # PHP-Python bridge for Q&A
├── quiz_bridge.php        # PHP-Python bridge for Quiz
├── config.php             # Database config (gitignored)
├── config.example.php     # Database config template
├── navbar.php             # Navigation component
└── README.md              # This file
```

## API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/status` | GET | Server health check |
| `/upload-and-index` | POST | Index a PDF file |
| `/ask` | POST | Ask a question |
| `/ask-stream` | POST | Ask with streaming response |
| `/generate-quiz` | POST | Generate MCQ quiz |

## Usage

1. **Register/Login** at the authentication page
2. **Upload a PDF** from the dashboard
3. **Ask Questions** about your document
4. **Take Quizzes** generated from your materials

## Environment Variables

| Variable | Description |
|----------|-------------|
| `GROQ_API_KEY` | Your Groq API key for AI responses |

## License

MIT License - feel free to use for educational purposes.

## Author

Built as an educational project for AI-powered learning.
