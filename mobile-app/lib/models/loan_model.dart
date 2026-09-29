class RepaymentScheduleItem {
  final int installmentNumber;
  final double amount;
  final String dueDate;
  final String status;

  RepaymentScheduleItem({
    required this.installmentNumber,
    required this.amount,
    required this.dueDate,
    required this.status,
  });

  factory RepaymentScheduleItem.fromJson(Map<String, dynamic> json) {
    return RepaymentScheduleItem(
      installmentNumber: json['installment_number'] ?? 1,
      amount: (json['amount'] is num) ? (json['amount'] as num).toDouble() : double.tryParse(json['amount'].toString()) ?? 0.0,
      dueDate: json['due_date'] ?? '',
      status: json['status'] ?? 'scheduled',
    );
  }
}

class LoanModel {
  final int id;
  final double amount;
  final int tenureDays;
  final double interestRateApr;
  final double monthlyAmortization;
  final double totalRepayment;
  final String status;
  final String? dueDate;
  final String? purpose;

  LoanModel({
    required this.id,
    required this.amount,
    required this.tenureDays,
    required this.interestRateApr,
    required this.monthlyAmortization,
    required this.totalRepayment,
    required this.status,
    this.dueDate,
    this.purpose,
  });

  factory LoanModel.fromJson(Map<String, dynamic> json) {
    return LoanModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      amount: (json['amount'] is num) ? (json['amount'] as num).toDouble() : double.tryParse(json['amount'].toString()) ?? 0.0,
      tenureDays: json['tenure_days'] is int ? json['tenure_days'] : int.tryParse(json['tenure_days'].toString()) ?? 90,
      interestRateApr: (json['interest_rate_apr'] is num) ? (json['interest_rate_apr'] as num).toDouble() : double.tryParse(json['interest_rate_apr'].toString()) ?? 24.0,
      monthlyAmortization: (json['monthly_amortization'] is num) ? (json['monthly_amortization'] as num).toDouble() : double.tryParse(json['monthly_amortization'].toString()) ?? 0.0,
      totalRepayment: (json['total_repayment'] is num) ? (json['total_repayment'] as num).toDouble() : double.tryParse(json['total_repayment'].toString()) ?? 0.0,
      status: json['status'] ?? 'pending',
      dueDate: json['due_date'],
      purpose: json['purpose'],
    );
  }
}
