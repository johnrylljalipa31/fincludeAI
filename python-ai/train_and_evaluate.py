"""
Finclude-AI Multi-Model Training & Benchmark Evaluation (Section 3.7)
Trains Logistic Regression, Random Forest, and Gradient Boosting Regressor/Classifier.
Evaluates ROC-AUC, F1-Score, Accuracy, Precision, and Recall.
"""

import os
import json
import joblib
import numpy as np
import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.linear_model import LogisticRegression
from sklearn.ensemble import RandomForestClassifier, GradientBoostingClassifier, GradientBoostingRegressor
from sklearn.metrics import roc_auc_score, f1_score, accuracy_score, precision_score, recall_score, classification_report
from features import FEATURE_NAMES

def generate_synthetic_training_dataset(n_samples: int = 1200, random_state: int = 42) -> pd.DataFrame:
    """
    Generates a realistic Philippine alternative credit profile dataset for gig workers,
    freelancers, and online sellers using the 12 credit risk indicators.
    """
    np.random.seed(random_state)

    # 1. avg_monthly_income (PHP 10,000 - 90,000)
    incomes = np.random.lognormal(mean=10.3, sigma=0.45, size=n_samples)
    incomes = np.clip(incomes, 10000.0, 95000.0)

    # 2. income_consistency (0.0 - 1.0, higher = more consistent)
    consistency = np.random.beta(a=4.0, b=2.0, size=n_samples)

    # 3. income_growth_rate (-0.5 to +0.8)
    growth = np.random.normal(loc=0.08, scale=0.25, size=n_samples)
    growth = np.clip(growth, -0.6, 1.5)

    # 4. inflow_outflow_ratio (0.5 to 3.0)
    inflow_outflow = np.random.normal(loc=1.35, scale=0.40, size=n_samples)
    inflow_outflow = np.clip(inflow_outflow, 0.4, 4.0)

    # 5. cash_flow_stability (PHP 500 to 12000)
    stability = np.random.uniform(500.0, 10000.0, size=n_samples)

    # 6. active_financial_days (3 to 28 days/month)
    active_days = np.random.uniform(4.0, 27.0, size=n_samples)

    # 7. avg_monthly_balance (PHP 1,000 to 50,000)
    avg_balance = np.maximum(500.0, incomes * np.random.uniform(0.1, 0.6, size=n_samples))

    # 8. min_account_balance (-2000 to 20,000)
    min_balance = np.random.uniform(-500.0, 15000.0, size=n_samples)

    # 9. transaction_frequency (5 to 60 tx/month)
    frequency = np.random.uniform(6.0, 55.0, size=n_samples)

    # 10. payment_consistency (0.1 to 1.0)
    payment_consistency = np.random.beta(a=3.5, b=2.0, size=n_samples)

    # 11. gig_earnings_activity (0.1 to 1.0)
    gig_activity = np.random.beta(a=3.0, b=2.5, size=n_samples)

    # 12. burn_rate_trajectory (0.4 to 2.5)
    burn_rate = np.random.normal(loc=1.05, scale=0.35, size=n_samples)
    burn_rate = np.clip(burn_rate, 0.3, 3.2)

    df = pd.DataFrame({
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

    # Latent creditworthiness probability formulation
    # Balanced formulation centered around 0 for realistic Philippine alternative credit
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
    # Binary target: 1 = Creditworthy / Low Risk, 0 = Elevated Default Risk
    y_binary = (prob >= 0.50).astype(int)

    # Continuous credit score (0-100)
    credit_scores = np.clip(prob * 65.0 + 30.0 + np.random.normal(0, 2.0, n_samples), 15.0, 98.0)

    df["target_binary"] = y_binary
    df["target_score"] = credit_scores
    return df

def train_and_compare_models():
    print("=================================================================")
    print("Finclude-AI: Section 3.7 Supervised ML Training & Model Selection")
    print("=================================================================")

    df = generate_synthetic_training_dataset(n_samples=1500, random_state=42)
    X = df[FEATURE_NAMES]
    y_binary = df["target_binary"]
    y_score = df["target_score"]

    X_train, X_test, y_b_train, y_b_test, y_s_train, y_s_test = train_test_split(
        X, y_binary, y_score, test_size=0.25, random_state=42, stratify=y_binary
    )

    from sklearn.pipeline import Pipeline
    from sklearn.preprocessing import StandardScaler

    models = {
        "Logistic Regression": Pipeline([
            ("scaler", StandardScaler()),
            ("clf", LogisticRegression(max_iter=1000, random_state=42))
        ]),
        "Random Forest": RandomForestClassifier(n_estimators=100, max_depth=6, random_state=42),
        "Gradient Boosting (GBM)": GradientBoostingClassifier(n_estimators=80, learning_rate=0.08, max_depth=4, random_state=42)
    }

    results = []
    print("\nEvaluating Candidate Classifiers on Holdout Test Set (n=375):")
    print("-" * 75)
    print(f"{'Model Name':<26} | {'Accuracy':<9} | {'Precision':<9} | {'Recall':<9} | {'F1-Score':<9} | {'ROC-AUC':<9}")
    print("-" * 75)

    best_model_name = None
    best_roc_auc = -1.0
    best_classifier = None

    for name, clf in models.items():
        clf.fit(X_train, y_b_train)
        preds = clf.predict(X_test)
        probs = clf.predict_proba(X_test)[:, 1]

        acc = accuracy_score(y_b_test, preds)
        prec = precision_score(y_b_test, preds)
        rec = recall_score(y_b_test, preds)
        f1 = f1_score(y_b_test, preds)
        roc = roc_auc_score(y_b_test, probs)

        results.append({
            "model": name,
            "accuracy": round(float(acc), 4),
            "precision": round(float(prec), 4),
            "recall": round(float(rec), 4),
            "f1_score": round(float(f1), 4),
            "roc_auc": round(float(roc), 4),
        })

        print(f"{name:<26} | {acc:.4f}    | {prec:.4f}    | {rec:.4f}    | {f1:.4f}   | {roc:.4f}")

        if roc > best_roc_auc:
            best_roc_auc = roc
            best_model_name = name
            best_classifier = clf

    print("-" * 75)
    print(f"\n[WINNER] Best Performing Model: '{best_model_name}' (ROC-AUC: {best_roc_auc:.4f})")

    # Also train calibrated GradientBoostingRegressor for 0-100 numeric scoring and SHAP TreeExplainer
    print("\nTraining Calibrated 0-100 Credit Score Regressor with 12 features...")
    regressor = GradientBoostingRegressor(n_estimators=60, max_depth=3, learning_rate=0.1, random_state=42)
    regressor.fit(X_train, y_s_train)

    os.makedirs("models", exist_ok=True)
    joblib.dump(regressor, "models/best_scoring_model.pkl")
    joblib.dump(best_classifier, "models/best_classifier.pkl")

    metrics_path = "models/model_benchmark_results.json"
    with open(metrics_path, "w") as f:
        json.dump({
            "comparison": results,
            "selected_model": best_model_name,
            "best_roc_auc": round(float(best_roc_auc), 4),
            "features": FEATURE_NAMES,
            "timestamp": pd.Timestamp.now().isoformat()
        }, f, indent=2)

    print(f"[OK] Saved model artifacts & metrics to: models/")
    return results

if __name__ == "__main__":
    train_and_compare_models()
