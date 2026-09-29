import pandas as pd
import numpy as np
from datetime import datetime, timezone
from typing import List, Dict, Any
from classifier import is_noise_transaction, classify_transaction

# 12 Primary Credit Risk Indicators (Section 3.6)
FEATURE_NAMES = [
    "avg_monthly_income",       # 1. Average monthly income
    "income_consistency",      # 2. Income consistency (variance / CV)
    "income_growth_rate",      # 3. Income growth/decline rate
    "inflow_outflow_ratio",    # 4. Inflow-to-outflow ratio
    "cash_flow_stability",     # 5. Cash-flow stability (std of net cash flow)
    "active_financial_days",   # 6. Active financial days count
    "avg_monthly_balance",     # 7. Average monthly balance
    "min_account_balance",     # 8. Minimum account balance
    "transaction_frequency",   # 9. Transaction frequency
    "payment_consistency",     # 10. Payment consistency (bills/utilities)
    "gig_earnings_activity",   # 11. Gig/business earnings activity
    "burn_rate_trajectory",    # 12. Burn rate trajectory (outflow acceleration)
]

def parse_date(date_val: Any, fallback: datetime) -> datetime:
    if not date_val:
        return fallback
    try:
        s = str(date_val).replace("Z", "+00:00")
        dt = datetime.fromisoformat(s)
        if dt.tzinfo is None:
            dt = dt.replace(tzinfo=timezone.utc)
        return dt
    except Exception:
        return fallback

def extract_12_features(transactions: List[Dict[str, Any]]) -> Dict[str, float]:
    """
    Transforms clean financial records into the 12 primary credit risk indicators (Section 3.6).
    """
    now = datetime.now(timezone.utc)
    default_features = {
        "avg_monthly_income": 0.0,
        "income_consistency": 0.0,
        "income_growth_rate": 0.0,
        "inflow_outflow_ratio": 0.0,
        "cash_flow_stability": 0.0,
        "active_financial_days": 0.0,
        "avg_monthly_balance": 0.0,
        "min_account_balance": 0.0,
        "transaction_frequency": 0.0,
        "payment_consistency": 0.0,
        "gig_earnings_activity": 0.0,
        "burn_rate_trajectory": 1.0,
    }

    if not transactions:
        return default_features

    cleaned = []
    for tx in transactions:
        if is_noise_transaction(tx):
            continue

        cat = classify_transaction(tx)
        amount = abs(float(tx.get("amount", 0.0)))
        tx_type = str(tx.get("type", "")).lower()
        if not tx_type:
            tx_type = "credit" if cat == "INCOME" else "debit"
        elif tx_type in ["inflow"]:
            tx_type = "credit"
        elif tx_type in ["outflow"]:
            tx_type = "debit"

        dt = parse_date(tx.get("date") or tx.get("transaction_date") or tx.get("created_at"), now)
        days_ago = max(0.0, (now - dt).total_seconds() / 86400.0)
        desc = str(tx.get("description", "")).lower()

        # Check gig/freelance origin
        is_gig = any(k in desc for k in [
            "grab", "foodpanda", "lalamove", "angkas", "joyride", "toktok",
            "shopee", "lazada", "freelance", "upwork", "fiverr", "remittance"
        ])

        cleaned.append({
            "amount": amount,
            "type": tx_type,
            "category": cat,
            "datetime": dt,
            "days_ago": days_ago,
            "date_only": dt.strftime("%Y-%m-%d"),
            "is_gig": is_gig,
        })

    if not cleaned:
        return default_features

    df = pd.DataFrame(cleaned)

    # Observation period in months (minimum 1 month for normalization)
    max_days = max(df["days_ago"].max(), 30.0)
    month_factor = max_days / 30.0

    # Inflows & Outflows
    inflows_df = df[df["type"] == "credit"]
    outflows_df = df[df["type"] == "debit"]

    total_inflows = float(inflows_df["amount"].sum())
    total_outflows = float(outflows_df["amount"].sum())

    # 1. Average Monthly Income
    avg_monthly_income = float(total_inflows / month_factor)

    # 2. Income Consistency: Coefficient of variation of inflow events (or 1 / (1 + CV))
    if len(inflows_df) >= 2:
        inflow_amounts = inflows_df["amount"].values
        mean_inflow = np.mean(inflow_amounts)
        std_inflow = np.std(inflow_amounts)
        cv = std_inflow / (mean_inflow + 1e-5)
        # Normalized score: 1.0 = highly consistent, 0.0 = highly volatile
        income_consistency = float(np.clip(1.0 / (1.0 + cv), 0.0, 1.0))
    elif len(inflows_df) == 1:
        income_consistency = 0.50
    else:
        income_consistency = 0.0

    # 3. Income Growth/Decline Rate: Recent 30 days vs prior 30-60 days inflow
    recent_inflow = float(df[(df["days_ago"] <= 30.0) & (df["type"] == "credit")]["amount"].sum())
    prior_inflow = float(df[(df["days_ago"] > 30.0) & (df["days_ago"] <= 60.0) & (df["type"] == "credit")]["amount"].sum())
    if prior_inflow > 0:
        income_growth_rate = float(np.clip((recent_inflow - prior_inflow) / prior_inflow, -1.0, 2.0))
    else:
        income_growth_rate = 0.0 if recent_inflow == 0 else 0.10

    # 4. Inflow-to-Outflow Ratio
    inflow_outflow_ratio = float(np.clip(total_inflows / (total_outflows + 1.0), 0.0, 10.0))

    # 5. Cash-Flow Stability: Net monthly or weekly cash-flow volatility
    # Group net flow by date
    daily_net = df.apply(lambda r: r["amount"] if r["type"] == "credit" else -r["amount"], axis=1)
    cash_flow_stability = float(max(0.0, np.std(daily_net) if len(daily_net) > 1 else 0.0))

    # 6. Active Financial Days Count (normalized per 30-day window)
    distinct_active_days = float(df["date_only"].nunique())
    active_financial_days = float(np.clip(distinct_active_days / month_factor, 1.0, 30.0))

    # 7. Average Monthly Balance & 8. Minimum Account Balance
    # Reconstruct estimated running balance curve starting from estimated initial reserve
    sorted_df = df.sort_values("datetime")
    running_balance = 5000.0 # Initial baseline reserve (PHP)
    balances = []
    for _, row in sorted_df.iterrows():
        if row["type"] == "credit":
            running_balance += row["amount"]
        else:
            running_balance -= row["amount"]
        balances.append(running_balance)

    avg_monthly_balance = float(max(0.0, np.mean(balances)))
    min_account_balance = float(min(balances)) if balances else 0.0

    # 9. Transaction Frequency: Normalized count of transactions per month
    transaction_frequency = float(len(df) / month_factor)

    # 10. Payment Consistency: Utility & recurring bill payments regularity
    utility_df = df[df["category"].isin(["UTILITY", "DEBT_REPAYMENT"])]
    if len(utility_df) >= 3:
        payment_consistency = 0.95
    elif len(utility_df) == 2:
        payment_consistency = 0.75
    elif len(utility_df) == 1:
        payment_consistency = 0.50
    else:
        payment_consistency = 0.20

    # 11. Gig/Business Earnings Activity: Proportion of inflow originating from verified gig platforms
    gig_inflow = float(df[(df["type"] == "credit") & (df["is_gig"])]["amount"].sum())
    gig_earnings_activity = float(np.clip(gig_inflow / (total_inflows + 1.0), 0.0, 1.0))
    if gig_inflow == 0 and total_inflows > 0:
        # Check if description has standard payroll/freelance
        gig_earnings_activity = 0.40

    # 12. Burn Rate Trajectory: Spending in recent 15 days vs prior 15-45 days
    recent_outflow = float(df[(df["days_ago"] <= 15.0) & (df["type"] == "debit")]["amount"].sum())
    prior_outflow = float(df[(df["days_ago"] > 15.0) & (df["days_ago"] <= 45.0) & (df["type"] == "debit")]["amount"].sum())
    prior_15_equiv = (prior_outflow / 2.0) if prior_outflow > 0 else (total_outflows / (2.0 * month_factor) if total_outflows > 0 else 1.0)
    burn_rate_trajectory = float(np.clip(recent_outflow / (prior_15_equiv + 1e-5), 0.1, 4.0))

    return {
        "avg_monthly_income": round(avg_monthly_income, 2),
        "income_consistency": round(income_consistency, 4),
        "income_growth_rate": round(income_growth_rate, 4),
        "inflow_outflow_ratio": round(inflow_outflow_ratio, 4),
        "cash_flow_stability": round(cash_flow_stability, 2),
        "active_financial_days": round(active_financial_days, 1),
        "avg_monthly_balance": round(avg_monthly_balance, 2),
        "min_account_balance": round(min_account_balance, 2),
        "transaction_frequency": round(transaction_frequency, 2),
        "payment_consistency": round(payment_consistency, 4),
        "gig_earnings_activity": round(gig_earnings_activity, 4),
        "burn_rate_trajectory": round(burn_rate_trajectory, 4),
    }
