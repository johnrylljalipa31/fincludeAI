# Finclude-AI Deployment & Orchestration Guide

This guide details local orchestration, webhook bridging via ngrok, and production cloud deployment.

---

## 1. Local Multi-Service Orchestration

### Service Port Map
| Service | Technology | Port | Command |
|---|---|---|---|
| **Python AI Microservice** | FastAPI / Uvicorn | `8000` | `cd python-ai && .\venv\Scripts\uvicorn main:app --port 8000 --reload` |
| **Backend REST API** | Laravel 11 / PHP | `8001` | `cd backend && php artisan serve --port=8001` |
| **Mobile App** | Flutter | Web / Android | `cd mobile-app && flutter run -d chrome` |

### Step-by-Step Launch Procedure

#### Terminal 1: Python AI Microservice (Port 8000)
```powershell
cd c:\Finclude-AI\python-ai
.\venv\Scripts\python.exe -m uvicorn main:app --port 8000 --reload
```
Health Check: `http://127.0.0.1:8000/health` (returns `{"status":"healthy"}`)

#### Terminal 2: Laravel Backend (Port 8001)
```powershell
cd c:\Finclude-AI\backend
php artisan serve --port=8001
```
Route Check: `http://127.0.0.1:8001/api/dashboard`

#### Terminal 3: Mobile App (Flutter)
```powershell
cd c:\Finclude-AI\mobile-app
flutter run -d chrome
```

---

## 2. Webhook Tunneling via ngrok

To receive real-time data sync callbacks (`TRANSACTIONS_ADDED`) from Smile API during local development:

### Expose Laravel Port 8001:
```bash
ngrok http 8001
```

You will receive an HTTPS forwarding URL like:
`https://abc123-def456.ngrok-free.app`

### Register in Smile Developer Portal:
1. Log in to [Smile Developer Portal](https://portal.smileapi.io/).
2. Navigate to **Webhooks** > **Add Webhook Endpoint**.
3. Set URL to:
   ```
   https://abc123-def456.ngrok-free.app/api/webhook/smile
   ```
4. Subscribe to the event: `TRANSACTIONS_ADDED` (and `ACCOUNTS_CONNECTED`).
5. Click **Send Test Event** or link a sandbox account (Username: `George`, Password: `123456`).

---

## 3. Production Cloud Deployment (Stage 5)

### A. Smile API Production Upgrade
1. In the Smile Developer Portal, submit business verification details (SEC registration / DTI, AML compliance certification).
2. Request upgrade from **Sandbox** to **Production**.
3. Replace `.env` variables in production:
   ```env
   SMILE_API_KEY=live_your_production_key
   SMILE_API_SECRET=live_your_production_secret
   SMILE_BASE_URL=https://api.smileapi.io/v1
   ```

### B. Python AI Microservice Deployment
Deploy using Docker or serverless containers (e.g. AWS ECS / Railway / Modal / Cloud Run):

```dockerfile
# python-ai/Dockerfile
FROM python:3.11-slim
WORKDIR /app
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt
COPY . .
EXPOSE 8000
CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8000"]
```

### C. Laravel Cloud Deployment & Worker Queues
- Host on AWS EC2, DigitalOcean Droplet, or Laravel Forge with Nginx, PHP 8.2+ FPM, and MySQL 8.0.
- Enforce SSL/TLS 1.3 certificates via Let's Encrypt (`certbot`).
- **Redis Queue Worker for Sub-200ms Webhook Latency**:
  Set `.env`:
  ```env
  QUEUE_CONNECTION=redis
  REDIS_HOST=127.0.0.1
  REDIS_PORT=6379
  ```
  Run the background queue daemon with Supervisor:
  ```bash
  php artisan queue:work redis --tries=3 --timeout=90
  ```
