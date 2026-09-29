import pandas as pd
import numpy as np
from datetime import datetime, timezone
from typing import List, Dict, Any
from classifier import is_noise_transaction, classify_transaction

def compute_psite_features(transactions: List[Dict[str, Any]]) -> Dict[str, float]:
    """
    Computes PSITE features:
    1. Cash Flow Velocity
    2. Burn Rate Trajectory
    3. Utility Recency & Consistency
    4. Debt-to-Income (DTI) Ratio
    """
    if not transactions:
        return {
            "monthly_income": 0.0,
            "monthly_expenses": 0.0,
            "cash_flow_velocity": 0.0,
            "burn_rate_trajectory": 1.0,
            "utility_recency_days": 90.0,
            "utility_payment_count": 0.0,
            "debt_service_amount": 0.0,
            "debt_to_income_ratio": 0.0,
            "net_disposable_cash_flow": 0.0,
        }

    # Step 1: Filter noise and categorize
    cleaned = []
    now = datetime.now(timezone.utc)

    for tx in transactions:
        if is_noise_transaction(tx):
            continue
        
        category = classify_transaction(tx)
        amount = abs(float(tx.get("amount", 0.0)))
        
        # Parse date
        date_str = tx.get("date") or tx.get("transaction_date") or tx.get("created_at")
        tx_dt = None
        if date_str:
            try:
                # Handle ISO format
                cleaned_date_str = str(date_str).replace("Z", "+00:00")
                tx_dt = datetime.fromisoformat(cleaned_date_str)
                if tx_dt.tzinfo is None:
                    tx_dt = tx_dt.replace(tzinfo=timezone.utc)
            except Exception:
                tx_dt = now
        else:
            tx_dt = now

        days_ago = max(0.0, (now - tx_dt).total_seconds() / 86400.0)

        cleaned.append({
            "category": category,
            "amount": amount,
            "datetime": tx_dt,
            "days_ago": days_ago
        })

    if not cleaned:
        return {
            "monthly_income": 0.0,
            "monthly_expenses": 0.0,
            "cash_flow_velocity": 0.0,
            "burn_rate_trajectory": 1.0,
            "utility_recency_days": 90.0,
            "utility_payment_count": 0.0,
            "debt_service_amount": 0.0,
            "debt_to_income_ratio": 0.0,
            "net_disposable_cash_flow": 0.0,
        }

    df = pd.DataFrame(cleaned)

    # Consider transactions within last 90 days, or all if span is smaller
    max_days = max(df["days_ago"].max(), 30.0)
    month_factor = max_days / 30.0

    # Inflows (Income)
    income_txs = df[df["category"] == "INCOME"]
    total_income = income_txs["amount"].sum()
    monthly_income = float(total_income / month_factor)

    # Utilities
    utility_txs = df[df["category"] == "UTILITY"]
    utility_count = float(len(utility_txs))
    if not utility_txs.empty:
        utility_recency_days = float(utility_txs["days_ago"].min())
        monthly_utilities = float(utility_txs["amount"].sum() / month_factor)
    else:
        utility_recency_days = 90.0
        monthly_utilities = 0.0

    # Debt Repayments
    debt_txs = df[df["category"] == "DEBT_REPAYMENT"]
    total_debt = debt_txs["amount"].sum()
    monthly_debt = float(total_debt / month_factor)

    # Discretionary / Outflows
    expense_txs = df[df["category"].isin(["DISCRETIONARY_EXPENSE", "UTILITY", "DEBT_REPAYMENT"])]
    total_expenses = expense_txs["amount"].sum()
    monthly_expenses = float(total_expenses / month_factor)

    # 1. Cash Flow Velocity: Volume & frequency of funds moving through the account per month
    tx_count_per_month = float(len(df) / month_factor)
    turnover_ratio = (total_income + total_expenses) / (monthly_income + 1.0)
    # Velocity index normalized around 1.0 - 10.0
    cash_flow_velocity = float(np.clip(tx_count_per_month * 0.2 + (turnover_ratio * 0.5), 0.1, 20.0))

    # 2. Burn Rate Trajectory: Day-to-day spending acceleration (Recent 15 days outflow vs previous 15-45 days)
    recent_outflows = df[(df["days_ago"] <= 15.0) & (df["category"] != "INCOME")]["amount"].sum()
    past_outflows = df[(df["days_ago"] > 15.0) & (df["days_ago"] <= 45.0) & (df["category"] != "INCOME")]["amount"].sum()
    
    # Normalized 15-day baseline
    past_15day_equivalent = past_outflows / 2.0 if past_outflows > 0 else (monthly_expenses * 0.5)
    if past_15day_equivalent > 0:
        burn_rate_trajectory = float(np.clip(recent_outflows / past_15day_equivalent, 0.2, 3.0))
    else:
        burn_rate_trajectory = 1.0

    # 3. Utility Recency: Already computed days since last utility bill
    # Lower is better (e.g. paid within 15-30 days)

    # 4. Debt-to-Income (DTI) Ratio: Total recurring debt obligations / verified monthly income
    if monthly_income > 0:
        debt_to_income_ratio = float(np.clip(monthly_debt / monthly_income, 0.0, 1.5))
    else:
        debt_to_income_ratio = 1.0 if monthly_debt > 0 else 0.0

    # Net Projected Monthly Disposable Cash Flow
    # Income minus (Utilities + Debt Payments + Discretionary baseline)
    net_disposable_cash_flow = float(max(0.0, monthly_income - monthly_expenses))

    return {
        "monthly_income": round(monthly_income, 2),
        "monthly_expenses": round(monthly_expenses, 2),
        "cash_flow_velocity": round(cash_flow_velocity, 2),
        "burn_rate_trajectory": round(burn_rate_trajectory, 2),
        "utility_recency_days": round(utility_recency_days, 1),
        "utility_payment_count": round(utility_count, 1),
        "debt_service_amount": round(monthly_debt, 2),
        "debt_to_income_ratio": round(debt_to_income_ratio, 3),
        "net_disposable_cash_flow": round(net_disposable_cash_flow, 2),
    }
