import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/theme_provider.dart';

class PrivacyPolicyScreen extends StatelessWidget {
  const PrivacyPolicyScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final theme = context.watch<ThemeProvider>();
    final isDark = theme.isDarkMode;

    final primaryColor = Theme.of(context).primaryColor;
    final textColor = isDark ? Colors.white : const Color(0xFF0F172A);
    final subtextColor = isDark ? const Color(0xFFE0E1DD) : const Color(0xFF475569);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Privacy Policy & Disclosures'),
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
          padding: const EdgeInsets.all(24.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Data Privacy Act (RA 10173) & Google Financial Services Compliance',
                style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                  color: textColor,
                  height: 1.3,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                'Effective Date: September 2026 | Version: 1.0 (SEC Reg. Compliant)',
                style: TextStyle(color: primaryColor, fontSize: 12, fontWeight: FontWeight.w600),
              ),
              const SizedBox(height: 20),

              _buildSection(
                title: '1. Personal & Financial Data We Collect',
                content:
                    'Finclude-AI collects user-provided identification information (Philippine National ID, PhilSys, passport, or government IDs) and transactional data via authorized Open Banking aggregator Smile API. This includes transaction velocity, utility bill settlement recency (electricity, water, telco), and verified income inflows.',
                primaryColor: primaryColor,
                textColor: subtextColor,
              ),
              _buildSection(
                title: '2. Purpose of Data Processing (PSITE Engine)',
                content:
                    'Collected data is processed strictly for alternative credit scoring, risk evaluation, and fraud mitigation under the PSITE feature framework. We do not sell or monetize personal data to third-party advertising brokers.',
                primaryColor: primaryColor,
                textColor: subtextColor,
              ),
              _buildSection(
                title: '3. Google Play Financial Services Policy Compliance',
                content:
                    '• Minimum Repayment Tenure: 61 Days (No payday loans < 60 days).\n'
                    '• Maximum Repayment Tenure: 180 Days.\n'
                    '• Annual Percentage Rate (APR): Fixed at 24.0% per annum.\n'
                    '• Representative Example: A micro-loan of ₱10,000 for 90 days at 24.0% APR results in total interest of ₱591.78, with a total repayment of ₱10,591.78 payable in 3 equal monthly installments of ₱3,530.59.',
                primaryColor: primaryColor,
                textColor: subtextColor,
              ),
              _buildSection(
                title: '4. Open Banking & End-to-End Encryption',
                content:
                    'Account connectivity via Smile API utilizes TLS 1.3 encryption, secure tokenization, and zero plain-text storage of bank or platform passwords. Credentials are authenticated directly via bank-grade OAuth / Widget sessions.',
                primaryColor: primaryColor,
                textColor: subtextColor,
              ),
              _buildSection(
                title: '5. Rights of the Data Subject (RA 10173)',
                content:
                    'Under the Philippine Data Privacy Act of 2012, you have the statutory right to be informed, access your credit score factor breakdown, rectify discrepancies, object to automated profiling, and revoke data access consent at any time.',
                primaryColor: primaryColor,
                textColor: subtextColor,
              ),
              _buildSection(
                title: '6. Data Protection Officer (DPO) Contact',
                content:
                    'For privacy inquiries, audit requests, or consent revocation, contact: dpo@finclude.ai / compliance@finclude.ph | Finclude Financial Technologies Inc., Bonifacio Global City, Taguig, Philippines.',
                primaryColor: primaryColor,
                textColor: subtextColor,
              ),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: () => Navigator.pop(context),
                style: ElevatedButton.styleFrom(
                  backgroundColor: primaryColor,
                  foregroundColor: isDark ? const Color(0xFF0D1B2A) : Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                ),
                child: const Center(
                  child: Text('Understood & Close', style: TextStyle(fontWeight: FontWeight.bold)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSection({required String title, required String content, required Color primaryColor, required Color textColor}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 20.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.bold,
              color: primaryColor,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            content,
            style: TextStyle(
              fontSize: 13,
              color: textColor,
              height: 1.5,
            ),
          ),
        ],
      ),
    );
  }
}
