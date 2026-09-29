import uvicorn
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field
from typing import List, Dict, Any, Optional

from features import extract_12_features
from psite import compute_psite_features
from model import engine

app = FastAPI(
    title="Finclude-AI Alternative Credit Scoring Microservice",
    description="12-Indicator Feature Engineering, Multi-Model Scoring & SHAP Explainability Engine",
    version="2.1.0"
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

class ScoreRequest(BaseModel):
    user_id: Optional[Any] = None
    transactions: List[Dict[str, Any]] = Field(default_factory=list)

class ScoreResponse(BaseModel):
    status: str
    credit_score: int
    risk_tier: str
    risk_category: str
    model_version: str
    approved_loan_ceiling: int
    projected_monthly_disposable_cash_flow: float
    shap_factors: List[Dict[str, Any]]
    features: Dict[str, Any]
    psite_metrics: Dict[str, Any]

@app.get("/health")
def health_check():
    return {
        "status": "healthy",
        "service": "finclude-ai-python",
        "version": "2.1.0"
    }

@app.post("/score", response_model=ScoreResponse)
def score_transactions(request: ScoreRequest):
    try:
        raw_txs = request.transactions
        # 1. 12-Indicator Feature Engineering
        features_12 = extract_12_features(raw_txs)

        # 2. Credit Scoring, Cash flow projection, Loan ceiling & SHAP Factor extraction
        prediction = engine.predict(features_12)

        # PSITE features for backward compatibility
        psite_feats = compute_psite_features(raw_txs)

        return ScoreResponse(
            status="success",
            credit_score=prediction["credit_score"],
            risk_tier=prediction["risk_tier"],
            risk_category=prediction["risk_category"],
            model_version=prediction["model_version"],
            approved_loan_ceiling=prediction["approved_loan_ceiling"],
            projected_monthly_disposable_cash_flow=prediction["projected_monthly_disposable_cash_flow"],
            shap_factors=prediction["shap_factors"],
            features=prediction["engineered_features"],
            psite_metrics=psite_feats
        )
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Scoring failed: {str(e)}")

if __name__ == "__main__":
    uvicorn.run("main:app", host="127.0.0.1", port=8000, reload=True)
