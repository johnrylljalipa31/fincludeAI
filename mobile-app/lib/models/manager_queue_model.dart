class ManagerQueueItem {
  final int id;
  final int userId;
  final String applicantName;
  final String applicantEmail;
  final String initials;
  final String purpose;
  final double amount;
  final int tenureDays;
  final double monthlyAmortization;
  final double totalRepayment;
  final String status;
  final String? appliedAt;
  final int creditScore;
  final int aiConfidence;
  final String riskStatus;
  final String riskClass;
  final String riskTier;

  ManagerQueueItem({
    required this.id,
    required this.userId,
    required this.applicantName,
    required this.applicantEmail,
    required this.initials,
    required this.purpose,
    required this.amount,
    required this.tenureDays,
    required this.monthlyAmortization,
    required this.totalRepayment,
    required this.status,
    this.appliedAt,
    required this.creditScore,
    required this.aiConfidence,
    required this.riskStatus,
    required this.riskClass,
    required this.riskTier,
  });

  factory ManagerQueueItem.fromJson(Map<String, dynamic> json) {
    return ManagerQueueItem(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      userId: json['user_id'] is int ? json['user_id'] : int.tryParse(json['user_id'].toString()) ?? 0,
      applicantName: json['applicant_name'] ?? 'Applicant',
      applicantEmail: json['applicant_email'] ?? '',
      initials: json['initials'] ?? 'GW',
      purpose: json['purpose'] ?? 'Working capital',
      amount: (json['amount'] is num) ? (json['amount'] as num).toDouble() : double.tryParse(json['amount'].toString()) ?? 0.0,
      tenureDays: json['tenure_days'] is int ? json['tenure_days'] : int.tryParse(json['tenure_days'].toString()) ?? 90,
      monthlyAmortization: (json['monthly_amortization'] is num) ? (json['monthly_amortization'] as num).toDouble() : 0.0,
      totalRepayment: (json['total_repayment'] is num) ? (json['total_repayment'] as num).toDouble() : 0.0,
      status: json['status'] ?? 'pending',
      appliedAt: json['applied_at'],
      creditScore: json['credit_score'] is int ? json['credit_score'] : int.tryParse(json['credit_score'].toString()) ?? 75,
      aiConfidence: json['ai_confidence'] is int ? json['ai_confidence'] : int.tryParse(json['ai_confidence'].toString()) ?? 75,
      riskStatus: json['risk_status'] ?? 'Clear',
      riskClass: json['risk_class'] ?? 'clear',
      riskTier: json['risk_tier'] ?? 'Standard',
    );
  }
}
