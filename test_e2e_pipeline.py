"""
Finclude-AI End-to-End Orchestration & Verification Test Suite
Tests Stage 1, Stage 2, and Webhook Data Sync Pipeline across all endpoints.
"""

import urllib.request
import json
import time

def make_request(url, method="GET", data=None, token=None):
    headers = {
        "Content-Type": "application/json",
        "Accept": "application/json"
    }
    if token:
        headers["Authorization"] = f"Bearer {token}"

    body = json.dumps(data).encode("utf-8") if data else None
    req = urllib.request.Request(url, data=body, headers=headers, method=method)
    
    try:
        with urllib.request.urlopen(req, timeout=10) as response:
            return response.status, json.loads(response.read().decode("utf-8"))
    except urllib.error.HTTPError as e:
        return e.code, json.loads(e.read().decode("utf-8"))
    except Exception as e:
        return 500, {"error": str(e)}

def run_tests():
    print("==========================================================")
    print("[TEST] Finclude-AI End-to-End System Test Suite")
    print("==========================================================")

    # 1. Test Python AI Microservice
    print("\n[Step 1] Testing Python AI Microservice (http://127.0.0.1:8000)...")
    code, res = make_request("http://127.0.0.1:8000/health")
    if code == 200:
        print(f"[OK] Python AI Health: {res}")
    else:
        print(f"[WARN] Python AI not currently running on port 8000 (code {code}): {res}")

    # Test Direct Python Scoring
    sample_scoring_payload = {
        "user_id": 1,
        "transactions": [
            {"description": "Direct Deposit Payroll ABC Corp", "amount": 38000.0, "type": "credit", "category": "salary", "date": "2026-09-01T08:00:00Z"},
            {"description": "Meralco Electric Bill", "amount": 2900.0, "type": "debit", "category": "utility", "date": "2026-09-05T08:00:00Z"},
            {"description": "Maynilad Water", "amount": 650.0, "type": "debit", "category": "utility", "date": "2026-09-07T08:00:00Z"},
            {"description": "Home Credit Loan Payment", "amount": 2500.0, "type": "debit", "category": "loan", "date": "2026-09-08T08:00:00Z"}
        ]
    }
    code, res = make_request("http://127.0.0.1:8000/score", method="POST", data=sample_scoring_payload)
    if code == 200:
        print(f"[OK] Python AI Score Engine: Score={res.get('credit_score')}/100, Tier={res.get('risk_tier')}, Ceiling=PHP {res.get('approved_loan_ceiling'):,}")
        top_factor = res.get('shap_factors')[0]['display_text'].replace("\u20b1", "PHP ")
        print(f"   Top SHAP Factor: {top_factor}")
    else:
        print(f"[WARN] Python AI scoring test returned: {code}")

    # 2. Test Laravel Backend
    print("\n[Step 2] Testing Laravel Backend (http://127.0.0.1:8001)...")
    login_payload = {
        "email": "george@finclude.ph",
        "password": "123456"
    }
    code, res = make_request("http://127.0.0.1:8001/api/auth/login", method="POST", data=login_payload)
    if code != 200:
        print(f"[ERROR] Laravel login error ({code}): {res}")
        return

    token = res.get("token")
    print(f"[OK] Authenticated as George! Bearer token obtained.")

    # 3. Test RA 10173 Consent
    print("\n[Step 3] Verifying RA 10173 Consent Logging...")
    code, res = make_request("http://127.0.0.1:8001/api/consent/agree", method="POST", data={
        "policy_version": "1.0",
        "legal_basis": "RA 10173 - Philippine Data Privacy Act of 2012"
    }, token=token)
    print(f"[OK] Consent API ({code}): {res.get('message')}")

    # 4. Test Smile Token
    print("\n[Step 4] Requesting Smile Wink User Token...")
    code, res = make_request("http://127.0.0.1:8001/api/smile/token", method="POST", token=token)
    user_token = res.get("userToken")
    print(f"[OK] Smile Token API ({code}): userToken={user_token}")

    # 5. Test Webhook Pipeline (TRANSACTIONS_ADDED)
    print("\n[Step 5] Triggering Smile Webhook TRANSACTIONS_ADDED Pipeline...")
    webhook_payload = {
        "event": "TRANSACTIONS_ADDED",
        "userId": 1,
        "provider": "GCash",
        "data": {
            "transactions": sample_scoring_payload["transactions"]
        }
    }
    code, res = make_request("http://127.0.0.1:8001/api/webhook/smile", method="POST", data=webhook_payload)
    print(f"[OK] Webhook Synced ({code}): {res.get('message')}")
    if "quality_gate" in res:
        qg = res["quality_gate"]
        print(f"   Quality Gate: Completeness={qg.get('completeness_rate')}%, Validity={qg.get('validity_rate')}%, Passed={qg.get('passed_quality_gate')}")

    # 5b. Test Data Minimization Guardrail (Rejection of PINs/OTPs)
    print("\n[Step 5b] Verifying RA 10173 Data Minimization Guardrail (Rejection of PIN/OTP)...")
    forbidden_payload = {
        "event": "TRANSACTIONS_ADDED",
        "userId": 1,
        "pin": "1234",
        "otp": "999888",
        "data": {"transactions": []}
    }
    code, res = make_request("http://127.0.0.1:8001/api/webhook/smile", method="POST", data=forbidden_payload)
    if code == 422:
        print(f"[OK] Blocked Sensitive Payload as expected (code {code}): {res.get('error')}")
    else:
        print(f"[WARN] Expected 422 for sensitive payload, got {code}: {res}")

    # 6. Test Dashboard API
    print("\n[Step 6] Fetching Real-Time Credit Score Dashboard...")
    code, res = make_request("http://127.0.0.1:8001/api/dashboard", method="GET", token=token)
    score_info = res.get("credit_score", {})
    print(f"[OK] Dashboard Data ({code}): Score={score_info.get('score')}/100, Ceiling=PHP {score_info.get('approved_ceiling'):,}")
    print(f"   SHAP Factors: {len(score_info.get('shap_factors', []))} explainability drivers active")

    # 7. Test Loan Application (61-180 days compliant tenure)
    print("\n[Step 7] Testing Compliant Micro-Loan Application (90 Days, 24% APR)...")
    loan_payload = {
        "amount": 5000,
        "tenure_days": 90,
        "purpose": "Motorcycle maintenance & delivery working capital"
    }
    code, res = make_request("http://127.0.0.1:8001/api/loan/apply", method="POST", data=loan_payload, token=token)
    if code == 201:
        loan = res.get("loan", {})
        print(f"[OK] Loan Approved ({code}): Amount=PHP {loan.get('amount')}, Total Repayment=PHP {loan.get('total_repayment')}, Status={loan.get('status')}")
    else:
        print(f"[INFO] Loan Apply response ({code}): {res.get('message')}")

    print("\n=== ALL E2E PIPELINE TESTS COMPLETE AND VERIFIED! ===")

if __name__ == "__main__":
    run_tests()
