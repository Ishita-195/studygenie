import os
import time
import json
from flask import Flask, request, jsonify, Response
from flask_cors import CORS
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
import numpy as np
import fitz  # PyMuPDF - much faster than pdfplumber
from groq import Groq

app = Flask(__name__)
CORS(app)

# --- CORRECT PATH ---
UPLOAD_FOLDER = r'C:\Users\KIIT0001\Desktop\xampp\htdocs\ad_lab\uploads'
GROQ_MODEL = "llama3-8b-8192"

# Per-file storage: { filename: { "chunks": [...], "vectorizer": tfidf, "matrix": tfidf_matrix } }
per_file_data = {}

# Initialize Groq with timeout
groq_client = None
api_key = os.getenv("GROQ_API_KEY")
if api_key:
    try:
        groq_client = Groq(api_key=api_key, timeout=30.0)
        print("Groq Client Initialized (timeout=30s).")
    except Exception as e:
        print(f"Groq init error: {e}")
else:
    print("GROQ_API_KEY not found. Will return raw context.")

print(f"Upload folder: {UPLOAD_FOLDER}")
print(f"Folder exists: {os.path.exists(UPLOAD_FOLDER)}")
if os.path.exists(UPLOAD_FOLDER):
    pdfs = [f for f in os.listdir(UPLOAD_FOLDER) if f.endswith('.pdf')]
    print(f"PDFs found: {pdfs}")


@app.route('/upload-and-index', methods=['POST'])
def upload_and_index():
    """Index a single PDF file using TF-IDF (fast, no GPU needed)."""
    global per_file_data
    
    data = request.get_json()
    raw_filename = data.get('filename', '')
    filename = os.path.basename(raw_filename).strip()
    
    print(f"[INDEX] Request to index: '{filename}'")
    
    if not filename:
        return jsonify({'error': 'No filename provided'}), 400
    
    filepath = os.path.join(UPLOAD_FOLDER, filename)
    print(f"[INDEX] Looking for file at: '{filepath}'")
    print(f"[INDEX] File exists: {os.path.exists(filepath)}")
    
    if not os.path.exists(filepath):
        files_in_folder = []
        if os.path.exists(UPLOAD_FOLDER):
            files_in_folder = [f for f in os.listdir(UPLOAD_FOLDER) if f.endswith('.pdf')]
        print(f"[INDEX] ERROR: File not found. Available PDFs: {files_in_folder}")
        return jsonify({
            'error': f'File not found: {filename}',
            'filepath_checked': filepath,
            'files_available': files_in_folder
        }), 404
    
    start_time = time.time()
    
    # Extract text using PyMuPDF (fitz) - much faster than pdfplumber
    chunks = []
    try:
        doc = fitz.open(filepath)
        print(f"[INDEX] PDF has {len(doc)} pages")
        all_text = ""
        for page in doc:
            all_text += page.get_text() + " "
        doc.close()
        
        # Split into chunks
        words = all_text.split()
        for i in range(0, len(words), 250):
            chunk = ' '.join(words[i:i+300])
            if chunk.strip():
                chunks.append(chunk.strip())
    except Exception as e:
        print(f"[INDEX] PDF extraction error: {e}")
        return jsonify({'error': f'PDF extraction failed: {str(e)}'}), 500
    
    print(f"[INDEX] Extracted {len(chunks)} chunks")
    
    if not chunks:
        return jsonify({'error': 'No text extracted from PDF'}), 400
    
    # Build TF-IDF index (FAST - seconds instead of minutes)
    try:
        vectorizer = TfidfVectorizer(max_features=5000, stop_words='english')
        tfidf_matrix = vectorizer.fit_transform(chunks)
        
        per_file_data[filename] = {
            "chunks": chunks,
            "vectorizer": vectorizer,
            "matrix": tfidf_matrix
        }
        
        elapsed = time.time() - start_time
        print(f"[INDEX] Successfully indexed '{filename}' with {len(chunks)} chunks in {elapsed:.2f}s")
        
        return jsonify({
            'status': 'indexed',
            'filename': filename,
            'chunks': len(chunks),
            'time_seconds': round(elapsed, 2)
        })
    except Exception as e:
        print(f"[INDEX] TF-IDF error: {e}")
        return jsonify({'error': f'Indexing failed: {str(e)}'}), 500


@app.route("/ask", methods=["POST"])
def ask():
    """Answer a question using TF-IDF similarity search."""
    global per_file_data, groq_client
    
    data = request.get_json()
    question = data.get("question", "")
    raw_filename = data.get("filename", "")
    filename = os.path.basename(raw_filename).strip()
    
    print(f"[ASK] filename='{filename}', question='{question[:50]}...'")
    print(f"[ASK] Indexed files: {list(per_file_data.keys())}")
    
    if not filename or filename not in per_file_data:
        return jsonify({
            "status": "error",
            "answer": f"File '{filename}' not indexed. Indexed: {list(per_file_data.keys())}",
            "sources": [],
            "confidence": 0,
            "indexed_files": list(per_file_data.keys())
        })
    
    file_data = per_file_data[filename]
    chunks = file_data["chunks"]
    vectorizer = file_data["vectorizer"]
    tfidf_matrix = file_data["matrix"]
    
    # TF-IDF similarity search
    query_vec = vectorizer.transform([question])
    scores = cosine_similarity(query_vec, tfidf_matrix).flatten()
    top_indices = scores.argsort()[-3:][::-1]
    relevant_chunks = [chunks[i] for i in top_indices if i < len(chunks)]
    context = "\n---\n".join(relevant_chunks)
    
    print(f"[ASK] Retrieved {len(relevant_chunks)} chunks")
    
    # Try Groq, fallback to raw context
    if groq_client:
        try:
            completion = groq_client.chat.completions.create(
                model=GROQ_MODEL,
                messages=[
                    {"role": "system", "content": "Answer based on the context. If info not found, say so."},
                    {"role": "user", "content": f"Context:\n{context}\n\nQuestion: {question}"}
                ],
                temperature=0.5,
                max_tokens=500
            )
            answer = completion.choices[0].message.content
        except Exception as e:
            print(f"[GROQ ERROR] {e}")
            answer = "Based on the document:\n\n" + "\n\n".join(relevant_chunks[:2])
    else:
        answer = "Based on the document:\n\n" + "\n\n".join(relevant_chunks[:2])
    
    return jsonify({
        "status": "ok",
        "answer": answer,
        "sources": [filename],
        "confidence": 85
    })


@app.route("/ask-stream", methods=["POST"])
def ask_stream():
    """Stream answer using TF-IDF similarity search."""
    global per_file_data, groq_client
    
    data = request.get_json()
    question = data.get("question", "")
    raw_filename = data.get("filename", "")
    filename = os.path.basename(raw_filename).strip()
    
    print(f"[ASK-STREAM] filename='{filename}', question='{question[:50]}...'")
    print(f"[ASK-STREAM] Indexed files: {list(per_file_data.keys())}")
    
    if not filename or filename not in per_file_data:
        indexed = list(per_file_data.keys())
        def err_gen():
            msg = json.dumps({"token": f"File '{filename}' not indexed. Indexed: {indexed}"})
            yield f'data: {msg}\n\n'
            yield 'data: [DONE]\n\n'
        return Response(err_gen(), mimetype='text/event-stream')
    
    file_data = per_file_data[filename]
    chunks = file_data["chunks"]
    vectorizer = file_data["vectorizer"]
    tfidf_matrix = file_data["matrix"]
    
    # TF-IDF similarity search
    query_vec = vectorizer.transform([question])
    scores = cosine_similarity(query_vec, tfidf_matrix).flatten()
    top_indices = scores.argsort()[-3:][::-1]
    relevant_chunks = [chunks[i] for i in top_indices if i < len(chunks)]
    context = "\n---\n".join(relevant_chunks)
    
    print(f"[ASK-STREAM] Retrieved {len(relevant_chunks)} chunks")
    
    def generate():
        if groq_client:
            try:
                stream = groq_client.chat.completions.create(
                    model=GROQ_MODEL,
                    messages=[
                        {"role": "system", "content": "Answer based on the context. If info not found, say so."},
                        {"role": "user", "content": f"Context:\n{context}\n\nQuestion: {question}"}
                    ],
                    stream=True,
                    max_tokens=500
                )
                for chunk in stream:
                    if chunk.choices[0].delta.content:
                        token = chunk.choices[0].delta.content
                        token_msg = json.dumps({"token": token})
                        yield f'data: {token_msg}\n\n'
            except Exception as e:
                print(f"[GROQ STREAM ERROR] {e}")
                # Fallback: stream raw chunks token by token
                fallback_text = "Based on the document:\n\n" + "\n\n".join(relevant_chunks[:2])
                words = fallback_text.split()
                for word in words:
                    token_msg = json.dumps({"token": word + " "})
                    yield f'data: {token_msg}\n\n'
        else:
            # No Groq: stream raw chunks
            fallback_text = "Based on the document:\n\n" + "\n\n".join(relevant_chunks[:2])
            words = fallback_text.split()
            for word in words:
                token_msg = json.dumps({"token": word + " "})
                yield f'data: {token_msg}\n\n'
        
        yield 'data: [DONE]\n\n'
    
    return Response(generate(), mimetype='text/event-stream')


@app.route("/generate-quiz", methods=["POST"])
def generate_quiz():
    """Generate quiz using TF-IDF indexed data."""
    global per_file_data, groq_client
    
    data = request.get_json()
    filename = os.path.basename(data.get("filename", "")).strip()
    num_questions = data.get("num_questions", 5)
    
    if not filename or filename not in per_file_data:
        return jsonify({"error": f"File not indexed: {filename}", "questions": []})
    
    chunks = per_file_data[filename]["chunks"]
    context = "\n---\n".join(chunks[:10])
    
    if not groq_client:
        return jsonify({"error": "No Groq API key", "questions": []})
    
    prompt = f"""Generate {num_questions} MCQ questions. Return ONLY JSON array:
[{{"question": "...", "options": ["A", "B", "C", "D"], "answer": 0}}]

Content:
{context}"""
    
    try:
        resp = groq_client.chat.completions.create(
            model=GROQ_MODEL,
            messages=[{"role": "user", "content": prompt}],
            temperature=0.7,
            max_tokens=1000
        )
        text = resp.choices[0].message.content.strip()
        if "```" in text:
            text = text.split("```")[1].replace("json", "").strip()
        questions = json.loads(text)
        return jsonify({"status": "ok", "questions": questions})
    except Exception as e:
        print(f"[QUIZ ERROR] {e}")
        return jsonify({"error": str(e), "questions": []})


@app.route("/status", methods=["GET"])
def status():
    """Server status."""
    print(f"[STATUS] Indexed files: {list(per_file_data.keys())}")
    return jsonify({
        "status": "running",
        "indexed_files": list(per_file_data.keys()),
        "groq_available": groq_client is not None,
        "upload_folder": UPLOAD_FOLDER
    })


if __name__ == "__main__":
    print("=" * 50)
    print("StudyGenie Flask Server (TF-IDF - Fast Indexing)")
    print(f"Upload folder: {UPLOAD_FOLDER}")
    print("=" * 50)
    app.run(port=5000, host='0.0.0.0')
