# Deploying StudyGenie for free (Render)

StudyGenie is packaged as **one Docker container** that runs the PHP UI (Apache),
the Python Flask AI service, and MariaDB together — because the Python side reads
uploaded files from the same `uploads/` folder the PHP side writes to. Running them
together keeps the app's internal `127.0.0.1:5000` calls working with no code changes.

## What you get on the free tier
- A public HTTPS URL (e.g. `https://studygenie.onrender.com`)
- No credit card required
- **Sleeps after ~15 min idle** → the first request afterwards takes ~30–50s to wake
- **Ephemeral storage**: when the container restarts, uploaded files *and* the
  database reset to a clean slate (the seeded demo login comes back). Fine for a
  demo/portfolio link; not for real users.

## Prerequisites
- A **Groq API key** — free at <https://console.groq.com> → *API Keys*
- This repo pushed to GitHub (it already is)

## Steps

1. **Push the deploy files** in this repo to GitHub (Dockerfile, `docker/`, `render.yaml`).

2. Go to <https://render.com> → sign up (GitHub login is easiest).

3. **New +** → **Web Service** → connect this GitHub repo.
   - Render auto-detects the `Dockerfile` → **Runtime: Docker**
   - **Instance type: Free**
   - (Or use **New + → Blueprint** and Render reads `render.yaml` for you.)

4. Under **Environment**, add:
   | Key             | Value                          |
   |-----------------|--------------------------------|
   | `GROQ_API_KEY`  | *your Groq key*                |
   | `GROQ_MODEL`    | `llama-3.3-70b-versatile` (optional) |

   Leave `PORT` alone — Render sets it automatically and the container reads it.

5. **Create Web Service**. First build takes ~5–8 min (installing MariaDB + Python deps).

6. Open the URL → you land on the login page.
   **Demo login:** `2305457@kiit.ac.in` / `1234`  (or create a new account).

## Using an external database instead (optional, makes data persist)
The container uses its built-in MariaDB by default. To point it at a managed MySQL
(so accounts/quiz history survive restarts), add these env vars and the built-in DB
is skipped:

```
DB_HOST = <host>
DB_PORT = 3306
DB_USER = <user>
DB_PASS = <password>
DB_NAME = studygenie_db
```

Then import `docker/seed.sql` (or the original `database.sql`) into that database once.
Free MySQL options: TiDB Cloud Serverless, Aiven, Clever Cloud. (Uploaded *files*
are still ephemeral unless you also attach a paid persistent disk.)

## Run it locally with Docker (same image)
```bash
docker build -t studygenie:local .
docker run --rm -p 8090:10000 -e GROQ_API_KEY=your_key studygenie:local
# open http://localhost:8090
```

## Notes / limits
- `sentence-transformers` and Tesseract OCR are omitted from the deploy image to fit
  free-tier RAM; the app falls back to TF-IDF retrieval and skips OCR on scanned PDFs.
  (These are optional imports in `python/app.py`.)
- Very large PDFs may momentarily push memory past the 512 MB free limit during
  indexing. If a request dies, try a smaller document or upgrade the instance.
