import sys
import os
sys.path.insert(0, os.path.abspath(os.path.join(os.path.dirname(__file__), "..")))

import pytest
from fastapi.testclient import TestClient
from main import app
from classifier import is_noise_transaction, classify_transaction
from psite import compute_psite_features

client = TestClient(app)

def test_health():
    response = client.get("/health")
    assert response.status_code == 200
    assert response.json()["status"] == "healthy"

def test_noise_filter():
    noise_tx = {"description": "Cash-in from GCash to Maya", "amount": 1000.0, "category": "cash_in"}
    assert is_noise_transaction(noise_tx) is True

    self_transfer = {"description": "Fund transfer to own account", "amount": 500.0}
    assert is_noise_transaction(self_transfer) is True

    legit_tx = {"description": "Meralco Electric Bill Payment", "amount": 2800.0}
    assert is_noise_transaction(legit_tx) is False

def test_nlp_classifier():
    salary_tx = {"description": "Payroll Direct Deposit ABC Corp", "amount": 32000.0, "type": "credit"}
    assert classify_transaction(salary_tx) == "INCOME"

    utility_tx = {"description": "Maynilad Water Bill", "amount": 650.0, "type": "debit"}
    assert classify_transaction(utility_tx) == "UTILITY"

    debt_tx = {"description": "Home Credit Monthly Amortization", "amount": 2100.0, "type": "debit"}
    assert classify_transaction(debt_tx) == "DEBT_REPAYMENT"

def test_psite_calculation():
    sample_txs = [
        {"description": "Payroll Salary Inward", "amount": 35000.0, "type": "credit", "date": "2026-09-01T08:00:00Z"},
        {"description": "Meralco Electric Bill", "amount": 2500.0, "type": "debit", "date": "2026-09-05T08:00:00Z"},
        {"description": "Maynilad Water", "amount": 600.0, "type": "debit", "date": "2026-09-07T08:00:00Z"},
        {"description": "Home Credit loan payment", "amount": 3000.0, "type": "debit", "date": "2026-09-08T08:00:00Z"},
        {"description": "Groceries Puregold", "amount": 4000.0, "type": "debit", "date": "2026-09-10T08:00:00Z"},
    ]
    features = compute_psite_features(sample_txs)
    assert features["monthly_income"] > 0
    assert features["utility_recency_days"] <= 30
    assert features["utility_payment_count"] >= 2
    assert features["debt_to_income_ratio"] > 0
    assert features["net_disposable_cash_flow"] > 0

def test_score_endpoint():
    payload = {
        "user_id": 1,
        "transactions": [
            {"description": "Monthly Salary BPO Inc", "amount": 38000.0, "type": "credit", "date": "2026-09-01T08:00:00Z"},
            {"description": "Meralco Bill", "amount": 3200.0, "type": "debit", "date": "2026-09-04T08:00:00Z"},
            {"description": "PLDT Fiber Bill", "amount": 1899.0, "type": "debit", "date": "2026-09-05T08:00:00Z"},
            {"description": "BDO Credit Card payment", "amount": 2500.0, "type": "debit", "date": "2026-09-08T08:00:00Z"},
            {"description": "Cash-in from 7-Eleven", "amount": 5000.0, "type": "credit", "category": "cash_in"} # Should be filtered
        ]
    }
    response = client.post("/score", json=payload)
    assert response.status_code == 200
    data = response.json()
    assert data["status"] == "success"
    assert 10 <= data["credit_score"] <= 100
    assert data["approved_loan_ceiling"] >= 3000
    assert len(data["shap_factors"]) > 0
    assert "pts:" in data["shap_factors"][0]["display_text"]
