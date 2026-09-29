import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/auth_provider.dart';
import '../../providers/theme_provider.dart';

class LoanLifecycleScreen extends StatefulWidget {
  const LoanLifecycleScreen({super.key});

  @override
  State<LoanLifecycleScreen> createState() => _LoanLifecycleScreenState();
}

class _LoanLifecycleScreenState extends State<LoanLifecycleScreen> {
  final currencyFormat = NumberFormat.currency(locale: 'en_PH', symbol: '₱');

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AuthProvider>().fetchLoanSchedule();
    });
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final theme = context.watch<ThemeProvider>();
    final isDark = theme.isDarkMode;

    final primaryColor = Theme.of(context).primaryColor;
    final cardColor = Theme.of(context).cardColor;
    final textColor = isDark ? Colors.white : const Color(0xFF0F172A);
    final subtextColor = isDark ? const Color(0xFF90E0EF) : const Color(0xFF64748B);

    final loan = auth.activeLoan;
    final schedule = auth.repaymentSchedule;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Loan Lifecycle & Repayment'),
        actions: [
          IconButton(
            icon: Icon(isDark ? Icons.light_mode_outlined : Icons.dark_mode_outlined),
            tooltip: isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode',
            onPressed: () => theme.toggleTheme(),
          ),
        ],
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              if (loan != null) ...[
                // Active Loan Overview Card
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: cardColor,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: primaryColor.withValues(alpha: 0.4)),
                    boxShadow: [
                      BoxShadow(
                        color: primaryColor.withValues(alpha: isDark ? 0.1 : 0.05),
                        blurRadius: 16,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            'CURRENT APPROVED LOAN',
                            style: TextStyle(
                              letterSpacing: 1.2,
                              fontSize: 12,
                              color: primaryColor,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: const Color(0xFF10B981).withValues(alpha: 0.15),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              loan.status.toUpperCase(),
                              style: const TextStyle(
                                color: Color(0xFF10B981),
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Text(
                        currencyFormat.format(loan.totalRepayment),
                        style: TextStyle(
                          fontSize: 32,
                          fontWeight: FontWeight.bold,
                          color: textColor,
                        ),
                      ),
                      Text(
                        'Total Balance with 24% APR Interest',
                        style: TextStyle(color: subtextColor, fontSize: 12),
                      ),
                      Divider(color: isDark ? const Color(0xFF283A5A) : const Color(0xFFE2E8F0), height: 24),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          _buildDetailItem('Principal', currencyFormat.format(loan.amount), textColor, subtextColor),
                          _buildDetailItem('Tenure', '${loan.tenureDays} Days', textColor, subtextColor),
                          _buildDetailItem('Monthly', currencyFormat.format(loan.monthlyAmortization), textColor, subtextColor),
                        ],
                      ),
                      const SizedBox(height: 12),
                      if (loan.dueDate != null)
                        Text(
                          'Next Settlement Due: ${loan.dueDate}',
                          style: TextStyle(color: primaryColor, fontSize: 12, fontWeight: FontWeight.w600),
                        ),
                    ],
                  ),
                ),
                const SizedBox(height: 24),

                // Repayment Schedule & Installments
                Text(
                  'Repayment Schedule Breakdown',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: textColor),
                ),
                const SizedBox(height: 4),
                Text(
                  'Compliant with Google Financial Services: 61–180 day equal amortizations.',
                  style: TextStyle(color: subtextColor, fontSize: 12),
                ),
                const SizedBox(height: 16),
                if (schedule.isNotEmpty)
                  ...schedule.map((item) {
                    return Container(
                      margin: const EdgeInsets.only(bottom: 10),
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: cardColor,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(
                          color: isDark ? const Color(0xFF283A5A) : const Color(0xFFE2E8F0),
                        ),
                      ),
                      child: Row(
                        children: [
                          CircleAvatar(
                            radius: 18,
                            backgroundColor: isDark ? const Color(0xFF0B1325) : const Color(0xFFF1F5F9),
                            child: Text(
                              '#${item.installmentNumber}',
                              style: TextStyle(color: primaryColor, fontSize: 12, fontWeight: FontWeight.bold),
                            ),
                          ),
                          const SizedBox(width: 14),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Installment ${item.installmentNumber}',
                                  style: TextStyle(color: textColor, fontWeight: FontWeight.bold, fontSize: 14),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  'Due Date: ${item.dueDate}',
                                  style: TextStyle(color: subtextColor, fontSize: 12),
                                ),
                              ],
                            ),
                          ),
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              Text(
                                currencyFormat.format(item.amount),
                                style: TextStyle(
                                  color: primaryColor,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 15,
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                item.status.toUpperCase(),
                                style: TextStyle(
                                  color: item.status == 'paid' ? const Color(0xFF10B981) : subtextColor,
                                  fontSize: 11,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    );
                  })
                else
                  Container(
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      color: cardColor,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: isDark ? const Color(0xFF283A5A) : const Color(0xFFE2E8F0)),
                    ),
                    child: Column(
                      children: [
                        _buildInstallmentRow(1, currencyFormat.format(loan.monthlyAmortization), '30 days', 'Upcoming', primaryColor, textColor, subtextColor),
                        Divider(color: isDark ? const Color(0xFF283A5A) : const Color(0xFFE2E8F0)),
                        _buildInstallmentRow(2, currencyFormat.format(loan.monthlyAmortization), '60 days', 'Scheduled', primaryColor, textColor, subtextColor),
                        Divider(color: isDark ? const Color(0xFF283A5A) : const Color(0xFFE2E8F0)),
                        _buildInstallmentRow(3, currencyFormat.format(loan.monthlyAmortization), '90 days', 'Scheduled', primaryColor, textColor, subtextColor),
                      ],
                    ),
                  ),
              ] else ...[
                // No active loan view
                Container(
                  padding: const EdgeInsets.all(32),
                  decoration: BoxDecoration(
                    color: cardColor,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: isDark ? const Color(0xFF283A5A) : const Color(0xFFE2E8F0)),
                  ),
                  child: Column(
                    children: [
                      Icon(Icons.credit_card_off_outlined, color: primaryColor, size: 56),
                      const SizedBox(height: 16),
                      Text(
                        'No Active Loan',
                        style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: textColor),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'You currently do not have an active micro-loan. You can request a loan from your approved credit ceiling on the Dashboard.',
                        textAlign: TextAlign.center,
                        style: TextStyle(color: subtextColor, fontSize: 13, height: 1.4),
                      ),
                      const SizedBox(height: 24),
                      ElevatedButton(
                        onPressed: () => Navigator.pop(context),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: primaryColor,
                          foregroundColor: isDark ? const Color(0xFF0B1325) : Colors.white,
                          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
                        ),
                        child: const Text('Back to Dashboard', style: TextStyle(fontWeight: FontWeight.bold)),
                      ),
                    ],
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDetailItem(String label, String value, Color textColor, Color subtextColor) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: TextStyle(color: subtextColor, fontSize: 11)),
        const SizedBox(height: 2),
        Text(value, style: TextStyle(color: textColor, fontWeight: FontWeight.bold, fontSize: 14)),
      ],
    );
  }

  Widget _buildInstallmentRow(int num, String amount, String period, String status, Color primaryColor, Color textColor, Color subtextColor) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text('Installment #$num ($period)', style: TextStyle(color: subtextColor, fontSize: 13)),
          Text('$amount ($status)', style: TextStyle(color: primaryColor, fontWeight: FontWeight.bold, fontSize: 13)),
        ],
      ),
    );
  }
}
