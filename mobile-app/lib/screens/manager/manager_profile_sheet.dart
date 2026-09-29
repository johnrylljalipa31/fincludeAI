import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/theme_provider.dart';
import '../auth/login_screen.dart';

const _kGreen = Color(0xFF10B981);
const _kAmber = Color(0xFFF59E0B);
const _kRed = Color(0xFFEF4444);

class ManagerProfileSheet extends StatefulWidget {
  final Map<String, int> counts;
  final VoidCallback onRefreshQueue;

  const ManagerProfileSheet({
    super.key,
    required this.counts,
    required this.onRefreshQueue,
  });

  static void show(
    BuildContext context, {
    required Map<String, int> counts,
    required VoidCallback onRefreshQueue,
  }) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => ManagerProfileSheet(
        counts: counts,
        onRefreshQueue: onRefreshQueue,
      ),
    );
  }

  @override
  State<ManagerProfileSheet> createState() => _ManagerProfileSheetState();
}

class _ManagerProfileSheetState extends State<ManagerProfileSheet> {
  bool _isEditing = false;
  late TextEditingController _nameController;
  late TextEditingController _phoneController;
  bool _isSaving = false;

  @override
  void initState() {
    super.initState();
    final user = context.read<AuthProvider>().user;
    _nameController = TextEditingController(text: user?.name ?? '');
    _phoneController = TextEditingController(text: user?.phoneNumber ?? '+63 917 888 2401');
  }

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    super.dispose();
  }

  Future<void> _saveProfile() async {
    setState(() => _isSaving = true);
    final auth = context.read<AuthProvider>();
    final success = await auth.updateProfile(
      name: _nameController.text.trim(),
      phoneNumber: _phoneController.text.trim(),
    );
    if (!mounted) return;
    setState(() {
      _isSaving = false;
      if (success) _isEditing = false;
    });

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          success ? 'Manager profile updated successfully!' : 'Failed to update profile.',
          style: const TextStyle(fontWeight: FontWeight.w600),
        ),
        backgroundColor: success ? _kGreen : _kRed,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final theme = context.watch<ThemeProvider>();
    final isDark = theme.isDarkMode;
    final primary = Theme.of(context).primaryColor;
    final user = auth.user;

    final sheetBg = isDark ? const Color(0xFF0F1F35) : Colors.white;
    final cardBg = isDark ? const Color(0xFF152238) : const Color(0xFFF8FAFC);
    final border = isDark ? const Color(0xFF1E3A5F) : const Color(0xFFE2E8F0);
    final text = isDark ? Colors.white : const Color(0xFF0F172A);
    final subtext = isDark ? const Color(0xFF90A4AE) : const Color(0xFF64748B);

    final displayName = user?.name.isNotEmpty == true ? user!.name : 'Senior Underwriter';
    final email = user?.email.isNotEmpty == true ? user!.email : 'underwriting.lead@finclude.ph';
    final phone = user?.phoneNumber?.isNotEmpty == true ? user!.phoneNumber! : '+63 917 888 2401';
    final initials = displayName
        .split(' ')
        .where((s) => s.isNotEmpty)
        .take(2)
        .map((s) => s[0].toUpperCase())
        .join();

    return Center(
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 580),
        child: Container(
          decoration: BoxDecoration(
            color: sheetBg,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: isDark ? 0.4 : 0.15),
                blurRadius: 30,
                offset: const Offset(0, -4),
              ),
            ],
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // Drag Handle
              Container(
                margin: const EdgeInsets.only(top: 10),
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: border,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),

              // Title Bar
              Padding(
                padding: const EdgeInsets.fromLTRB(22, 12, 16, 8),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(7),
                      decoration: BoxDecoration(
                        color: primary.withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Icon(Icons.shield_outlined, size: 18, color: primary),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Manager Profile',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: text,
                            ),
                          ),
                          Text(
                            'Underwriting Authority & System Credentials',
                            style: TextStyle(fontSize: 11, color: subtext),
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      icon: Icon(Icons.close_rounded, color: subtext),
                      onPressed: () => Navigator.pop(context),
                      tooltip: 'Close',
                    ),
                  ],
                ),
              ),
              Divider(color: border, height: 1),

              // Content Body
              Flexible(
                child: SingleChildScrollView(
                  padding: EdgeInsets.fromLTRB(
                    20,
                    16,
                    20,
                    MediaQuery.of(context).viewInsets.bottom + 24,
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // Profile Header Card
                      _buildHeaderCard(
                        isDark: isDark,
                        primary: primary,
                        cardBg: cardBg,
                        border: border,
                        text: text,
                        subtext: subtext,
                        displayName: displayName,
                        initials: initials.isEmpty ? 'M' : initials,
                        email: email,
                        phone: phone,
                      ),
                      const SizedBox(height: 16),

                      // Edit Profile Form (Inline when active)
                      if (_isEditing) ...[
                        _buildEditForm(
                          cardBg: cardBg,
                          border: border,
                          text: text,
                          subtext: subtext,
                          primary: primary,
                        ),
                        const SizedBox(height: 16),
                      ],

                      // Underwriting Mandate & Limits
                      _buildSectionTitle('UNDERWRITING MANDATE & AUTHORITY', subtext),
                      const SizedBox(height: 8),
                      _buildAuthorityCard(
                        cardBg: cardBg,
                        border: border,
                        text: text,
                        subtext: subtext,
                        primary: primary,
                      ),
                      const SizedBox(height: 18),

                      // Portfolio & Queue Overview
                      _buildSectionTitle('QUEUE OVERSIGHT SUMMARY', subtext),
                      const SizedBox(height: 8),
                      _buildPortfolioGrid(
                        cardBg: cardBg,
                        border: border,
                        text: text,
                        subtext: subtext,
                        primary: primary,
                      ),
                      const SizedBox(height: 18),

                      // Compliance & Regulatory Standards
                      _buildSectionTitle('COMPLIANCE & REGULATORY STANDARDS', subtext),
                      const SizedBox(height: 8),
                      _buildComplianceCard(
                        cardBg: cardBg,
                        border: border,
                        text: text,
                        subtext: subtext,
                      ),
                      const SizedBox(height: 20),

                      // Quick Settings & Actions
                      _buildActionButtons(
                        context: context,
                        auth: auth,
                        theme: theme,
                        isDark: isDark,
                        primary: primary,
                        cardBg: cardBg,
                        border: border,
                        text: text,
                        subtext: subtext,
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSectionTitle(String title, Color subtext) {
    return Text(
      title,
      style: TextStyle(
        fontSize: 11,
        fontWeight: FontWeight.w700,
        color: subtext,
        letterSpacing: 0.5,
      ),
    );
  }

  Widget _buildHeaderCard({
    required bool isDark,
    required Color primary,
    required Color cardBg,
    required Color border,
    required Color text,
    required Color subtext,
    required String displayName,
    required String initials,
    required String email,
    required String phone,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: border),
        boxShadow: [
          BoxShadow(
            color: primary.withValues(alpha: isDark ? 0.1 : 0.04),
            blurRadius: 16,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        children: [
          Row(
            children: [
              // Avatar with Online indicator
              Stack(
                children: [
                  CircleAvatar(
                    radius: 28,
                    backgroundColor: primary,
                    child: Text(
                      initials,
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: isDark ? const Color(0xFF0D1B2A) : Colors.white,
                      ),
                    ),
                  ),
                  Positioned(
                    bottom: 0,
                    right: 0,
                    child: Container(
                      width: 14,
                      height: 14,
                      decoration: BoxDecoration(
                        color: _kGreen,
                        shape: BoxShape.circle,
                        border: Border.all(color: cardBg, width: 2),
                      ),
                    ),
                  ),
                ],
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
                            displayName,
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: text,
                            ),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        const SizedBox(width: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                          decoration: BoxDecoration(
                            color: _kGreen.withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.verified_rounded, size: 11, color: _kGreen),
                              SizedBox(width: 3),
                              Text(
                                'On Duty',
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                  color: _kGreen,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Senior Underwriting Manager · Level III',
                      style: TextStyle(fontSize: 12, color: primary, fontWeight: FontWeight.w600),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Staff ID: FIN-MGR-8842 · Finclude Risk Committee',
                      style: TextStyle(fontSize: 11, color: subtext),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Divider(color: border, height: 1),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Icon(Icons.email_outlined, size: 13, color: subtext),
                        const SizedBox(width: 5),
                        Text('Work Email', style: TextStyle(fontSize: 10.5, color: subtext)),
                      ],
                    ),
                    const SizedBox(height: 2),
                    Text(
                      email,
                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: text),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Icon(Icons.phone_outlined, size: 13, color: subtext),
                        const SizedBox(width: 5),
                        Text('Direct Contact', style: TextStyle(fontSize: 10.5, color: subtext)),
                      ],
                    ),
                    const SizedBox(height: 2),
                    Text(
                      phone,
                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: text),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
              OutlinedButton.icon(
                onPressed: () => setState(() => _isEditing = !_isEditing),
                icon: Icon(
                  _isEditing ? Icons.close_rounded : Icons.edit_outlined,
                  size: 13,
                ),
                label: Text(
                  _isEditing ? 'Cancel' : 'Edit',
                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                ),
                style: OutlinedButton.styleFrom(
                  foregroundColor: primary,
                  side: BorderSide(color: primary.withValues(alpha: 0.4)),
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildEditForm({
    required Color cardBg,
    required Color border,
    required Color text,
    required Color subtext,
    required Color primary,
  }) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: primary.withValues(alpha: 0.3)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            children: [
              Icon(Icons.badge_outlined, size: 15, color: primary),
              const SizedBox(width: 6),
              Text(
                'Edit Manager Details',
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: text),
              ),
            ],
          ),
          const SizedBox(height: 10),
          TextField(
            controller: _nameController,
            style: TextStyle(fontSize: 13, color: text),
            decoration: InputDecoration(
              labelText: 'Manager Full Name',
              labelStyle: TextStyle(fontSize: 12, color: subtext),
              isDense: true,
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            ),
          ),
          const SizedBox(height: 10),
          TextField(
            controller: _phoneController,
            style: TextStyle(fontSize: 13, color: text),
            decoration: InputDecoration(
              labelText: 'Contact Phone Number',
              labelStyle: TextStyle(fontSize: 12, color: subtext),
              isDense: true,
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            ),
          ),
          const SizedBox(height: 12),
          ElevatedButton(
            onPressed: _isSaving ? null : _saveProfile,
            style: ElevatedButton.styleFrom(
              backgroundColor: primary,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(vertical: 10),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            child: _isSaving
                ? const SizedBox(
                    width: 16,
                    height: 16,
                    child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                  )
                : const Text('Save Changes', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  Widget _buildAuthorityCard({
    required Color cardBg,
    required Color border,
    required Color text,
    required Color subtext,
    required Color primary,
  }) {
    final items = [
      ('Single Borrower Approval Cap', '₱100,000.00', 'Automated ceiling for micro-enterprises'),
      ('Discretionary Variance', '±15 points', 'Permitted AI scoring override margin'),
      ('Maximum Loan Tenure Clearance', '180 Days', 'Standard working capital tenure cap'),
      ('Delegated Committee Authority', 'Level III Sign-off', 'Sole authority for Prime & Standard tiers'),
    ];

    return Container(
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: border),
      ),
      child: Column(
        children: items.asMap().entries.map((entry) {
          final idx = entry.key;
          final item = entry.value;
          return Column(
            children: [
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
                child: Row(
                  children: [
                    Container(
                      width: 6,
                      height: 6,
                      decoration: BoxDecoration(color: primary, shape: BoxShape.circle),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(item.$1, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: text)),
                          Text(item.$3, style: TextStyle(fontSize: 10.5, color: subtext)),
                        ],
                      ),
                    ),
                    Text(
                      item.$2,
                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: primary),
                    ),
                  ],
                ),
              ),
              if (idx < items.length - 1) Divider(color: border, height: 1),
            ],
          );
        }).toList(),
      ),
    );
  }

  Widget _buildPortfolioGrid({
    required Color cardBg,
    required Color border,
    required Color text,
    required Color subtext,
    required Color primary,
  }) {
    final stats = [
      ('Total Portfolio', '${widget.counts['total'] ?? 0}', Icons.folder_open_rounded, primary),
      ('Pending Review', '${widget.counts['pending'] ?? 0}', Icons.hourglass_top_rounded, _kAmber),
      ('Approved Loans', '${widget.counts['approved'] ?? 0}', Icons.check_circle_outline_rounded, _kGreen),
      ('Rejected / Flagged', '${widget.counts['rejected'] ?? 0}', Icons.cancel_outlined, _kRed),
    ];

    return Row(
      children: stats.map((s) {
        return Expanded(
          child: Container(
            margin: const EdgeInsets.symmetric(horizontal: 3),
            padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
            decoration: BoxDecoration(
              color: cardBg,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: s.$4.withValues(alpha: 0.2)),
            ),
            child: Column(
              children: [
                Icon(s.$3, size: 16, color: s.$4),
                const SizedBox(height: 6),
                Text(
                  s.$2,
                  style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: text),
                ),
                const SizedBox(height: 2),
                Text(
                  s.$1,
                  style: TextStyle(fontSize: 9.5, color: s.$4, fontWeight: FontWeight.w600),
                  textAlign: TextAlign.center,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
        );
      }).toList(),
    );
  }

  Widget _buildComplianceCard({
    required Color cardBg,
    required Color border,
    required Color text,
    required Color subtext,
  }) {
    final compliances = [
      ('BSP Circular 855', 'Credit Risk Management Standard (Compliant)', _kGreen),
      ('Data Privacy Act (RA 10173)', 'Certified Officer & Encryption Custodian', _kGreen),
      ('PhilSys National ID e-KYC', 'Biometrically Verified Officer Match', _kGreen),
      ('Two-Factor Authentication', 'Enforced Hardware Token & SMS 2FA', _kGreen),
    ];

    return Container(
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: border),
      ),
      child: Column(
        children: compliances.asMap().entries.map((entry) {
          final idx = entry.key;
          final c = entry.value;
          return Column(
            children: [
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                child: Row(
                  children: [
                    Icon(Icons.verified_user_outlined, size: 15, color: c.$3),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(c.$1, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: text)),
                          Text(c.$2, style: TextStyle(fontSize: 10.5, color: subtext)),
                        ],
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: c.$3.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        'Verified',
                        style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: c.$3),
                      ),
                    ),
                  ],
                ),
              ),
              if (idx < compliances.length - 1) Divider(color: border, height: 1),
            ],
          );
        }).toList(),
      ),
    );
  }

  Widget _buildActionButtons({
    required BuildContext context,
    required AuthProvider auth,
    required ThemeProvider theme,
    required bool isDark,
    required Color primary,
    required Color cardBg,
    required Color border,
    required Color text,
    required Color subtext,
  }) {
    return Column(
      children: [
        // Theme switch row
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
          decoration: BoxDecoration(
            color: cardBg,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: border),
          ),
          child: Row(
            children: [
              Icon(
                isDark ? Icons.light_mode_outlined : Icons.dark_mode_outlined,
                size: 16,
                color: text,
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  isDark ? 'Switch to Light Theme' : 'Switch to Dark Theme',
                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: text),
                ),
              ),
              Switch(
                value: isDark,
                onChanged: (_) => theme.toggleTheme(),
                activeThumbColor: primary,
              ),
            ],
          ),
        ),
        const SizedBox(height: 10),

        // Sign Out Button
        SizedBox(
          width: double.infinity,
          child: OutlinedButton.icon(
            onPressed: () {
              Navigator.pop(context);
              auth.logout();
              Navigator.pushAndRemoveUntil(
                context,
                MaterialPageRoute(builder: (_) => const LoginScreen()),
                (_) => false,
              );
            },
            icon: const Icon(Icons.logout_rounded, size: 16, color: _kRed),
            label: const Text(
              'Sign Out of Underwriting Portal',
              style: TextStyle(fontWeight: FontWeight.bold, color: _kRed, fontSize: 12),
            ),
            style: OutlinedButton.styleFrom(
              side: BorderSide(color: _kRed.withValues(alpha: 0.4)),
              padding: const EdgeInsets.symmetric(vertical: 12),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
          ),
        ),
      ],
    );
  }
}
