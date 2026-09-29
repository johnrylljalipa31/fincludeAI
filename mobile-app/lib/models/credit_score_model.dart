class ShapFactor {
  final String displayText;
  final int impactPoints;
  final String direction;

  ShapFactor({
    required this.displayText,
    required this.impactPoints,
    required this.direction,
  });

  factory ShapFactor.fromJson(Map<String, dynamic> json) {
    return ShapFactor(
      displayText: json['display_text'] ?? '',
      impactPoints: json['impact_points'] is int
          ? json['impact_points']
          : int.tryParse(json['impact_points'].toString()) ?? 0,
      direction: json['direction'] ?? 'positive',
    );
  }
}

class CreditScoreModel {
  final int score;
  final String riskTier;
  final double approvedCeiling;
  final double projectedDisposableCashFlow;
  final List<ShapFactor> shapFactors;
  final String? scoredAt;

  CreditScoreModel({
    required this.score,
    required this.riskTier,
    required this.approvedCeiling,
    required this.projectedDisposableCashFlow,
    required this.shapFactors,
    this.scoredAt,
  });

  factory CreditScoreModel.fromJson(Map<String, dynamic> json) {
    var rawFactors = json['shap_factors'];
    List<ShapFactor> factors = [];
    if (rawFactors is List) {
      factors = rawFactors.map((f) => ShapFactor.fromJson(f as Map<String, dynamic>)).toList();
    }

    return CreditScoreModel(
      score: json['score'] is int ? json['score'] : int.tryParse(json['score'].toString()) ?? 50,
      riskTier: json['risk_tier'] ?? 'Standard',
      approvedCeiling: (json['approved_ceiling'] is num)
          ? (json['approved_ceiling'] as num).toDouble()
          : double.tryParse(json['approved_ceiling'].toString()) ?? 15000.0,
      projectedDisposableCashFlow: (json['projected_disposable_cash_flow'] is num)
          ? (json['projected_disposable_cash_flow'] as num).toDouble()
          : double.tryParse(json['projected_disposable_cash_flow'].toString()) ?? 0.0,
      shapFactors: factors,
      scoredAt: json['scored_at'],
    );
  }
}
