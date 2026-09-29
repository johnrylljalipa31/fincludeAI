import os
import joblib
import numpy as np
import pandas as pd
from typing import Dict, Any, List, Tuple
from sklearn.ensemble import GradientBoostingRegressor
import shap
from features import FEATURE_NAMES

class CreditScoringEngine:
    def __init__(self):
        self.model = None
        self.explainer = None
        self.model_version = "2.1.0"
        self._initialize_model()

    def _initialize_model(self):
        """
        Loads the pre-trained 12-feature model artifact if available, or initializes and trains one.
        """
        model_path = os.path.join(os.path.dirname(__file__), "models", "best_scoring_model.pkl")
        if os.path.exists(model_path):
            try:
                self.model = joblib.load(model_path)
            except Exception:
                self.model = None

        if self.model is None:
            # Synthetic reference distribution modeling Philippine alternative credit profiles
            np.random.seed(42)
            n_samples = 600

            incomes = np.random.lognormal(mean=10.3, sigma=0.45, size=n_samples)
            consistency = np.random.beta(4.0, 2.0, size=n_samples)
            growth = np.random.normal(0.08, 0.25, size=n_samples)
            inflow_outflow = np.random.normal(1.35, 0.40, size=n_samples)
            stability = np.random.uniform(500.0, 10000.0, size=n_samples)
            active_days = np.random.uniform(4.0, 27.0, size=n_samples)
            avg_balance = np.maximum(500.0, incomes * np.random.uniform(0.1, 0.6, size=n_samples))
            min_balance = np.random.uniform(-500.0, 15000.0, size=n_samples)
            frequency = np.random.uniform(6.0, 55.0, size=n_samples)
            payment_consistency = np.random.beta(3.5, 2.0, size=n_samples)
            gig_activity = np.random.beta(3.0, 2.5, size=n_samples)
            burn_rate = np.random.normal(1.05, 0.35, size=n_samples)

            X = pd.DataFrame({
                "avg_monthly_income": incomes,
                "income_consistency": consistency,
                "income_growth_rate": growth,
                "inflow_outflow_ratio": inflow_outflow,
                "cash_flow_stability": stability,
                "active_financial_days": active_days,
                "avg_monthly_balance": avg_balance,
                "min_account_balance": min_balance,
                "transaction_frequency": frequency,
                "payment_consistency": payment_consistency,
                "gig_earnings_activity": gig_activity,
                "burn_rate_trajectory": burn_rate,
            })[FEATURE_NAMES]

            z = (
                0.9 * (np.log(incomes) - 10.3)
                + 2.2 * (consistency - 0.65)
                + 1.5 * growth
                + 2.0 * (inflow_outflow - 1.35)
                + 0.8 * ((active_days - 15.0) / 8.0)
                + 0.9 * (np.log(np.maximum(100.0, avg_balance)) - 8.5)
                + 0.6 * ((min_balance - 4000.0) / 5000.0)
                + 2.0 * (payment_consistency - 0.65)
                + 0.8 * (gig_activity - 0.55)
                - 2.5 * (burn_rate - 1.05)
            )
            prob = 1.0 / (1.0 + np.exp(-z))
            y = np.clip(prob * 65.0 + 30.0 + np.random.normal(0, 1.5, n_samples), 15.0, 98.0)

            self.model = GradientBoostingRegressor(n_estimators=60, max_depth=3, learning_rate=0.1, random_state=42)
            self.model.fit(X, y)

        try:
            self.explainer = shap.TreeExplainer(self.model)
        except Exception:
            self.explainer = None

    def calculate_approved_ceiling(self, score: float, avg_monthly_income: float, inflow_outflow_ratio: float) -> int:
        """
        Calculates approved loan offer ceiling in Philippine Pesos (PHP) based on credit score
        and verified disposable capacity.
        """
        net_buffer = max(1500.0, avg_monthly_income * (1.0 - (1.0 / max(1.0, inflow_outflow_ratio))))

        if score >= 80:
            ceiling = max(20000, min(50000, int(net_buffer * 1.5)))
        elif score >= 70:
            ceiling = max(12000, min(30000, int(net_buffer * 1.0)))
        elif score >= 60:
            ceiling = max(6000, min(18000, int(net_buffer * 0.7)))
        elif score >= 50:
            ceiling = max(3000, min(10000, int(net_buffer * 0.4)))
        else:
            ceiling = 3000

        return int(round(ceiling / 500.0) * 500)

    def explain_score(self, features: Dict[str, float], raw_shap_values: np.ndarray) -> List[Dict[str, Any]]:
        """
        Translates mathematical SHAP marginal contribution values into readable insights
        (Positive and Negative credit drivers) per Section 3.8.
        """
        human_factors = []

        for idx, key in enumerate(FEATURE_NAMES):
            shap_val = float(raw_shap_values[idx])
            val = float(features.get(key, 0.0))
            pts = int(round(shap_val))
            sign_str = f"+{pts}" if pts > 0 else f"{pts}"
            direction = "positive" if shap_val >= 0 else "negative"

            if key == "avg_monthly_income":
                if val >= 30000:
                    desc = f"Strong verified monthly income (₱{val:,.0f})"
                elif val >= 18000:
                    desc = f"Moderate monthly inflow baseline (₱{val:,.0f})"
                else:
                    desc = f"Limited monthly inflow volume (₱{val:,.0f})"

            elif key == "income_consistency":
                if val >= 0.70:
                    desc = "High earnings consistency with low variance across cycles"
                elif val >= 0.45:
                    desc = "Moderate earnings stability across recent cycles"
                else:
                    desc = "Elevated income volatility between payout periods"

            elif key == "income_growth_rate":
                if val > 0.05:
                    desc = f"Positive income growth trend (+{int(val * 100)}% MoM)"
                elif val < -0.05:
                    desc = f"Contraction in monthly earnings ({int(val * 100)}% MoM)"
                else:
                    desc = "Stable month-over-month earnings trend"

            elif key == "inflow_outflow_ratio":
                if val >= 1.25:
                    desc = f"Healthy positive cash flow ratio ({val:.2f}x inflows vs outflows)"
                elif val >= 1.0:
                    desc = f"Break-even cash flow ratio ({val:.2f}x inflows to outflows)"
                else:
                    desc = f"Spending exceeds incoming funds ({val:.2f}x ratio)"

            elif key == "cash_flow_stability":
                if val <= 2500:
                    desc = "Predictable and controlled daily cash flow variance"
                else:
                    desc = "Fluctuating daily cash transaction amounts"

            elif key == "active_financial_days":
                if val >= 15:
                    desc = f"High account activity across {int(val)} active days/month"
                else:
                    desc = f"Infrequent account usage ({int(val)} active days/month)"

            elif key == "avg_monthly_balance":
                if val >= 10000:
                    desc = f"Substantial average liquid buffer maintained (₱{val:,.0f})"
                elif val >= 3000:
                    desc = f"Moderate average balance cushion (₱{val:,.0f})"
                else:
                    desc = f"Low average liquid cash reserve (₱{val:,.0f})"

            elif key == "min_account_balance":
                if val >= 1000:
                    desc = f"Positive minimum balance safety floor (₱{val:,.0f})"
                else:
                    desc = f"Low or zero minimum balance reached (₱{val:,.0f})"

            elif key == "transaction_frequency":
                if val >= 20:
                    desc = f"Active financial velocity ({int(val)} transactions/month)"
                else:
                    desc = f"Light transaction history ({int(val)} transactions/month)"

            elif key == "payment_consistency":
                if val >= 0.70:
                    desc = "Consistent on-time utility and recurring bill payment records"
                elif val >= 0.40:
                    desc = "Moderate recurring bill payment history"
                else:
                    desc = "Limited or irregular recurring bill payment history"

            elif key == "gig_earnings_activity":
                if val >= 0.50:
                    desc = "Strong documented gig economy / digital platform earnings"
                elif val >= 0.20:
                    desc = "Documented supplemental gig/freelance revenue"
                else:
                    desc = "Low proportion of digital platform earnings"

            elif key == "burn_rate_trajectory":
                if val <= 1.05:
                    desc = "Disciplined day-to-day spending trajectory (low burn rate)"
                else:
                    desc = f"Accelerating spending burn rate ({val:.2f}x recent vs baseline)"
            else:
                desc = key.replace("_", " ").capitalize()

            prefix = "Positive" if direction == "positive" else "Attention"
            human_factors.append({
                "feature_key": key,
                "raw_value": val,
                "shap_value": round(shap_val, 4),
                "impact_points": pts,
                "direction": direction,
                "display_text": f"[{prefix}] {sign_str} pts: {desc}"
            })

        # Sort by absolute impact magnitude
        human_factors.sort(key=lambda x: abs(x["shap_value"]), reverse=True)
        return human_factors

    def predict(self, features: Dict[str, float]) -> Dict[str, Any]:
        """
        Computes 0-100 score, risk tier, approved ceiling, and SHAP explanations.
        """
        row_df = pd.DataFrame([{col: features.get(col, 0.0) for col in FEATURE_NAMES}])[FEATURE_NAMES]

        # Raw score prediction
        pred_score = float(self.model.predict(row_df)[0])
        score = int(np.clip(round(pred_score), 10, 99))

        # Determine risk tier
        if score >= 80:
            risk_tier = "Prime"
            risk_category = "High Reliability / Low Risk"
        elif score >= 70:
            risk_tier = "Standard"
            risk_category = "Good Reliability / Moderate Risk"
        elif score >= 60:
            risk_tier = "Moderate"
            risk_category = "Fair Reliability / Medium Risk"
        else:
            risk_tier = "Subprime"
            risk_category = "High Risk / Additional Verification Needed"

        # Calculate SHAP values
        if self.explainer is not None:
            try:
                shap_res = self.explainer(row_df)
                raw_shap = shap_res.values[0]
            except Exception:
                raw_shap = np.zeros(len(FEATURE_NAMES))
        else:
            raw_shap = np.zeros(len(FEATURE_NAMES))

        avg_income = float(features.get("avg_monthly_income", 0.0))
        ratio = float(features.get("inflow_outflow_ratio", 1.0))
        disposable = max(0.0, avg_income * (1.0 - (1.0 / max(1.0, ratio))))

        approved_ceiling = self.calculate_approved_ceiling(score, avg_income, ratio)
        shap_factors = self.explain_score(features, raw_shap)

        return {
            "credit_score": score,
            "risk_tier": risk_tier,
            "risk_category": risk_category,
            "model_version": self.model_version,
            "approved_loan_ceiling": approved_ceiling,
            "projected_monthly_disposable_cash_flow": round(disposable, 2),
            "shap_factors": shap_factors,
            "engineered_features": features
        }

engine = CreditScoringEngine()
