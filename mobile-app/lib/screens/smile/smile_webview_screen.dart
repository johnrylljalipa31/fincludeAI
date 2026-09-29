import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/theme_provider.dart';
import '../dashboard/dashboard_screen.dart';

class SmileWebviewScreen extends StatefulWidget {
  final String userToken;

  const SmileWebviewScreen({super.key, required this.userToken});

  @override
  State<SmileWebviewScreen> createState() => _SmileWebviewScreenState();
}

class _SmileWebviewScreenState extends State<SmileWebviewScreen> {
  bool _isLinking = false;
  late final String _targetUrl;
  String _selectedCategory = 'All';
  String _searchQuery = '';

  final List<Map<String, dynamic>> _providers = [
    // ==========================================
    // 1. TOP COMMERCIAL BANKS IN THE PHILIPPINES
    // ==========================================
    {
      'name': 'BDO Unibank',
      'category': 'Banks',
      'subtitle': 'Banco de Oro • Savings, Checking & Payroll Inflows',
      'logo': 'https://logo.clearbit.com/bdo.com.ph',
      'icon': Icons.account_balance_rounded,
      'color': const Color(0xFF003882),
    },
    {
      'name': 'BPI (Bank of the Philippine Islands)',
      'category': 'Banks',
      'subtitle': 'BPI Online, Express & Corporate Payroll Accounts',
      'logo': 'https://logo.clearbit.com/bpi.com.ph',
      'icon': Icons.account_balance_rounded,
      'color': const Color(0xFFB3141E),
    },
    {
      'name': 'Metrobank',
      'category': 'Banks',
      'subtitle': 'Metropolitan Bank & Trust Co. • Commercial & Inflow Accounts',
      'logo': 'https://logo.clearbit.com/metrobank.com.ph',
      'icon': Icons.account_balance_rounded,
      'color': const Color(0xFF002B7F),
    },
    {
      'name': 'Landbank of the Philippines',
      'category': 'Banks',
      'subtitle': 'LBP iAccess, Government Payouts & Institutional Payroll',
      'logo': 'https://logo.clearbit.com/landbank.com',
      'icon': Icons.account_balance_rounded,
      'color': const Color(0xFF006837),
    },
    {
      'name': 'UnionBank Philippines',
      'category': 'Banks',
      'subtitle': 'UnionBank Online, SME, Digital Inflows & Freelance Payouts',
      'logo': 'https://logo.clearbit.com/unionbankph.com',
      'icon': Icons.account_balance_rounded,
      'color': const Color(0xFFFF7A00),
    },
    {
      'name': 'RCBC (Rizal Commercial Banking Corp)',
      'category': 'Banks',
      'subtitle': 'RCBC Pulz, Commercial Accounts & Inflow History',
      'logo': 'https://logo.clearbit.com/rcbc.com',
      'icon': Icons.account_balance_rounded,
      'color': const Color(0xFF005BA6),
    },
    {
      'name': 'Security Bank',
      'category': 'Banks',
      'subtitle': 'Security Bank Online & BetterBanking Accounts',
      'logo': 'https://logo.clearbit.com/securitybank.com',
      'icon': Icons.account_balance_rounded,
      'color': const Color(0xFF00693E),
    },
    {
      'name': 'PNB (Philippine National Bank)',
      'category': 'Banks',
      'subtitle': 'PNB Digital, Remittance Settlement & Savings',
      'logo': 'https://logo.clearbit.com/pnb.com.ph',
      'icon': Icons.account_balance_rounded,
      'color': const Color(0xFF00205B),
    },

    // ==========================================
    // 2. TOP DIGITAL BANKS (BSP LICENSED NEOBANKS)
    // ==========================================
    {
      'name': 'Maya Bank',
      'category': 'Digital Banks',
      'subtitle': 'High-Yield Savings & Merchant Accounts • BSP Licensed',
      'logo': 'https://logo.clearbit.com/maya.ph',
      'icon': Icons.account_balance_wallet_rounded,
      'color': const Color(0xFF00D18F),
    },
    {
      'name': 'GoTyme Bank',
      'category': 'Digital Banks',
      'subtitle': 'Robinsons & Tyme Group Digital Banking • BSP Licensed',
      'logo': 'https://logo.clearbit.com/gotyme.com',
      'icon': Icons.credit_card_rounded,
      'color': const Color(0xFF00B4D8),
    },
    {
      'name': 'SeaBank Philippines',
      'category': 'Digital Banks',
      'subtitle': 'Shopee SeaMoney Banking Ecosystem • BSP Licensed',
      'logo': 'https://logo.clearbit.com/seabank.com.ph',
      'icon': Icons.shopping_bag_outlined,
      'color': const Color(0xFFFF5722),
    },
    {
      'name': 'CIMB Bank Philippines',
      'category': 'Digital Banks',
      'subtitle': 'FastPlus, GSave, Revi Credit & Personal Loan • BSP Licensed',
      'logo': 'https://logo.clearbit.com/cimb.com.ph',
      'icon': Icons.account_balance_rounded,
      'color': const Color(0xFFED1C24),
    },
    {
      'name': 'Tonik Digital Bank',
      'category': 'Digital Banks',
      'subtitle': 'First Neobank in the Philippines, Stashes & Loans • BSP Licensed',
      'logo': 'https://logo.clearbit.com/tonikbank.com',
      'icon': Icons.savings_rounded,
      'color': const Color(0xFF6C63FF),
    },

    // ==========================================
    // 3. TOP GIG ECONOMY PLATFORMS IN PH
    // ==========================================
    {
      'name': 'Grab Philippines',
      'category': 'Gig Economy',
      'subtitle': 'GrabCar, GrabFood, GrabMart & GrabExpress Partner Income',
      'logo': 'https://logo.clearbit.com/grab.com',
      'icon': Icons.local_taxi_rounded,
      'color': const Color(0xFF00B14F),
    },
    {
      'name': 'Foodpanda Philippines',
      'category': 'Gig Economy',
      'subtitle': 'Rider & Food Delivery Partner Weekly Income',
      'logo': 'https://logo.clearbit.com/foodpanda.ph',
      'icon': Icons.delivery_dining_rounded,
      'color': const Color(0xFFD70F64),
    },
    {
      'name': 'Lalamove Philippines',
      'category': 'Gig Economy',
      'subtitle': 'On-Demand Motorcycle, Sedan, MPV & Van Driver Partner',
      'logo': 'https://logo.clearbit.com/lalamove.com',
      'icon': Icons.local_shipping_rounded,
      'color': const Color(0xFFFF6600),
    },
    {
      'name': 'Shopee Express (SPX)',
      'category': 'Gig Economy',
      'subtitle': 'Courier, Hub Delivery Rider & Logistics Partner',
      'logo': 'https://logo.clearbit.com/shopee.ph',
      'icon': Icons.shopping_bag_outlined,
      'color': const Color(0xFFEE4D2D),
    },
    {
      'name': 'Lazada Logistics (LEX)',
      'category': 'Gig Economy',
      'subtitle': 'E-Commerce Delivery Courier & Hub Dispatcher',
      'logo': 'https://logo.clearbit.com/lazada.com.ph',
      'icon': Icons.storefront_rounded,
      'color': const Color(0xFF0F146D),
    },
    {
      'name': 'Angkas',
      'category': 'Gig Economy',
      'subtitle': 'Biker & On-Demand Parcel Delivery Partner',
      'logo': 'https://logo.clearbit.com/angkas.com',
      'icon': Icons.two_wheeler_rounded,
      'color': const Color(0xFF00A3E0),
    },
    {
      'name': 'JoyRide Philippines',
      'category': 'Gig Economy',
      'subtitle': 'JoyRide Superapp Driver, MC Taxi & Delivery Partner',
      'logo': 'https://logo.clearbit.com/joyride.com.ph',
      'icon': Icons.moped_rounded,
      'color': const Color(0xFF0088CE),
    },
    {
      'name': 'Move It Philippines',
      'category': 'Gig Economy',
      'subtitle': 'Motorcycle Taxi Partner Platform (Grab-Powered)',
      'logo': 'https://logo.clearbit.com/moveitph.com',
      'icon': Icons.two_wheeler_rounded,
      'color': const Color(0xFFE50914),
    },
    {
      'name': 'Upwork',
      'category': 'Gig Economy',
      'subtitle': 'Remote Digital Freelancer, Developer & VA Payouts',
      'logo': 'https://logo.clearbit.com/upwork.com',
      'icon': Icons.laptop_mac_rounded,
      'color': const Color(0xFF14A800),
    },
    {
      'name': 'TikTok Shop',
      'category': 'Gig Economy',
      'subtitle': 'E-Commerce Commission, Affiliate & Live Sales Inflows',
      'logo': 'https://logo.clearbit.com/tiktok.com',
      'icon': Icons.video_library_rounded,
      'color': const Color(0xFF010101),
    },

    // ==========================================
    // 4. TOP E-WALLETS IN THE PHILIPPINES
    // ==========================================
    {
      'name': 'GCash',
      'category': 'E-Wallets',
      'subtitle': 'Mynt • Mobile Wallet, GSave, GCredit & Cash-Flow History',
      'logo': 'https://logo.clearbit.com/gcash.com',
      'icon': Icons.payments_rounded,
      'color': const Color(0xFF007DFE),
    },
    {
      'name': 'Maya (PayMaya)',
      'category': 'E-Wallets',
      'subtitle': 'Consumer E-Wallet, Maya Credit & Verified Merchant QR',
      'logo': 'https://logo.clearbit.com/maya.ph',
      'icon': Icons.account_balance_wallet_rounded,
      'color': const Color(0xFF00D18F),
    },
    {
      'name': 'ShopeePay',
      'category': 'E-Wallets',
      'subtitle': 'Shopee Digital Wallet, SPayLater & Merchant Inflows',
      'logo': 'https://logo.clearbit.com/shopee.ph',
      'icon': Icons.shopping_cart_outlined,
      'color': const Color(0xFFEE4D2D),
    },
    {
      'name': 'GrabPay',
      'category': 'E-Wallets',
      'subtitle': 'Grab In-App Wallet & Merchant Financial Settlements',
      'logo': 'https://logo.clearbit.com/grab.com',
      'icon': Icons.wallet_rounded,
      'color': const Color(0xFF00B14F),
    },
    {
      'name': 'PalawanPay',
      'category': 'E-Wallets',
      'subtitle': 'Palawan Pawnshop Digital Wallet, Remittances & Inflows',
      'logo': 'https://logo.clearbit.com/palawanpay.com',
      'icon': Icons.price_change_rounded,
      'color': const Color(0xFF007A33),
    },

    // ==========================================
    // 5. TOP UTILITIES & STATUTORY INSTITUTIONS
    // ==========================================
    {
      'name': 'Meralco',
      'category': 'Utilities & Govt',
      'subtitle': 'Manila Electric Company • Monthly Electricity Statements',
      'logo': 'https://logo.clearbit.com/meralco.com.ph',
      'icon': Icons.bolt_rounded,
      'color': const Color(0xFFFFB300),
    },
    {
      'name': 'Maynilad / Manila Water',
      'category': 'Utilities & Govt',
      'subtitle': 'Metro Manila Water Supply & Billing Statements',
      'logo': 'https://logo.clearbit.com/maynilad.com.ph',
      'icon': Icons.water_drop_rounded,
      'color': const Color(0xFF0072CE),
    },
    {
      'name': 'PLDT / Globe Telecom',
      'category': 'Utilities & Govt',
      'subtitle': 'Broadband, Mobile & Telecommunication Recurring Statements',
      'logo': 'https://logo.clearbit.com/pldt.com.ph',
      'icon': Icons.wifi_rounded,
      'color': const Color(0xFFD32F2F),
    },
    {
      'name': 'SSS (Social Security System)',
      'category': 'Utilities & Govt',
      'subtitle': 'Self-Employed Contributions & Salary Loan History',
      'logo': 'https://logo.clearbit.com/sss.gov.ph',
      'icon': Icons.verified_user_rounded,
      'color': const Color(0xFF003882),
    },
  ];

  /// Builds a provider logo widget: tries network image, falls back to colored icon.
  Widget _buildProviderLogo(Map<String, dynamic> p, {double size = 44, double iconSize = 24}) {
    final logoUrl = p['logo'] as String?;
    final color = p['color'] as Color;
    final icon = p['icon'] as IconData;
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(size * 0.27),
      ),
      child: logoUrl != null
          ? ClipRRect(
              borderRadius: BorderRadius.circular(size * 0.27),
              child: Image.network(
                logoUrl,
                width: size,
                height: size,
                fit: BoxFit.contain,
                errorBuilder: (context, error, stackTrace) => Icon(icon, color: color, size: iconSize),
                loadingBuilder: (_, child, progress) {
                  if (progress == null) return child;
                  return Icon(icon, color: color.withValues(alpha: 0.4), size: iconSize);
                },
              ),
            )
          : Icon(icon, color: color, size: iconSize),
    );
  }

  @override
  void initState() {
    super.initState();
    _targetUrl = 'https://wink.smileapi.io?token=${widget.userToken}';
  }

  void _handleLinkingSuccess([String provider = 'Grab']) async {
    if (_isLinking) return;
    setState(() => _isLinking = true);
    final auth = Provider.of<AuthProvider>(context, listen: false);

    // Trigger webhook sync to score the linked account's transactions
    await auth.triggerWebhookSync(provider: provider);
    await auth.fetchDashboard();

    if (!mounted) return;
    setState(() => _isLinking = false);

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        backgroundColor: const Color(0xFF00E5FF),
        content: Text(
          '$provider account linked successfully! Credit score updated.',
          style: const TextStyle(color: Color(0xFF0D1B2A), fontWeight: FontWeight.bold),
        ),
      ),
    );

    Navigator.of(context).pushReplacement(
      MaterialPageRoute(builder: (_) => const DashboardScreen()),
    );
  }

  void _showLinkProviderModal(Map<String, dynamic> provider) {
    final theme = Provider.of<ThemeProvider>(context, listen: false);
    final isDark = theme.isDarkMode;

    final accountCtrl = TextEditingController();
    final passwordCtrl = TextEditingController();
    bool obscure = true;
    bool isConnecting = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: isDark ? const Color(0xFF1B263B) : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) {
          final primaryColor = Theme.of(context).primaryColor;
          final textColor = isDark ? Colors.white : const Color(0xFF0F172A);
          final subColor = isDark ? const Color(0xFF90E0EF) : const Color(0xFF64748B);
          final fieldFill = isDark ? const Color(0xFF0D1B2A) : const Color(0xFFF8FAFC);
          final fieldBorder = isDark ? const Color(0xFF415A77) : const Color(0xFFCBD5E1);

          return Padding(
            padding: EdgeInsets.only(
              left: 24,
              right: 24,
              top: 24,
              bottom: MediaQuery.of(ctx).viewInsets.bottom + 24,
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Row(
                  children: [
                    _buildProviderLogo(provider, size: 52, iconSize: 28),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            provider['name'] as String,
                            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: textColor),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            'Smile Open Banking Secure Link',
                            style: TextStyle(fontSize: 12, color: subColor),
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      icon: Icon(Icons.close, color: subColor),
                      onPressed: () => Navigator.of(ctx).pop(),
                    ),
                  ],
                ),
                const SizedBox(height: 20),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFF00E5FF).withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFF00E5FF).withValues(alpha: 0.3)),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.lock_outline, color: Color(0xFF00E5FF), size: 18),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          'Smile API uses end-to-end 256-bit encryption. Your credentials are authenticated directly with the provider.',
                          style: TextStyle(
                            fontSize: 11,
                            color: isDark ? Colors.white70 : const Color(0xFF1E293B),
                            height: 1.3,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 18),
                Text(
                  'Account Identifier / Mobile Number',
                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: textColor),
                ),
                const SizedBox(height: 6),
                TextField(
                  controller: accountCtrl,
                  style: TextStyle(color: textColor, fontSize: 14),
                  decoration: InputDecoration(
                    hintText: 'e.g. +63 917 123 4567 or username',
                    hintStyle: TextStyle(color: subColor.withValues(alpha: 0.7), fontSize: 13),
                    prefixIcon: Icon(Icons.person_outline, color: subColor, size: 18),
                    filled: true,
                    fillColor: fieldFill,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: BorderSide(color: fieldBorder),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: BorderSide(color: primaryColor, width: 1.5),
                    ),
                  ),
                ),
                const SizedBox(height: 14),
                Text(
                  'Password / PIN',
                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: textColor),
                ),
                const SizedBox(height: 6),
                TextField(
                  controller: passwordCtrl,
                  obscureText: obscure,
                  style: TextStyle(color: textColor, fontSize: 14),
                  decoration: InputDecoration(
                    hintText: 'Enter your password or PIN',
                    hintStyle: TextStyle(color: subColor.withValues(alpha: 0.7), fontSize: 13),
                    prefixIcon: Icon(Icons.lock_outline, color: subColor, size: 18),
                    suffixIcon: IconButton(
                      icon: Icon(obscure ? Icons.visibility_off : Icons.visibility, color: subColor, size: 18),
                      onPressed: () => setModalState(() => obscure = !obscure),
                    ),
                    filled: true,
                    fillColor: fieldFill,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: BorderSide(color: fieldBorder),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: BorderSide(color: primaryColor, width: 1.5),
                    ),
                  ),
                ),
                const SizedBox(height: 24),
                ElevatedButton(
                  onPressed: isConnecting
                      ? null
                      : () async {
                          setModalState(() => isConnecting = true);
                          await Future.delayed(const Duration(milliseconds: 600));
                          if (!mounted || !ctx.mounted) return;
                          Navigator.of(ctx).pop();
                          _handleLinkingSuccess(provider['name'] as String);
                        },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF00E5FF),
                    foregroundColor: const Color(0xFF0D1B2A),
                    padding: const EdgeInsets.symmetric(vertical: 15),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  child: isConnecting
                      ? const SizedBox(
                          height: 18,
                          width: 18,
                          child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF0D1B2A)),
                        )
                      : Text(
                          'Authorize & Link ${provider['name']}',
                          style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                        ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = context.watch<ThemeProvider>();
    final isDark = theme.isDarkMode;

    final primaryColor = Theme.of(context).primaryColor;
    final textColor = isDark ? Colors.white : const Color(0xFF0F172A);
    final subtextColor = isDark ? const Color(0xFF90E0EF) : const Color(0xFF64748B);
    final cardBg = isDark ? const Color(0xFF1B263B) : Colors.white;
    final borderColor = isDark ? const Color(0xFF283A5A) : const Color(0xFFE2E8F0);

    final filteredProviders = _providers.where((p) {
      final matchesCategory = _selectedCategory == 'All' ||
          p['category'] == _selectedCategory ||
          (_selectedCategory == 'Banks' && p['category'] == 'Digital Banks');
      final matchesSearch = _searchQuery.isEmpty ||
          (p['name'] as String).toLowerCase().contains(_searchQuery.toLowerCase()) ||
          (p['subtitle'] as String).toLowerCase().contains(_searchQuery.toLowerCase());
      return matchesCategory && matchesSearch;
    }).toList();

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0D1B2A) : const Color(0xFFF8FAFC),
      appBar: AppBar(
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => Navigator.of(context).pop(),
        ),
        title: const Text('Smile Open Banking Link'),
        backgroundColor: isDark ? const Color(0xFF0D1B2A) : Colors.white,
        foregroundColor: isDark ? Colors.white : const Color(0xFF0F172A),
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            tooltip: 'Sync Account',
            onPressed: _isLinking ? null : () => _handleLinkingSuccess(),
          ),
          IconButton(
            icon: const Icon(Icons.close),
            tooltip: 'Return to Dashboard',
            onPressed: () => Navigator.of(context).pushReplacement(
              MaterialPageRoute(builder: (_) => const DashboardScreen()),
            ),
          ),
        ],
      ),
      body: SafeArea(
        child: Column(
          children: [
            if (_isLinking)
              const LinearProgressIndicator(
                color: Color(0xFF00E5FF),
                backgroundColor: Color(0xFF1B263B),
              ),

            // Top URL and SSL status bar
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              color: isDark ? const Color(0xFF1B263B) : const Color(0xFFE2E8F0),
              child: Row(
                children: [
                  const Icon(Icons.lock_outline, color: Color(0xFF00B4D8), size: 16),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      _targetUrl,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        color: isDark ? const Color(0xFF90E0EF) : const Color(0xFF0F172A),
                        fontSize: 11,
                      ),
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(
                      color: const Color(0xFF00B4D8).withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(color: const Color(0xFF00B4D8).withValues(alpha: 0.4)),
                    ),
                    child: const Text(
                      'SANDBOX',
                      style: TextStyle(color: Color(0xFF00B4D8), fontSize: 10, fontWeight: FontWeight.bold),
                    ),
                  ),
                ],
              ),
            ),

            // Provider Directory & Interactive Aggregator
            Expanded(
              child: ListView(
                padding: const EdgeInsets.all(16.0),
                children: [
                  Text(
                    'Link Financial & Gig Accounts',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: textColor),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Select your gig platform, e-wallet, or bank to securely verify your income stream.',
                    style: TextStyle(fontSize: 12, color: subtextColor, height: 1.4),
                  ),
                  const SizedBox(height: 16),

                  // Search Bar
                  TextField(
                    onChanged: (val) => setState(() => _searchQuery = val),
                    style: TextStyle(color: textColor, fontSize: 14),
                    decoration: InputDecoration(
                      hintText: 'Search provider (e.g. Grab, Foodpanda, Maya)...',
                      hintStyle: TextStyle(color: subtextColor.withValues(alpha: 0.7), fontSize: 13),
                      prefixIcon: Icon(Icons.search, color: subtextColor, size: 20),
                      filled: true,
                      fillColor: cardBg,
                      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: BorderSide(color: borderColor),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: BorderSide(color: primaryColor, width: 1.5),
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),

                  // Category Filter Chips (Wrap layout so all chips are visible at once)
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: [
                      'All',
                      'Banks',
                      'Digital Banks',
                      'Gig Economy',
                      'E-Wallets',
                      'Utilities & Govt',
                    ].map((cat) {
                      final isSelected = _selectedCategory == cat;
                      final count = cat == 'All'
                          ? _providers.length
                          : cat == 'Banks'
                              ? _providers.where((p) => p['category'] == 'Banks' || p['category'] == 'Digital Banks').length
                              : _providers.where((p) => p['category'] == cat).length;
                      return FilterChip(
                        label: Text('$cat ($count)'),
                        selected: isSelected,
                        onSelected: (_) => setState(() => _selectedCategory = cat),
                        selectedColor: primaryColor.withValues(alpha: 0.2),
                        backgroundColor: cardBg,
                        labelStyle: TextStyle(
                          fontSize: 12,
                          fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                          color: isSelected ? primaryColor : subtextColor,
                        ),
                        side: BorderSide(color: isSelected ? primaryColor : borderColor),
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      );
                    }).toList(),
                  ),
                  const SizedBox(height: 16),

                  // Results count indicator
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Available Providers (${filteredProviders.length})',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                          color: subtextColor,
                        ),
                      ),
                      if (_selectedCategory != 'All' || _searchQuery.isNotEmpty)
                        GestureDetector(
                          onTap: () => setState(() {
                            _selectedCategory = 'All';
                            _searchQuery = '';
                          }),
                          child: Text(
                            'Reset filters',
                            style: TextStyle(
                              fontSize: 11,
                              color: primaryColor,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 12),

                  if (filteredProviders.isEmpty)
                    Container(
                      padding: const EdgeInsets.symmetric(vertical: 36, horizontal: 20),
                      alignment: Alignment.center,
                      child: Column(
                        children: [
                          Icon(Icons.search_off_rounded, size: 48, color: subtextColor.withValues(alpha: 0.5)),
                          const SizedBox(height: 12),
                          Text(
                            'No matching accounts found',
                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: textColor),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            'Try searching for another bank, gig platform, or e-wallet name.',
                            textAlign: TextAlign.center,
                            style: TextStyle(fontSize: 12, color: subtextColor),
                          ),
                        ],
                      ),
                    ),

                  // Provider Cards
                  ...filteredProviders.map((p) {
                    return Container(
                      margin: const EdgeInsets.only(bottom: 10),
                      decoration: BoxDecoration(
                        color: cardBg,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: borderColor),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: isDark ? 0.2 : 0.03),
                            blurRadius: 6,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: ListTile(
                        onTap: () => _showLinkProviderModal(p),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                        leading: _buildProviderLogo(p, size: 44, iconSize: 24),
                        title: Text(
                          p['name'] as String,
                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: textColor),
                        ),
                        subtitle: Text(
                          p['subtitle'] as String,
                          style: TextStyle(fontSize: 11, color: subtextColor),
                        ),
                        trailing: Icon(Icons.chevron_right, color: subtextColor, size: 20),
                      ),
                    );
                  }),
                  const SizedBox(height: 70),
                ],
              ),
            ),

            // Clean bottom security indicator
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
              decoration: BoxDecoration(
                color: cardBg,
                border: Border(top: BorderSide(color: borderColor)),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.04),
                    blurRadius: 6,
                    offset: const Offset(0, -2),
                  ),
                ],
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.shield_outlined, color: Color(0xFF10B981), size: 18),
                  const SizedBox(width: 8),
                  Text(
                    'End-to-End 256-bit Encrypted • RA 10173 Compliant',
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: subtextColor),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
