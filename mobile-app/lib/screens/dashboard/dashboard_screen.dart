import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/auth_provider.dart';
import '../../providers/theme_provider.dart';
import '../loan/loan_lifecycle_screen.dart';
import '../compliance/privacy_policy_screen.dart';
import '../auth/login_screen.dart';
import '../onboarding/consent_modal_screen.dart';
import 'worker_profile_sheet.dart';
import '../../widgets/finclude_logo.dart';
import '../../models/credit_score_model.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  final currencyFormat = NumberFormat.currency(locale: 'en_PH', symbol: '₱');
  bool _isWalletBalanceVisible = true;
  bool _isUserInfoVisible = true;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AuthProvider>().fetchDashboard();
    });
  }

  void _showRequestLoanModal([double? overrideCeiling]) {
    final auth = Provider.of<AuthProvider>(context, listen: false);
    final theme = Provider.of<ThemeProvider>(context, listen: false);
    final isDark = theme.isDarkMode;
    final score = auth.creditScore;
    final ceiling = overrideCeiling ?? score?.approvedCeiling ?? 15000.0;

    double selectedAmount = (ceiling > 5000 ? 5000 : ceiling);
    int selectedTenure = 90; // Default 90 days (compliant: 61 - 180 days)
    String selectedPurpose = 'Working capital & utility cash cushion';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: isDark ? const Color(0xFF152238) : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
      ),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            final apr = 24.0;
            final interest = selectedAmount * (apr / 100.0) * (selectedTenure / 365.0);
            final totalRepayment = selectedAmount + interest;
            final months = selectedTenure / 30.0;
            final monthlyAmortization = totalRepayment / months;

            final textColor = isDark ? Colors.white : const Color(0xFF0F172A);
            final subtextColor = isDark ? const Color(0xFF90E0EF) : const Color(0xFF64748B);
            final primaryColor = Theme.of(context).primaryColor;

            return Padding(
              padding: EdgeInsets.only(
                left: 24,
                right: 24,
                top: 24,
                bottom: MediaQuery.of(context).viewInsets.bottom + 24,
              ),
              child: SingleChildScrollView(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Request Micro-Loan',
                          style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: textColor),
                        ),
                        IconButton(
                          icon: Icon(Icons.close, color: subtextColor),
                          onPressed: () => Navigator.pop(context),
                        ),
                      ],
                    ),
                    const SizedBox(height: 6),
                    Text(
                      'Approved Credit Ceiling: ${currencyFormat.format(ceiling)}',
                      style: TextStyle(color: primaryColor, fontWeight: FontWeight.bold, fontSize: 14),
                    ),
                    const SizedBox(height: 20),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('Loan Amount', style: TextStyle(color: subtextColor)),
                        Text(
                          currencyFormat.format(selectedAmount),
                          style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: primaryColor),
                        ),
                      ],
                    ),
                    Slider(
                      value: selectedAmount,
                      min: 1000.0,
                      max: ceiling > 1000.0 ? ceiling : 1000.0,
                      divisions: ceiling > 1000.0 ? ((ceiling - 1000) ~/ 500).clamp(1, 100) : 1,
                      activeColor: primaryColor,
                      inactiveColor: isDark ? const Color(0xFF283A5A) : const Color(0xFFCBD5E1),
                      onChanged: (val) => setModalState(() => selectedAmount = val),
                    ),
                    const SizedBox(height: 12),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('Tenure (Google Play Compliant)', style: TextStyle(color: subtextColor)),
                        Text(
                          '$selectedTenure Days',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: textColor),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [61, 90, 120, 180].map((days) {
                        final isSelected = selectedTenure == days;
                        return Expanded(
                          child: Padding(
                            padding: const EdgeInsets.symmetric(horizontal: 4.0),
                            child: ChoiceChip(
                              label: Text(
                                '$days d',
                                style: TextStyle(
                                  color: isSelected
                                      ? (isDark ? const Color(0xFF0B1325) : Colors.white)
                                      : textColor,
                                  fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                                ),
                              ),
                              selected: isSelected,
                              selectedColor: primaryColor,
                              backgroundColor: isDark ? const Color(0xFF0B1325) : const Color(0xFFF1F5F9),
                              onSelected: (_) => setModalState(() => selectedTenure = days),
                            ),
                          ),
                        );
                      }).toList(),
                    ),
                    const SizedBox(height: 16),
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: isDark ? const Color(0xFF0B1325) : const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(
                          color: isDark ? const Color(0xFF283A5A) : const Color(0xFFE2E8F0),
                        ),
                      ),
                      child: Column(
                        children: [
                          _buildDisclosureRow('Annual Percentage Rate (APR)', '24.0% p.a.', isDark),
                          const SizedBox(height: 6),
                          _buildDisclosureRow('Estimated Total Interest', currencyFormat.format(interest), isDark),
                          const SizedBox(height: 6),
                          _buildDisclosureRow('Total Repayment', currencyFormat.format(totalRepayment), isDark),
                          Divider(color: isDark ? const Color(0xFF283A5A) : const Color(0xFFE2E8F0), height: 16),
                          _buildDisclosureRow(
                            'Monthly Amortization',
                            currencyFormat.format(monthlyAmortization),
                            isDark,
                            highlight: true,
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 24),
                    ElevatedButton(
                      onPressed: () async {
                        Navigator.pop(context);
                        final res = await auth.requestLoan(
                          amount: selectedAmount,
                          tenureDays: selectedTenure,
                          purpose: selectedPurpose,
                        );
                        if (!mounted) return;
                        if (res) {
                          ScaffoldMessenger.of(this.context).showSnackBar(
                            SnackBar(
                              backgroundColor: primaryColor,
                              content: Text(
                                'Loan Approved!',
                                style: TextStyle(
                                  color: isDark ? const Color(0xFF0D1B2A) : Colors.white,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          );
                          Navigator.push(
                            this.context,
                            MaterialPageRoute(builder: (_) => const LoanLifecycleScreen()),
                          );
                        } else {
                          ScaffoldMessenger.of(this.context).showSnackBar(
                            SnackBar(content: Text(auth.errorMessage ?? 'Application failed.')),
                          );
                        }
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: primaryColor,
                        foregroundColor: isDark ? const Color(0xFF0B1325) : Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 16),
                      ),
                      child: const Text('Confirm & Accept Loan Offer', style: TextStyle(fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildDisclosureRow(String label, String value, bool isDark, {bool highlight = false}) {
    final textColor = isDark ? Colors.white : const Color(0xFF0F172A);
    final subtextColor = isDark ? const Color(0xFF90E0EF) : const Color(0xFF64748B);
    final primaryColor = Theme.of(context).primaryColor;

    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: TextStyle(color: highlight ? primaryColor : subtextColor, fontSize: 13)),
        Text(
          value,
          style: TextStyle(
            color: highlight ? primaryColor : textColor,
            fontWeight: highlight ? FontWeight.bold : FontWeight.w600,
            fontSize: highlight ? 14 : 13,
          ),
        ),
      ],
    );
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

    final score = auth.creditScore;
    final user = auth.user;

    // XAI Factors list (using dynamic score factors, or resilient defaults)
    final defaultFactors = [
      ShapFactor(displayText: 'Strong verified monthly income (₱38,000)', impactPoints: 13, direction: 'positive'),
      ShapFactor(displayText: 'Consistent on-time utility payments', impactPoints: 10, direction: 'positive'),
      ShapFactor(displayText: 'Verified gig platform earnings', impactPoints: 8, direction: 'positive'),
      ShapFactor(displayText: 'Disciplined day-to-day spending trajectory', impactPoints: 5, direction: 'positive'),
    ];
    final factors = (score != null && score.shapFactors.isNotEmpty) ? score.shapFactors : defaultFactors;

    final int currentScore = score?.score ?? 82;

    int totalPositiveImpact = 0;
    int totalNegativeImpact = 0;
    for (final f in factors) {
      if (f.direction == 'positive') {
        totalPositiveImpact += f.impactPoints.abs();
      } else {
        totalNegativeImpact += f.impactPoints.abs();
      }
    }

    // Dynamic Tier and Color derived directly from currentScore
    final Color scoreColor;
    final String tierLabel;
    if (currentScore >= 80) {
      scoreColor = const Color(0xFF10B981); // Green — Prime
      tierLabel = score?.riskTier.isNotEmpty == true ? score!.riskTier : 'Prime (Low Risk)';
    } else if (currentScore >= 60) {
      scoreColor = const Color(0xFFF59E0B); // Amber — Standard
      tierLabel = score?.riskTier.isNotEmpty == true ? score!.riskTier : 'Standard (Moderate Risk)';
    } else {
      scoreColor = const Color(0xFFEF4444); // Red — Subprime / High Risk
      tierLabel = score?.riskTier.isNotEmpty == true ? score!.riskTier : 'Subprime (High Risk)';
    }

    final double ceiling = score?.approvedCeiling ?? 25000.0;
    final double disposableCash = score?.projectedDisposableCashFlow ?? 18500.0;

    return Scaffold(
      appBar: AppBar(
        automaticallyImplyLeading: false,
        title: Row(
          mainAxisSize: MainAxisSize.min,
          children: const [
            FincludeLogo(size: 28),
            SizedBox(width: 8),
            Flexible(
              child: Text(
                'Finclude-AI',
                style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: Icon(isDark ? Icons.light_mode_outlined : Icons.dark_mode_outlined),
            tooltip: isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode',
            onPressed: () => theme.toggleTheme(),
          ),
          IconButton(
            icon: const Icon(Icons.refresh),
            tooltip: 'Refresh & Score Sync',
            onPressed: () => auth.fetchDashboard(),
          ),
          IconButton(
            icon: const Icon(Icons.privacy_tip_outlined),
            tooltip: 'Data Privacy & Terms',
            onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PrivacyPolicyScreen())),
          ),
          IconButton(
            icon: const Icon(Icons.logout),
            tooltip: 'Logout',
            onPressed: () {
              auth.logout();
              Navigator.pushAndRemoveUntil(
                context,
                MaterialPageRoute(builder: (_) => const LoginScreen()),
                (route) => false,
              );
            },
          ),
        ],
      ),
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: () => auth.fetchDashboard(),
          color: primaryColor,
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.all(20.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Header User Profile
                InkWell(
                  borderRadius: BorderRadius.circular(20),
                  onTap: () => WorkerProfileSheet.show(context),
                  child: Container(
                    padding: const EdgeInsets.all(18),
                    decoration: BoxDecoration(
                      color: cardColor,
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(
                        color: isDark ? const Color(0xFF283A5A) : const Color(0xFFE2E8F0),
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: isDark ? 0.2 : 0.04),
                          blurRadius: 16,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: Row(
                      children: [
                        CircleAvatar(
                          radius: 26,
                          backgroundColor: primaryColor,
                          child: _isUserInfoVisible
                              ? Text(
                                  (user?.name.isNotEmpty == true ? user!.name[0] : 'G').toUpperCase(),
                                  style: TextStyle(
                                    fontSize: 22,
                                    fontWeight: FontWeight.bold,
                                    color: isDark ? const Color(0xFF0B1325) : Colors.white,
                                  ),
                                )
                              : Icon(
                                  Icons.person_rounded,
                                  size: 26,
                                  color: isDark ? const Color(0xFF0B1325) : Colors.white,
                                ),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Flexible(
                                    child: Text(
                                      _isUserInfoVisible
                                          ? (user?.name ?? 'Gig Worker Partner')
                                          : '• • • • • • • •',
                                      style: TextStyle(
                                        fontSize: 17,
                                        fontWeight: FontWeight.bold,
                                        color: textColor,
                                        letterSpacing: _isUserInfoVisible ? 0 : 2.0,
                                      ),
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ),
                                  const SizedBox(width: 6),
                                  InkWell(
                                    borderRadius: BorderRadius.circular(16),
                                    onTap: () => setState(() => _isUserInfoVisible = !_isUserInfoVisible),
                                    child: Padding(
                                      padding: const EdgeInsets.all(4.0),
                                      child: Icon(
                                        _isUserInfoVisible
                                            ? Icons.visibility_outlined
                                            : Icons.visibility_off_outlined,
                                        size: 18,
                                        color: subtextColor,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 2),
                              Text(
                                _isUserInfoVisible
                                    ? (user?.email ?? 'user@finclude.ph')
                                    : '•••••••••••••@••••.com',
                                style: TextStyle(
                                  fontSize: 12,
                                  color: subtextColor,
                                  letterSpacing: _isUserInfoVisible ? 0 : 1.2,
                                ),
                                overflow: TextOverflow.ellipsis,
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 8),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.end,
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                              decoration: BoxDecoration(
                                color: primaryColor.withValues(alpha: 0.15),
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(color: primaryColor),
                              ),
                              child: Text(
                                'RA 10173 Verified',
                                style: TextStyle(color: primaryColor, fontSize: 11, fontWeight: FontWeight.bold),
                              ),
                            ),
                            const SizedBox(height: 4),
                            Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Text(
                                  'View Profile',
                                  style: TextStyle(fontSize: 11, color: primaryColor, fontWeight: FontWeight.w600),
                                ),
                                Icon(Icons.chevron_right_rounded, size: 14, color: primaryColor),
                              ],
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 20),

                // Finclude Wallet Balance Card
                _buildWalletBalanceCard(
                  auth: auth,
                  isDark: isDark,
                  cardColor: cardColor,
                  textColor: textColor,
                  subtextColor: subtextColor,
                  primaryColor: primaryColor,
                ),

                const SizedBox(height: 20),

                // Radial Credit Score Gauge Card
                Container(
                  padding: const EdgeInsets.all(24),
                  decoration: BoxDecoration(
                    color: cardColor,
                    borderRadius: BorderRadius.circular(24),
                    border: Border.all(
                      color: isDark ? scoreColor.withValues(alpha: 0.35) : const Color(0xFFE2E8F0),
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: scoreColor.withValues(alpha: isDark ? 0.16 : 0.10),
                        blurRadius: 24,
                        offset: const Offset(0, 8),
                      ),
                    ],
                  ),
                  child: Column(
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.auto_awesome, size: 16, color: scoreColor),
                          const SizedBox(width: 8),
                          Text(
                            'XAI ALTERNATIVE CREDIT SCORE',
                            style: TextStyle(
                              letterSpacing: 1.5,
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              color: scoreColor,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 18),

                      // Animated Gauge ring and counter
                      TweenAnimationBuilder<double>(
                        tween: Tween<double>(begin: 0.0, end: currentScore.toDouble()),
                        duration: const Duration(milliseconds: 700),
                        curve: Curves.easeOutCubic,
                        builder: (context, animValue, _) {
                          final displayScore = animValue.round();
                          return Stack(
                            alignment: Alignment.center,
                            children: [
                              SizedBox(
                                width: 144,
                                height: 144,
                                child: CircularProgressIndicator(
                                  value: (animValue / 100.0).clamp(0.0, 1.0),
                                  strokeWidth: 13,
                                  backgroundColor: isDark ? const Color(0xFF283A5A) : const Color(0xFFE2E8F0),
                                  valueColor: AlwaysStoppedAnimation<Color>(scoreColor),
                                ),
                              ),
                              Column(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Text(
                                    '$displayScore',
                                    style: TextStyle(
                                      fontSize: 46,
                                      fontWeight: FontWeight.bold,
                                      color: scoreColor,
                                    ),
                                  ),
                                  Text(
                                    '/100',
                                    style: TextStyle(fontSize: 14, color: subtextColor, fontWeight: FontWeight.w500),
                                  ),
                                ],
                              ),
                            ],
                          );
                        },
                      ),
                      const SizedBox(height: 16),

                      // Risk tier badge
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                        decoration: BoxDecoration(
                          color: scoreColor,
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          'Risk Tier: $tierLabel',
                          style: TextStyle(
                            color: isDark ? const Color(0xFF0B1325) : Colors.white,
                            fontWeight: FontWeight.bold,
                            fontSize: 13,
                          ),
                        ),
                      ),
                      const SizedBox(height: 12),

                      Text(
                        'Projected Net Disposable Cash: ${currencyFormat.format(disposableCash)} / mo',
                        style: TextStyle(color: subtextColor, fontSize: 13),
                      ),
                      const SizedBox(height: 16),

                      // XAI Score Attribution Breakdown
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: isDark ? const Color(0xFF0D1B2A) : const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(
                            color: isDark ? const Color(0xFF283A5A) : const Color(0xFFE2E8F0),
                          ),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                          children: [
                            Row(
                              children: [
                                const Icon(Icons.arrow_upward, size: 14, color: Color(0xFF10B981)),
                                const SizedBox(width: 4),
                                Text(
                                  '+$totalPositiveImpact pts',
                                  style: const TextStyle(
                                    color: Color(0xFF10B981),
                                    fontWeight: FontWeight.bold,
                                    fontSize: 13,
                                  ),
                                ),
                                const SizedBox(width: 4),
                                Text('Positive Factors', style: TextStyle(color: subtextColor, fontSize: 11)),
                              ],
                            ),
                            Container(
                              width: 1,
                              height: 18,
                              color: isDark ? const Color(0xFF283A5A) : const Color(0xFFE2E8F0),
                            ),
                            Row(
                              children: [
                                const Icon(Icons.arrow_downward, size: 14, color: Color(0xFFEF4444)),
                                const SizedBox(width: 4),
                                Text(
                                  '-$totalNegativeImpact pts',
                                  style: const TextStyle(
                                    color: Color(0xFFEF4444),
                                    fontWeight: FontWeight.bold,
                                    fontSize: 13,
                                  ),
                                ),
                                const SizedBox(width: 4),
                                Text('Risk Deductions', style: TextStyle(color: subtextColor, fontSize: 11)),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),

                // Stability Dashboard & Cash-Flow Trends Card
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: cardColor,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(
                      color: isDark ? const Color(0xFF283A5A) : const Color(0xFFE2E8F0),
                    ),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            'Financial Stability & Trends',
                            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: textColor),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: const Color(0xFF10B981).withValues(alpha: 0.15),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: const Text(
                              'High Reliability',
                              style: TextStyle(color: Color(0xFF10B981), fontSize: 11, fontWeight: FontWeight.bold),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Expanded(
                            child: Container(
                              padding: const EdgeInsets.all(14),
                              decoration: BoxDecoration(
                                color: isDark ? const Color(0xFF1E293B) : const Color(0xFFF8FAFC),
                                borderRadius: BorderRadius.circular(14),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: [
                                      const Icon(Icons.speed, size: 16, color: Color(0xFF10B981)),
                                      const SizedBox(width: 6),
                                      Text('Burn Rate', style: TextStyle(fontSize: 12, color: subtextColor)),
                                    ],
                                  ),
                                  const SizedBox(height: 6),
                                  Text(
                                    '0.85x',
                                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: textColor),
                                  ),
                                  Text(
                                    'Controlled spending',
                                    style: TextStyle(fontSize: 11, color: const Color(0xFF10B981)),
                                  ),
                                ],
                              ),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Container(
                              padding: const EdgeInsets.all(14),
                              decoration: BoxDecoration(
                                color: isDark ? const Color(0xFF1E293B) : const Color(0xFFF8FAFC),
                                borderRadius: BorderRadius.circular(14),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: [
                                      Icon(Icons.auto_graph, size: 16, color: primaryColor),
                                      const SizedBox(width: 6),
                                      Text('Consistency', style: TextStyle(fontSize: 12, color: subtextColor)),
                                    ],
                                  ),
                                  const SizedBox(height: 6),
                                  Text(
                                    '92%',
                                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: textColor),
                                  ),
                                  Text(
                                    'Predictable payouts',
                                    style: TextStyle(fontSize: 11, color: primaryColor),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      Text('Cash-Flow Trajectory (Past 4 Weeks)', style: TextStyle(fontSize: 12, color: subtextColor)),
                      const SizedBox(height: 10),
                      // Cash flow bar trend visualization
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          _buildTrendBar('W1', 0.65, '₱28k', primaryColor, subtextColor, isDark),
                          const SizedBox(width: 10),
                          _buildTrendBar('W2', 0.78, '₱34k', primaryColor, subtextColor, isDark),
                          const SizedBox(width: 10),
                          _buildTrendBar('W3', 0.72, '₱31k', primaryColor, subtextColor, isDark),
                          const SizedBox(width: 10),
                          _buildTrendBar('W4 (Now)', 0.90, '₱38k', const Color(0xFF10B981), subtextColor, isDark),
                        ],
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),

                // Approved Loan Ceiling Banner
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: cardColor,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(
                      color: isDark ? scoreColor.withValues(alpha: 0.5) : const Color(0xFFE2E8F0),
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: scoreColor.withValues(alpha: isDark ? 0.08 : 0.04),
                        blurRadius: 16,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Approved Loan Offer Ceiling',
                              style: TextStyle(color: subtextColor, fontSize: 13),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              currencyFormat.format(ceiling),
                              style: TextStyle(
                                fontSize: 28,
                                fontWeight: FontWeight.bold,
                                color: scoreColor,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              '24% APR • Repayment: 61–180 Days',
                              style: TextStyle(color: subtextColor, fontSize: 11),
                            ),
                          ],
                        ),
                      ),
                      ElevatedButton(
                        onPressed: () => _showRequestLoanModal(ceiling),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: scoreColor,
                          foregroundColor: isDark ? const Color(0xFF0B1325) : Colors.white,
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                        ),
                        child: const Text('Request Loan', style: TextStyle(fontWeight: FontWeight.bold)),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 24),

                // Explainable AI (SHAP) Score Factors Breakdown
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Explainable AI (SHAP) Score Factors',
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: textColor),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                ...factors.map((factor) {
                  final isPositive = factor.direction == 'positive';
                  final impactColor = isPositive ? const Color(0xFF10B981) : const Color(0xFFEF4444);
                  final barFraction = (factor.impactPoints.abs() / 15.0).clamp(0.0, 1.0);

                  return Container(
                    margin: const EdgeInsets.only(bottom: 10),
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                    decoration: BoxDecoration(
                      color: cardColor,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(
                        color: isPositive
                            ? const Color(0xFF10B981).withValues(alpha: 0.35)
                            : const Color(0xFFEF4444).withValues(alpha: 0.35),
                      ),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Icon(
                              isPositive ? Icons.trending_up : Icons.trending_down,
                              color: impactColor,
                              size: 18,
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Text(
                                factor.displayText,
                                style: TextStyle(
                                  color: textColor,
                                  fontSize: 13,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                            ),
                            const SizedBox(width: 8),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: impactColor.withValues(alpha: 0.15),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                '${isPositive ? '+' : '-'}${factor.impactPoints.abs()} pts',
                                style: TextStyle(
                                  color: impactColor,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 12,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        ClipRRect(
                          borderRadius: BorderRadius.circular(4),
                          child: LinearProgressIndicator(
                            value: barFraction,
                            minHeight: 5,
                            backgroundColor: isDark ? const Color(0xFF283A5A) : const Color(0xFFE2E8F0),
                            valueColor: AlwaysStoppedAnimation<Color>(impactColor.withValues(alpha: 0.8)),
                          ),
                        ),
                      ],
                    ),
                  );
                }),

                const SizedBox(height: 20),

                // Active Loan / Loan Lifecycle Quick Link
                ElevatedButton.icon(
                  onPressed: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const LoanLifecycleScreen()),
                    );
                  },
                  icon: const Icon(Icons.history_outlined),
                  label: const Text('View Active Loan & Repayment Lifecycle'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: cardColor,
                    foregroundColor: primaryColor,
                    side: BorderSide(color: primaryColor),
                    padding: const EdgeInsets.symmetric(vertical: 14),
                  ),
                ),
                const SizedBox(height: 12),
                OutlinedButton.icon(
                  onPressed: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const ConsentModalScreen()),
                    );
                  },
                  icon: const Icon(Icons.link_rounded),
                  label: const Text('Re-link Smile Account / Consent'),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: subtextColor,
                    side: BorderSide(color: isDark ? const Color(0xFF283A5A) : const Color(0xFFCBD5E1)),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildTrendBar(String label, double heightFactor, String amount, Color barColor, Color subtextColor, bool isDark) {
    return Expanded(
      child: Column(
        children: [
          Text(amount, style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: barColor)),
          const SizedBox(height: 4),
          Container(
            height: 48 * heightFactor,
            decoration: BoxDecoration(
              color: barColor,
              borderRadius: BorderRadius.circular(6),
            ),
          ),
          const SizedBox(height: 6),
          Text(label, style: TextStyle(fontSize: 10, color: subtextColor)),
        ],
      ),
    );
  }

  Widget _buildWalletBalanceCard({
    required AuthProvider auth,
    required bool isDark,
    required Color cardColor,
    required Color textColor,
    required Color subtextColor,
    required Color primaryColor,
  }) {
    final balance = auth.walletBalance;
    final inflow = auth.totalInflow;
    final outflow = auth.totalOutflow;

    return Container(
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: cardColor,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(
          color: isDark ? primaryColor.withValues(alpha: 0.3) : const Color(0xFFE2E8F0),
        ),
        boxShadow: [
          BoxShadow(
            color: primaryColor.withValues(alpha: isDark ? 0.14 : 0.08),
            blurRadius: 20,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: primaryColor.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Icon(Icons.account_balance_wallet_rounded, color: primaryColor, size: 20),
                  ),
                  const SizedBox(width: 10),
                  Text(
                    'FINCLUDE WALLET BALANCE',
                    style: TextStyle(
                      letterSpacing: 1.2,
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      color: primaryColor,
                    ),
                  ),
                ],
              ),
              IconButton(
                icon: Icon(
                  _isWalletBalanceVisible ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                  color: subtextColor,
                  size: 20,
                ),
                tooltip: _isWalletBalanceVisible ? 'Hide Balance' : 'Show Balance',
                onPressed: () => setState(() => _isWalletBalanceVisible = !_isWalletBalanceVisible),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Text(
            _isWalletBalanceVisible ? currencyFormat.format(balance) : '₱ • • • • • •',
            style: TextStyle(
              fontSize: 34,
              fontWeight: FontWeight.bold,
              color: textColor,
              letterSpacing: _isWalletBalanceVisible ? -0.5 : 2.0,
            ),
          ),
          const SizedBox(height: 4),
          Row(
            children: [
              Container(
                width: 8,
                height: 8,
                decoration: const BoxDecoration(
                  color: Color(0xFF10B981),
                  shape: BoxShape.circle,
                ),
              ),
              const SizedBox(width: 6),
              Text(
                'Available Approved & Inflow Funds',
                style: TextStyle(fontSize: 12, color: subtextColor),
              ),
            ],
          ),
          const SizedBox(height: 18),
          Divider(color: isDark ? const Color(0xFF283A5A) : const Color(0xFFE2E8F0)),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: isDark ? const Color(0xFF0D1B2A) : const Color(0xFFF8FAFC),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(
                      color: isDark ? const Color(0xFF283A5A) : const Color(0xFFE2E8F0),
                    ),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          const Icon(Icons.arrow_downward_rounded, color: Color(0xFF10B981), size: 16),
                          const SizedBox(width: 4),
                          Text(
                            'Total Inflows',
                            style: TextStyle(fontSize: 11, color: subtextColor, fontWeight: FontWeight.w600),
                          ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Text(
                        _isWalletBalanceVisible ? '+${currencyFormat.format(inflow)}' : '₱ • • • •',
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                          color: Color(0xFF10B981),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: isDark ? const Color(0xFF0D1B2A) : const Color(0xFFF8FAFC),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(
                      color: isDark ? const Color(0xFF283A5A) : const Color(0xFFE2E8F0),
                    ),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          const Icon(Icons.arrow_upward_rounded, color: Color(0xFFFF6B6B), size: 16),
                          const SizedBox(width: 4),
                          Text(
                            'Total Outflows',
                            style: TextStyle(fontSize: 11, color: subtextColor, fontWeight: FontWeight.w600),
                          ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Text(
                        _isWalletBalanceVisible ? '-${currencyFormat.format(outflow)}' : '₱ • • • •',
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                          color: Color(0xFFFF6B6B),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
