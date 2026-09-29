import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/theme_provider.dart';
import '../smile/smile_webview_screen.dart';
import '../compliance/privacy_policy_screen.dart';
import '../../widgets/finclude_logo.dart';

class ConsentModalScreen extends StatefulWidget {
  const ConsentModalScreen({super.key});

  @override
  State<ConsentModalScreen> createState() => _ConsentModalScreenState();
}

class _ConsentModalScreenState extends State<ConsentModalScreen> {
  bool _agreedToTerms = false;
  bool _agreedToSmileSync = false;

  void _proceedToAccountLink() async {
    if (!_agreedToTerms || !_agreedToSmileSync) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please check both consent agreements to proceed in accordance with RA 10173.'),
        ),
      );
      return;
    }

    final auth = Provider.of<AuthProvider>(context, listen: false);
    final success = await auth.submitConsent();
    if (!mounted) return;

    if (success) {
      // Fetch user token for Smile Wink
      final token = await auth.fetchSmileToken();
      if (!mounted) return;

      Navigator.of(context).pushReplacement(
        MaterialPageRoute(
          builder: (_) => SmileWebviewScreen(userToken: token ?? 'sandbox_token_demo'),
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Failed to record consent. Please retry.')),
      );
    }
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

    return Scaffold(
      appBar: AppBar(
        title: const Text('Data Privacy & Consent'),
        actions: [
          IconButton(
            icon: Icon(isDark ? Icons.light_mode_outlined : Icons.dark_mode_outlined),
            tooltip: isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode',
            onPressed: () => theme.toggleTheme(),
          ),
          IconButton(
            icon: const Icon(Icons.description_outlined),
            tooltip: 'View Full Privacy Policy',
            onPressed: () {
              Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const PrivacyPolicyScreen()),
              );
            },
          ),
        ],
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Container(
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  color: cardColor,
                  borderRadius: BorderRadius.circular(18),
                  border: Border.all(color: primaryColor.withValues(alpha: 0.3)),
                  boxShadow: [
                    BoxShadow(
                      color: primaryColor.withValues(alpha: isDark ? 0.1 : 0.04),
                      blurRadius: 16,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    const FincludeLogo(size: 42),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'RA 10173 Compliance',
                            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: textColor),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Philippine Data Privacy Act of 2012 Statutory Authorization',
                            style: TextStyle(fontSize: 12, color: subtextColor),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),
              Text(
                'How Finclude-AI Evaluates Your Credit',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: textColor),
              ),
              const SizedBox(height: 10),
              Text(
                'Because traditional banks often exclude gig workers, freelancers, and micro-entrepreneurs without formal payslips, Finclude-AI uses verifiable transaction data through Smile Open Banking to calculate alternative creditworthiness.',
                style: TextStyle(fontSize: 13, color: subtextColor, height: 1.5),
              ),
              const SizedBox(height: 18),
              _buildScopeItem(
                icon: Icons.payments_outlined,
                title: 'Income & Inflow Velocity',
                description: 'Verifies payroll, freelance payouts, and remittances to calculate real disposable income.',
                primaryColor: primaryColor,
                cardColor: cardColor,
                textColor: textColor,
                subtextColor: subtextColor,
                isDark: isDark,
              ),
              const SizedBox(height: 12),
              _buildScopeItem(
                icon: Icons.bolt_outlined,
                title: 'Utility Bill Consistency',
                description: 'Tracks recency and on-time settlement of electricity (Meralco), water (Maynilad), and telco bills.',
                primaryColor: primaryColor,
                cardColor: cardColor,
                textColor: textColor,
                subtextColor: subtextColor,
                isDark: isDark,
              ),
              const SizedBox(height: 12),
              _buildScopeItem(
                icon: Icons.account_balance_outlined,
                title: 'Debt-to-Income (DTI) Burden',
                description: 'Calculates debt repayment habits to ensure your loan offer ceiling never causes financial distress.',
                primaryColor: primaryColor,
                cardColor: cardColor,
                textColor: textColor,
                subtextColor: subtextColor,
                isDark: isDark,
              ),
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: const Color(0xFF10B981).withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: const Color(0xFF10B981).withValues(alpha: 0.35)),
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Icon(Icons.shield_outlined, color: Color(0xFF10B981), size: 22),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Data Minimization Guardrail (RA 10173)',
                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF10B981)),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Finclude-AI strictly adheres to data minimization. We never request, process, or store your banking passwords, security PINs, or One-Time Passwords (OTPs). All connections are handled directly via OAuth Open Banking tokens.',
                            style: TextStyle(fontSize: 11.5, color: isDark ? Colors.white70 : const Color(0xFF1E293B), height: 1.4),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: cardColor,
                  borderRadius: BorderRadius.circular(18),
                  border: Border.all(color: isDark ? const Color(0xFF283A5A) : const Color(0xFFE2E8F0)),
                ),
                child: Column(
                  children: [
                    CheckboxListTile(
                      value: _agreedToTerms,
                      activeColor: primaryColor,
                      checkColor: isDark ? const Color(0xFF0D1B2A) : Colors.white,
                      contentPadding: EdgeInsets.zero,
                      title: Text(
                        'I explicitly authorize Finclude-AI to securely process my transactional and utility records in compliance with RA 10173.',
                        style: TextStyle(color: textColor, fontSize: 13),
                      ),
                      onChanged: (val) => setState(() => _agreedToTerms = val ?? false),
                    ),
                    Divider(color: isDark ? const Color(0xFF283A5A) : const Color(0xFFE2E8F0)),
                    CheckboxListTile(
                      value: _agreedToSmileSync,
                      activeColor: primaryColor,
                      checkColor: isDark ? const Color(0xFF0D1B2A) : Colors.white,
                      contentPadding: EdgeInsets.zero,
                      title: Text(
                        'I authorize Smile API to connect my financial or gig platform accounts using end-to-end encrypted Open Banking tokens.',
                        style: TextStyle(color: textColor, fontSize: 13),
                      ),
                      onChanged: (val) => setState(() => _agreedToSmileSync = val ?? false),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 28),
              ElevatedButton(
                onPressed: (_agreedToTerms && _agreedToSmileSync && !auth.isLoading)
                    ? _proceedToAccountLink
                    : null,
                style: ElevatedButton.styleFrom(
                  backgroundColor: primaryColor,
                  foregroundColor: isDark ? const Color(0xFF0D1B2A) : Colors.white,
                  disabledBackgroundColor: isDark ? const Color(0xFF283A5A) : const Color(0xFFCBD5E1),
                  disabledForegroundColor: Colors.white38,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                ),
                child: auth.isLoading
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                      )
                    : const Text(
                        'Authorize & Connect Financial Sources',
                        style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                      ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildScopeItem({
    required IconData icon,
    required String title,
    required String description,
    required Color primaryColor,
    required Color cardColor,
    required Color textColor,
    required Color subtextColor,
    required bool isDark,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          padding: const EdgeInsets.all(10),
          decoration: BoxDecoration(
            color: cardColor,
            borderRadius: BorderRadius.circular(10),
            border: Border.all(color: isDark ? const Color(0xFF283A5A) : const Color(0xFFE2E8F0)),
          ),
          child: Icon(icon, color: primaryColor, size: 20),
        ),
        const SizedBox(width: 14),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: TextStyle(fontWeight: FontWeight.bold, color: textColor, fontSize: 14),
              ),
              const SizedBox(height: 2),
              Text(
                description,
                style: TextStyle(color: subtextColor, fontSize: 12),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
