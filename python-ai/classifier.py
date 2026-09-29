import re
from typing import Dict, Any, List, Optional

# Regular expression patterns for noise filtering (self-transfers, cash-in, wallet top-ups, P2P personal churn)
NOISE_PATTERNS = [
    r"\b(cash[\s\-]?in)\b",
    r"\b(top[\s\-]?up)\b",
    r"\b(self[\s\-]transfer)\b",
    r"\b(own[\s\-]account)\b",
    r"\b(fund[\s\-]transfer[\s\-]to[\s\-]own)\b",
    r"\b(transfer[\s\-]between[\s\-]accounts)\b",
    r"\b(internal[\s\-]transfer)\b",
    r"\b(wallet[\s\-]load)\b",
    r"\b(load[\s\-]pocket)\b",
    r"\b(e[\s\-]?wallet[\s\-]cashin)\b",
    r"\b(cash[\s\-]?in\s+via\s+(7[\s\-]?eleven|touchpay|cliqq|over[\s\-]the[\s\-]counter|bank))\b",
    r"\b(express[\s\-]send\s+(to|from)\s+09\d{2}[\s\-]?\d{3}[\s\-]?\d{4})\b",
    r"\b(send[\s\-]money\s+(to|from)\s+09\d{2}[\s\-]?\d{3}[\s\-]?\d{4})\b",
]

INCOME_PATTERNS = [
    r"\b(payroll|salary|wages|stipend)\b",
    r"\b(remittance|remit|western[\s\-]union|moneygram)\b",
    r"\b(commission|freelance|upwork|fiverr|client[\s\-]payment|payout)\b",
    r"\b(dividend|interest[\s\-]earned|business[\s\-]revenue|sales[\s\-]income)\b",
    r"\b(direct[\s\-]deposit|credit[\s\-]transfer[\s\-]inward)\b",
    # Philippine Verified Gig & Freelance Platform Payouts (GCash / Maya receipts)
    r"\b(grab[\s\-]?driver|grabpay[\s\-]payout|foodpanda[\s\-]rider|lalamove[\s\-]driver)\b",
    r"\b(toktok[\s\-]rider|angkas[\s\-]driver|joyride[\s\-]driver|move[\s\-]it[\s\-]rider)\b",
    r"\b(shopee[\s\-]rider|spx[\s\-]express|lazada[\s\-]logistics|transportify)\b",
    r"\b(gcash[\s\-]merchant[\s\-]settlement|maya[\s\-]business[\s\-]payout)\b",
]

UTILITY_PATTERNS = [
    r"\b(meralco|electricity|electric[\s\-]coop|aboitiz[\s\-]power)\b",
    r"\b(maynilad|manila[\s\-]water|prime[\s\-]water|water[\s\-]district)\b",
    r"\b(pldt|globe[\s\-]telecom|smart[\s\-]comm|converge|dito|internet[\s\-]bill|telco)\b",
    r"\b(cable[\s\-]tv|cignal|sky[\s\-]cable|billspay[\s\-]utilities)\b",
    r"\b(pay[\s\-]bills\s*-\s*(meralco|maynilad|manila[\s\-]water|pldt|globe|smart))\b"
]

DEBT_PATTERNS = [
    r"\b(loan[\s\-]payment|loan[\s\-]repayment|amortization|installment)\b",
    r"\b(credit[\s\-]card|citi[\s\-]card|bdo[\s\-]card|bpi[\s\-]card|metrobank[\s\-]card)\b",
    r"\b(home[\s\-]credit|billease|cashalo|tala|finbro|tonik[\s\-]loan)\b",
    r"\b(maya[\s\-]credit|gloan|spaylater|lazada[\s\-]paylater|sloan)\b",
    r"\b(car[\s\-]loan|auto[\s\-]loan|housing[\s\-]loan|mortgage)\b"
]

def is_noise_transaction(tx: Dict[str, Any]) -> bool:
    """
    Identifies and filters self-account transfers, cash-in churn, and internal balance shifts.
    """
    desc = str(tx.get("description", "")).lower()
    cat = str(tx.get("category", "")).lower()
    tx_type = str(tx.get("type", "")).lower()

    combined_text = f"{desc} {cat} {tx_type}"
    for pattern in NOISE_PATTERNS:
        if re.search(pattern, combined_text):
            return True
            
    # Also check category metadata if provided by Smile API
    if cat in ["transfer_internal", "transfer_self", "cash_in", "topup"]:
        return True

    return False


def classify_transaction(tx: Dict[str, Any]) -> str:
    """
    NLP/pattern matching classifier tagging income streams, utility payments, and debt repayments.
    Returns one of: 'INCOME', 'UTILITY', 'DEBT_REPAYMENT', 'DISCRETIONARY_EXPENSE', or 'OTHER'.
    """
    desc = str(tx.get("description", "")).lower()
    cat = str(tx.get("category", "")).lower()
    amount = float(tx.get("amount", 0.0))
    tx_type = str(tx.get("type", "")).lower() # credit / debit

    combined_text = f"{desc} {cat}"

    # Check Income
    for pattern in INCOME_PATTERNS:
        if re.search(pattern, combined_text):
            return "INCOME"

    # Check Utility
    for pattern in UTILITY_PATTERNS:
        if re.search(pattern, combined_text):
            return "UTILITY"

    # Check Debt Repayment
    for pattern in DEBT_PATTERNS:
        if re.search(pattern, combined_text):
            return "DEBT_REPAYMENT"

    # If amount is positive or explicitly marked as credit
    if tx_type in ["credit", "income", "inflow"] or (amount > 0 and tx_type != "debit"):
        return "INCOME"

    if tx_type in ["debit", "outflow", "expense"] or amount < 0:
        return "DISCRETIONARY_EXPENSE"

    return "OTHER"
