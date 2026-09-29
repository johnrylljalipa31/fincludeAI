import sys
import os
sys.path.insert(0, os.path.abspath(os.path.join(os.path.dirname(__file__), "..")))

import pytest
from features import extract_12_features, FEATURE_NAMES
from model import engine

def test_12_features_extraction():
    transactions = [
        {"description": "Grab Driver Weekly Payout", "amount": 9500.0, "type": "credit", "category": "income", "date": "2026-09-01T08:00:00Z"},
        {"description": "Grab Driver Weekly Payout", "amount": 8800.0, "type": "credit", "category": "income", "date": "2026-09-08T08:00:00Z"},
        {"description": "Meralco Electric Bill", "amount": 2200.0, "type": "debit", "category": "utility", "date": "2026-09-05T08:00:00Z"},
        {"description": "Maynilad Water Bill", "amount": 550.0, "type": "debit", "category": "utility", "date": "2026-09-07T08:00:00Z"},
        {"description": "Puregold Supermarket", "amount": 3000.0, "type": "debit", "category": "groceries", "date": "2026-09-09T08:00:00Z"},
        {"description": "Cash-in 7-Eleven", "amount": 2000.0, "category": "cash_in"} # Should be filtered out as noise
    ]

    features = extract_12_features(transactions)

    # Check that all 12 indicators are present
    for feat_name in FEATURE_NAMES:
        assert feat_name in features, f"Missing feature: {feat_name}"

    assert features["avg_monthly_income"] > 15000.0
    assert 0.0 <= features["income_consistency"] <= 1.0
    assert features["inflow_outflow_ratio"] > 1.0
    assert features["active_financial_days"] >= 3.0
    assert features["transaction_frequency"] >= 4.0
    assert features["payment_consistency"] >= 0.50
    assert features["gig_earnings_activity"] > 0.80 # Grab payouts

def test_shap_explanation_and_scoring():
    sample_features = {
        "avg_monthly_income": 35000.0,
        "income_consistency": 0.85,
        "income_growth_rate": 0.12,
        "inflow_outflow_ratio": 1.75,
        "cash_flow_stability": 1800.0,
        "active_financial_days": 18.0,
        "avg_monthly_balance": 15000.0,
        "min_account_balance": 3500.0,
        "transaction_frequency": 24.0,
        "payment_consistency": 0.90,
        "gig_earnings_activity": 0.95,
        "burn_rate_trajectory": 0.85,
    }

    prediction = engine.predict(sample_features)

    assert 10 <= prediction["credit_score"] <= 100
    assert prediction["risk_tier"] in ["Prime", "Standard", "Moderate", "Subprime"]
    assert "High Reliability" in prediction["risk_category"] or "Good Reliability" in prediction["risk_category"]
    assert prediction["approved_loan_ceiling"] >= 10000

    shap_factors = prediction["shap_factors"]
    assert len(shap_factors) == 12

    # Check fields of SHAP factor
    for factor in shap_factors:
        assert "feature_key" in factor
        assert "raw_value" in factor
        assert "shap_value" in factor
        assert "impact_points" in factor
        assert factor["direction"] in ["positive", "negative"]
        assert "display_text" in factor
        assert "pts:" in factor["display_text"]
