import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import 'package:image_picker/image_picker.dart';
import '../../providers/auth_provider.dart';
import '../../providers/theme_provider.dart';
import '../compliance/privacy_policy_screen.dart';
import '../auth/login_screen.dart';

const _kGreen = Color(0xFF10B981);
const _kBlue = Color(0xFF3B82F6);
const _kAmber = Color(0xFFF59E0B);
const _kRed = Color(0xFFEF4444);

class WorkerProfileSheet extends StatefulWidget {
  const WorkerProfileSheet({super.key});

  static void show(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => const WorkerProfileSheet(),
    );
  }

  @override
  State<WorkerProfileSheet> createState() => _WorkerProfileSheetState();
}

class _WorkerProfileSheetState extends State<WorkerProfileSheet> {
  bool _isEditing = false;
  late TextEditingController _nameController;
  late TextEditingController _phoneController;
  bool _isSaving = false;
  bool _uploadingPicture = false;
  final currencyFormat = NumberFormat.currency(locale: 'en_PH', symbol: '₱');

  @override
  void initState() {
    super.initState();
    final user = context.read<AuthProvider>().user;
    _nameController = TextEditingController(text: user?.name ?? '');
    _phoneController = TextEditingController(text: user?.phoneNumber ?? '+63 917 123 4567');
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
          success ? 'Profile updated successfully!' : 'Failed to update profile.',
          style: const TextStyle(fontWeight: FontWeight.w600),
        ),
        backgroundColor: success ? _kGreen : _kRed,
      ),
    );
  }

  Future<void> _pickAndUploadPicture() async {
    // Show source picker
    final source = await showModalBottomSheet<ImageSource>(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        final isDark = context.read<ThemeProvider>().isDarkMode;
        final bg = isDark ? const Color(0xFF0F1F35) : Colors.white;
        final textColor = isDark ? Colors.white : const Color(0xFF0F172A);
        return Container(
          decoration: BoxDecoration(
            color: bg,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
          ),
          child: SafeArea(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const SizedBox(height: 8),
                Container(
                  width: 36, height: 4,
                  decoration: BoxDecoration(color: Colors.grey.shade400, borderRadius: BorderRadius.circular(2)),
                ),
                const SizedBox(height: 14),
                ListTile(
                  leading: Icon(Icons.photo_library_outlined, color: Theme.of(ctx).primaryColor),
                  title: Text('Choose from Gallery', style: TextStyle(color: textColor, fontWeight: FontWeight.w600)),
                  onTap: () => Navigator.pop(ctx, ImageSource.gallery),
                ),
                ListTile(
                  leading: Icon(Icons.camera_alt_outlined, color: Theme.of(ctx).primaryColor),
                  title: Text('Take a Photo', style: TextStyle(color: textColor, fontWeight: FontWeight.w600)),
                  onTap: () => Navigator.pop(ctx, ImageSource.camera),
                ),
                const SizedBox(height: 10),
              ],
            ),
          ),
        );
      },
    );

    if (source == null || !mounted) return;

    final picker = ImagePicker();
    final picked = await picker.pickImage(source: source, imageQuality: 85, maxWidth: 800, maxHeight: 800);
    if (picked == null || !mounted) return;

    setState(() => _uploadingPicture = true);

    final bytes = await picked.readAsBytes();
    final ext = picked.name.split('.').last.toLowerCase();
    final mimeMap = {'jpg': 'image/jpeg', 'jpeg': 'image/jpeg', 'png': 'image/png', 'gif': 'image/gif', 'webp': 'image/webp'};
    final mime = mimeMap[ext] ?? 'image/jpeg';

    final auth = context.read<AuthProvider>();
    final success = await auth.uploadProfilePicture(
      imageBytes: bytes,
      filename: picked.name,
      mimeType: mime,
    );

    if (!mounted) return;
    setState(() => _uploadingPicture = false);

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          success ? 'Profile picture updated!' : 'Failed to upload picture.',
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
    final score = auth.creditScore;

    final sheetBg = isDark ? const Color(0xFF0F1F35) : Colors.white;
    final cardBg = isDark ? const Color(0xFF152238) : const Color(0xFFF8FAFC);
    final border = isDark ? const Color(0xFF1E3A5F) : const Color(0xFFE2E8F0);
    final text = isDark ? Colors.white : const Color(0xFF0F172A);
    final subtext = isDark ? const Color(0xFF90A4AE) : const Color(0xFF64748B);

    final displayName = user?.name.isNotEmpty == true ? user!.name : 'Juan Dela Cruz';
    final email = user?.email.isNotEmpty == true ? user!.email : 'user@finclude.ph';
    final phone = user?.phoneNumber?.isNotEmpty == true ? user!.phoneNumber! : '+63 917 123 4567';
    final idType = user?.idType?.isNotEmpty == true ? user!.idType! : 'Philippine National ID (PhilSys)';
    final idNumber = user?.idNumber?.isNotEmpty == true ? user!.idNumber! : '9821-4412-8802';

    final initials = displayName
        .split(' ')
        .where((s) => s.isNotEmpty)
        .take(2)
        .map((s) => s[0].toUpperCase())
        .join();

    final currentScore = score?.score ?? 78;
    final Color scoreColor = currentScore >= 80 ? _kGreen : (currentScore >= 60 ? _kAmber : _kRed);
    final String scoreTier = score?.riskTier.isNotEmpty == true ? score!.riskTier : (currentScore >= 80 ? 'Prime' : 'Standard');

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
                      child: Icon(Icons.person_pin_rounded, size: 18, color: primary),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Worker Partner Profile',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: text,
                            ),
                          ),
                          Text(
                            'Gig Worker Identity & Verified Credit Profile',
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
                        initials: initials.isEmpty ? 'G' : initials,
                        email: email,
                        phone: phone,
                        idType: idType,
                        idNumber: idNumber,
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

                      // Alternative Credit & Borrowing Standing
                      _buildSectionTitle('FINCLUDE CREDIT STANDING & CEILING', subtext),
                      const SizedBox(height: 8),
                      _buildCreditCard(
                        auth: auth,
                        cardBg: cardBg,
                        border: border,
                        text: text,
                        subtext: subtext,
                        scoreColor: scoreColor,
                        scoreTier: scoreTier,
                        currentScore: currentScore,
                      ),
                      const SizedBox(height: 18),

                      // Connected Gig Economy Data Sources
                      _buildSectionTitle('CONNECTED GIG WORK PLATFORMS', subtext),
                      const SizedBox(height: 8),
                      _buildGigPlatformsCard(
                        cardBg: cardBg,
                        border: border,
                        text: text,
                        subtext: subtext,
                        primary: primary,
                      ),
                      const SizedBox(height: 18),

                      // Data Privacy & RA 10173 Consent
                      _buildSectionTitle('DATA PRIVACY & LEGAL PROTECTIONS', subtext),
                      const SizedBox(height: 8),
                      _buildPrivacyCard(
                        context: context,
                        cardBg: cardBg,
                        border: border,
                        text: text,
                        subtext: subtext,
                        primary: primary,
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
    required String idType,
    required String idNumber,
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
              // Avatar
              Stack(
                children: [
                  GestureDetector(
                    onTap: _uploadingPicture ? null : _pickAndUploadPicture,
                    child: Stack(
                      children: [
                        CircleAvatar(
                          radius: 28,
                          backgroundColor: primary,
                          backgroundImage: (user?.profilePictureUrl != null && user!.profilePictureUrl!.isNotEmpty)
                              ? NetworkImage(user.profilePictureUrl!)
                              : null,
                          child: (user?.profilePictureUrl == null || user!.profilePictureUrl!.isEmpty)
                              ? Text(
                                  initials,
                                  style: TextStyle(
                                    fontSize: 20,
                                    fontWeight: FontWeight.bold,
                                    color: isDark ? const Color(0xFF0D1B2A) : Colors.white,
                                  ),
                                )
                              : null,
                        ),
                        if (_uploadingPicture)
                          Positioned.fill(
                            child: DecoratedBox(
                              decoration: BoxDecoration(
                                color: Colors.black38,
                                shape: BoxShape.circle,
                              ),
                              child: const Center(
                                child: SizedBox(
                                  width: 18,
                                  height: 18,
                                  child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                                ),
                              ),
                            ),
                          ),
                      ],
                    ),
                  ),
                  // Camera badge
                  Positioned(
                    bottom: 0,
                    right: 0,
                    child: GestureDetector(
                      onTap: _uploadingPicture ? null : _pickAndUploadPicture,
                      child: Container(
                        width: 18,
                        height: 18,
                        decoration: BoxDecoration(
                          color: primary,
                          shape: BoxShape.circle,
                          border: Border.all(color: cardBg, width: 2),
                        ),
                        child: const Icon(Icons.camera_alt_rounded, size: 10, color: Colors.white),
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
                              Icon(Icons.check_circle_rounded, size: 11, color: _kGreen),
                              SizedBox(width: 3),
                              Text(
                                'Verified',
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
                      'Gig Economy Partner · Active Borrower',
                      style: TextStyle(fontSize: 12, color: primary, fontWeight: FontWeight.w600),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      '$idType: $idNumber',
                      style: TextStyle(fontSize: 11, color: subtext),
                      overflow: TextOverflow.ellipsis,
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
                        Text('Account Email', style: TextStyle(fontSize: 10.5, color: subtext)),
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
                        Text('Mobile Phone', style: TextStyle(fontSize: 10.5, color: subtext)),
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
                'Edit Worker Partner Details',
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: text),
              ),
            ],
          ),
          const SizedBox(height: 10),
          TextField(
            controller: _nameController,
            style: TextStyle(fontSize: 13, color: text),
            decoration: InputDecoration(
              labelText: 'Full Legal Name',
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
              labelText: 'Mobile Phone Number',
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

  Widget _buildCreditCard({
    required AuthProvider auth,
    required Color cardBg,
    required Color border,
    required Color text,
    required Color subtext,
    required Color scoreColor,
    required String scoreTier,
    required int currentScore,
  }) {
    final ceiling = auth.creditScore?.approvedCeiling ?? 25000.0;
    final wallet = auth.walletBalance;
    final hasActiveLoan = auth.activeLoan != null;

    final items = [
      ('Finclude AI Score', '$currentScore / 100 ($scoreTier)', scoreColor, Icons.speed_rounded),
      ('Approved Loan Ceiling', currencyFormat.format(ceiling), _kGreen, Icons.account_balance_wallet_outlined),
      ('Current Wallet Balance', currencyFormat.format(wallet), _kBlue, Icons.payments_outlined),
      ('Active Loan Status', hasActiveLoan ? '${auth.activeLoan!.purpose} (${currencyFormat.format(auth.activeLoan!.amount)})' : 'No Active Debt (Available for Micro-Loan)', hasActiveLoan ? _kAmber : _kGreen, Icons.receipt_long_outlined),
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
                    Icon(item.$4, size: 16, color: item.$3),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        item.$1,
                        style: TextStyle(fontSize: 12, color: subtext, fontWeight: FontWeight.w500),
                      ),
                    ),
                    Text(
                      item.$2,
                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: text),
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

  Widget _buildGigPlatformsCard({
    required Color cardBg,
    required Color border,
    required Color text,
    required Color subtext,
    required Color primary,
  }) {
    final platforms = [
      ('Grab & JoyRide Logistics', 'Verified Transport Driver Shifts', 'Daily Sync', _kGreen),
      ('Maya & GCash Financial', 'Verified Merchant & QR Settlements', 'Real-time', _kGreen),
      ('Shopee / TikTok Shop Merchant', 'Verified E-Commerce Merchant Cashflow', 'Connected', _kGreen),
    ];

    return Container(
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: border),
      ),
      child: Column(
        children: platforms.asMap().entries.map((entry) {
          final idx = entry.key;
          final p = entry.value;
          return Column(
            children: [
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(6),
                      decoration: BoxDecoration(
                        color: p.$4.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Icon(Icons.hub_outlined, size: 14, color: p.$4),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(p.$1, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: text)),
                          Text(p.$2, style: TextStyle(fontSize: 10.5, color: subtext)),
                        ],
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                      decoration: BoxDecoration(
                        color: p.$4.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        p.$3,
                        style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: p.$4),
                      ),
                    ),
                  ],
                ),
              ),
              if (idx < platforms.length - 1) Divider(color: border, height: 1),
            ],
          );
        }).toList(),
      ),
    );
  }

  Widget _buildPrivacyCard({
    required BuildContext context,
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
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: border),
      ),
      child: Column(
        children: [
          Row(
            children: [
              const Icon(Icons.shield_rounded, size: 16, color: _kGreen),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  'Republic Act 10173 (Data Privacy Act of 2012)',
                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: text),
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Text(
            'Your connected alternative financial data is fully encrypted with AES-256 and used solely for credit evaluation under strict consent.',
            style: TextStyle(fontSize: 11, color: subtext, height: 1.4),
          ),
          const SizedBox(height: 10),
          InkWell(
            onTap: () {
              Navigator.pop(context);
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const PrivacyPolicyScreen()),
              );
            },
            borderRadius: BorderRadius.circular(8),
            child: Padding(
              padding: const EdgeInsets.symmetric(vertical: 4),
              child: Row(
                children: [
                  Icon(Icons.open_in_new_rounded, size: 13, color: primary),
                  const SizedBox(width: 6),
                  Text(
                    'Review Data Privacy Policy & Consent Terms',
                    style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: primary),
                  ),
                ],
              ),
            ),
          ),
        ],
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
              'Sign Out of Finclude Partner Account',
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
