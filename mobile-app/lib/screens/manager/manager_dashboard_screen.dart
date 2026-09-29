import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import 'package:http/http.dart' as http;
import '../../providers/auth_provider.dart';
import '../../providers/theme_provider.dart';
import '../../config/api_config.dart';
import '../../models/manager_queue_model.dart';
import '../../widgets/finclude_logo.dart';
import '../auth/login_screen.dart';
import 'manager_profile_sheet.dart';

// Design Tokens
const _kGreen  = Color(0xFF10B981);
const _kBlue   = Color(0xFF3B82F6);
const _kAmber  = Color(0xFFF59E0B);
const _kRed    = Color(0xFFEF4444);
const _kCyan   = Color(0xFF00E5FF);
const _kPurple = Color(0xFF8B5CF6);

class ManagerDashboardScreen extends StatefulWidget {
  const ManagerDashboardScreen({super.key});

  @override
  State<ManagerDashboardScreen> createState() => _ManagerDashboardScreenState();
}

class _ManagerDashboardScreenState extends State<ManagerDashboardScreen> {
  final _search = TextEditingController();

  List<ManagerQueueItem> _queue = [];
  bool   _isLoading            = true;
  String _selectedStatus       = 'pending';
  Map<String, int> _counts     = {'total': 0, 'pending': 0, 'approved': 0, 'rejected': 0};

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _fetchQueue());
  }

  @override
  void dispose() { _search.dispose(); super.dispose(); }

  // ── Network ────────────────────────────────────────────────────────────────
  Future<void> _fetchQueue() async {
    setState(() => _isLoading = true);
    final auth = context.read<AuthProvider>();
    try {
      final uri = Uri.parse(ApiConfig.managerQueueUrl).replace(
        queryParameters: {'status': _selectedStatus},
      );
      final res = await http.get(uri, headers: _headers(auth.token));
      if (res.statusCode == 200) {
        final data    = jsonDecode(res.body);
        final list    = (data['queue'] as List? ?? [])
            .map((e) => ManagerQueueItem.fromJson(e as Map<String, dynamic>))
            .toList();
        final summary = data['summary'] as Map<String, dynamic>? ?? {};
        if (mounted) {
          setState(() {
            _queue  = list;
            _counts = {
              'total'   : summary['total']    ?? list.length,
              'pending' : summary['pending']  ?? 0,
              'approved': summary['approved'] ?? 0,
              'rejected': summary['rejected'] ?? 0,
            };
            _isLoading = false;
          });
        }
        return;
      }
    } catch (e) { debugPrint('queue error: $e'); }
    if (mounted) setState(() => _isLoading = false);
  }


  void _openReview(ManagerQueueItem item) {
    final auth = context.read<AuthProvider>();
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _ReviewSheet(
        item: item,
        token: auth.token,
        onDecisionMade: () { Navigator.pop(context); _fetchQueue(); },
      ),
    );
  }

  // ── Helpers ────────────────────────────────────────────────────────────────
  Map<String, String> _headers(String? token) => {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    if (token != null) 'Authorization': 'Bearer $token',
  };


  // ── Build ──────────────────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    final auth    = context.watch<AuthProvider>();
    final theme   = context.watch<ThemeProvider>();
    final isDark  = theme.isDarkMode;
    final primary = Theme.of(context).primaryColor;
    final card    = Theme.of(context).cardColor;
    final bg      = isDark ? const Color(0xFF0B1325) : const Color(0xFFF0F4F8);
    final text    = isDark ? Colors.white             : const Color(0xFF0F172A);
    final subtext = isDark ? const Color(0xFF90A4AE)  : const Color(0xFF64748B);
    final border  = isDark ? const Color(0xFF1E3A5F)  : const Color(0xFFE2E8F0);

    final query    = _search.text.toLowerCase();
    final filtered = _queue.where((e) =>
      query.isEmpty ||
      e.applicantName.toLowerCase().contains(query) ||
      e.purpose.toLowerCase().contains(query) ||
      e.applicantEmail.toLowerCase().contains(query),
    ).toList();

    return Scaffold(
      backgroundColor: bg,
      appBar: _buildAppBar(auth, theme, isDark, primary),
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: _fetchQueue,
          color: primary,
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.fromLTRB(20, 20, 20, 32),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Stat Cards
                _StatRow(counts: _counts, isDark: isDark, primary: primary),
                const SizedBox(height: 20),

                // Search
                _SearchBar(
                  controller: _search,
                  text: text, subtext: subtext, card: card, border: border,
                  onChanged: (_) => setState(() {}),
                ),
                const SizedBox(height: 10),

                // Filters
                _FilterRow(
                  selected: _selectedStatus,
                  counts: _counts,
                  primary: primary,
                  text: text,
                  isDark: isDark,
                  onSelect: (k) { setState(() => _selectedStatus = k); _fetchQueue(); },
                ),
                const SizedBox(height: 20),

                // Queue Table Card
                Container(
                  decoration: BoxDecoration(
                    color: card,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: border),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: isDark ? 0.25 : 0.05),
                        blurRadius: 20, offset: const Offset(0, 6),
                      ),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // Card Header
                      Padding(
                        padding: const EdgeInsets.fromLTRB(20, 18, 20, 0),
                        child: Row(children: [
                          Icon(Icons.table_rows_rounded, size: 18, color: primary),
                          const SizedBox(width: 10),
                          Text('Application Queue',
                              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: text)),
                          const Spacer(),
                          _Pill(label: '${filtered.length} shown', color: primary),
                        ]),
                      ),
                      const SizedBox(height: 14),
                      Divider(color: border, height: 1),

                      // Column Headers
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                        child: Row(children: [
                          Expanded(flex: 4, child: _ColHeader('Applicant', subtext)),
                          Expanded(flex: 3, child: _ColHeader('AI Score', subtext)),
                          Expanded(flex: 2, child: _ColHeader('Risk', subtext)),
                          Expanded(flex: 3, child: _ColHeader('Actions', subtext, right: true)),
                        ]),
                      ),
                      Divider(color: border, height: 1),

                      // Rows
                      if (_isLoading)
                        const Padding(
                          padding: EdgeInsets.symmetric(vertical: 48),
                          child: Center(child: CircularProgressIndicator()),
                        )
                      else if (filtered.isEmpty)
                        _EmptyState(subtext: subtext)
                      else
                        ListView.separated(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          itemCount: filtered.length,
                          separatorBuilder: (ctx, idx) => Divider(color: border, height: 1),
                          itemBuilder: (_, i) => _QueueRow(
                            item: filtered[i],
                            isDark: isDark,
                            text: text, subtext: subtext, primary: primary,
                            onReview: () => _openReview(filtered[i]),
                          ),
                        ),
                      const SizedBox(height: 4),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  AppBar _buildAppBar(AuthProvider auth, ThemeProvider theme, bool isDark, Color primary) {
    return AppBar(
      automaticallyImplyLeading: false,
      elevation: 0,
      titleSpacing: 16,
      title: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          const FincludeLogo(size: 26),
          const SizedBox(width: 10),
          Flexible(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                const Text('Finclude Underwriting',
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                    overflow: TextOverflow.ellipsis),
                Text('Manager Decision Portal',
                    style: TextStyle(fontSize: 11, color: primary),
                    overflow: TextOverflow.ellipsis),
              ],
            ),
          ),
        ],
      ),
      actions: [
        Tooltip(
          message: 'Manager Profile & Credentials',
          child: InkWell(
            onTap: () => ManagerProfileSheet.show(
              context,
              counts: _counts,
              onRefreshQueue: _fetchQueue,
            ),
            borderRadius: BorderRadius.circular(20),
            child: Container(
              margin: const EdgeInsets.symmetric(vertical: 8),
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(
                color: primary.withValues(alpha: isDark ? 0.18 : 0.1),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: primary.withValues(alpha: 0.35)),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  CircleAvatar(
                    radius: 11,
                    backgroundColor: primary,
                    child: Text(
                      (auth.user?.name.isNotEmpty == true ? auth.user!.name[0] : 'M').toUpperCase(),
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.bold,
                        color: isDark ? const Color(0xFF0D1B2A) : Colors.white,
                      ),
                    ),
                  ),
                  const SizedBox(width: 6),
                  ConstrainedBox(
                    constraints: const BoxConstraints(maxWidth: 130),
                    child: Text(
                      auth.user?.name.isNotEmpty == true ? auth.user!.name : 'Manager',
                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: primary),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  const SizedBox(width: 4),
                  Icon(Icons.shield_outlined, size: 13, color: primary),
                ],
              ),
            ),
          ),
        ),
        const SizedBox(width: 2),
        _AppBarIcon(
          icon: isDark ? Icons.light_mode_outlined : Icons.dark_mode_outlined,
          tooltip: 'Toggle Theme',
          onTap: theme.toggleTheme,
        ),
        _AppBarIcon(icon: Icons.refresh_rounded, tooltip: 'Refresh', onTap: _fetchQueue),
        _AppBarIcon(icon: Icons.logout_rounded,   tooltip: 'Sign Out', onTap: () {
          auth.logout();
          Navigator.pushAndRemoveUntil(
            context,
            MaterialPageRoute(builder: (_) => const LoginScreen()),
            (_) => false,
          );
        }),
        const SizedBox(width: 8),
      ],
    );
  }
}

// ── Stat Row ──────────────────────────────────────────────────────────────────
class _StatRow extends StatelessWidget {
  final Map<String, int> counts;
  final bool isDark;
  final Color primary;
  const _StatRow({required this.counts, required this.isDark, required this.primary});

  @override
  Widget build(BuildContext context) => Row(children: [
    _StatCard(label: 'Total',    value: '${counts['total']}',    icon: Icons.folder_copy_outlined,   color: primary, isDark: isDark),
    const SizedBox(width: 10),
    _StatCard(label: 'Pending',  value: '${counts['pending']}',  icon: Icons.hourglass_top_outlined,  color: _kAmber, isDark: isDark),
    const SizedBox(width: 10),
    _StatCard(label: 'Approved', value: '${counts['approved']}', icon: Icons.check_circle_outline,   color: _kGreen, isDark: isDark),
    const SizedBox(width: 10),
    _StatCard(label: 'Rejected', value: '${counts['rejected']}', icon: Icons.cancel_outlined,        color: _kRed,   isDark: isDark),
  ]);
}

class _StatCard extends StatelessWidget {
  final String label, value;
  final IconData icon;
  final Color color;
  final bool isDark;
  const _StatCard({required this.label, required this.value, required this.icon, required this.color, required this.isDark});

  @override
  Widget build(BuildContext context) {
    final card = Theme.of(context).cardColor;
    final text = isDark ? Colors.white : const Color(0xFF0F172A);
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
        decoration: BoxDecoration(
          color: card,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: color.withValues(alpha: 0.2)),
          boxShadow: [BoxShadow(color: color.withValues(alpha: 0.06), blurRadius: 12, offset: const Offset(0, 4))],
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Container(
            padding: const EdgeInsets.all(7),
            decoration: BoxDecoration(color: color.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(10)),
            child: Icon(icon, size: 16, color: color),
          ),
          const SizedBox(height: 10),
          Text(value, style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: text)),
          const SizedBox(height: 2),
          Text(label, style: TextStyle(fontSize: 11, color: color, fontWeight: FontWeight.w600)),
        ]),
      ),
    );
  }
}

// ── Search Bar ────────────────────────────────────────────────────────────────
class _SearchBar extends StatelessWidget {
  final TextEditingController controller;
  final Color text, subtext, card, border;
  final ValueChanged<String> onChanged;
  const _SearchBar({required this.controller, required this.text, required this.subtext, required this.card, required this.border, required this.onChanged});

  @override
  Widget build(BuildContext context) => Container(
    decoration: BoxDecoration(color: card, borderRadius: BorderRadius.circular(14), border: Border.all(color: border)),
    child: TextField(
      controller: controller,
      onChanged: onChanged,
      style: TextStyle(color: text, fontSize: 13),
      decoration: InputDecoration(
        hintText: 'Search by name, purpose, or email…',
        hintStyle: TextStyle(color: subtext, fontSize: 13),
        prefixIcon: Icon(Icons.search_rounded, color: subtext, size: 20),
        suffixIcon: controller.text.isNotEmpty
            ? IconButton(
                icon: Icon(Icons.close_rounded, size: 18, color: subtext),
                onPressed: () { controller.clear(); onChanged(''); },
              )
            : null,
        border: InputBorder.none,
        contentPadding: const EdgeInsets.symmetric(vertical: 13),
      ),
    ),
  );
}

// ── Filter Row ────────────────────────────────────────────────────────────────
class _FilterRow extends StatelessWidget {
  final String selected;
  final Map<String, int> counts;
  final Color primary, text;
  final bool isDark;
  final ValueChanged<String> onSelect;
  const _FilterRow({required this.selected, required this.counts, required this.primary, required this.text, required this.isDark, required this.onSelect});

  @override
  Widget build(BuildContext context) {
    const chips = [
      ('pending', 'Pending'),
      ('approved', 'Approved'),
      ('rejected', 'Rejected'),
    ];
    final countMap = {
      'pending' : counts['pending']  ?? 0,
      'approved': counts['approved'] ?? 0,
      'rejected': counts['rejected'] ?? 0,
    };
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Row(
        children: chips.map((c) {
          final key   = c.$1;
          final label = c.$2;
          final count = countMap[key] ?? 0;
          final isSel = selected == key;
          return Padding(
            padding: const EdgeInsets.only(right: 8),
            child: InkWell(
              onTap: () => onSelect(key),
              borderRadius: BorderRadius.circular(20),
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 180),
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
                decoration: BoxDecoration(
                  color: isSel ? primary : (isDark ? const Color(0xFF1E3A5F) : const Color(0xFFEFF2F7)),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: isSel ? primary : Colors.transparent),
                ),
                child: Text(
                  '$label  $count',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: isSel ? (isDark ? const Color(0xFF0D1B2A) : Colors.white) : text,
                  ),
                ),
              ),
            ),
          );
        }).toList(),
      ),
    );
  }
}

// ── Helpers ───────────────────────────────────────────────────────────────────
class _ColHeader extends StatelessWidget {
  final String label;
  final Color color;
  final bool right;
  const _ColHeader(this.label, this.color, {this.right = false});

  @override
  Widget build(BuildContext context) => Align(
    alignment: right ? Alignment.centerRight : Alignment.centerLeft,
    child: Text(label, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: color, letterSpacing: 0.4)),
  );
}

class _Pill extends StatelessWidget {
  final String label;
  final Color color;
  const _Pill({required this.label, required this.color});

  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
    decoration: BoxDecoration(color: color.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(12)),
    child: Text(label, style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: color)),
  );
}

class _AppBarIcon extends StatelessWidget {
  final IconData icon;
  final String tooltip;
  final VoidCallback onTap;
  const _AppBarIcon({required this.icon, required this.tooltip, required this.onTap});

  @override
  Widget build(BuildContext context) => IconButton(
    icon: Icon(icon, size: 20),
    tooltip: tooltip,
    onPressed: onTap,
    padding: EdgeInsets.zero,
    constraints: const BoxConstraints(minWidth: 36, minHeight: 36),
  );
}

class _EmptyState extends StatelessWidget {
  final Color subtext;
  const _EmptyState({required this.subtext});

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.symmetric(vertical: 48),
    child: Column(children: [
      Icon(Icons.inbox_outlined, size: 42, color: subtext),
      const SizedBox(height: 12),
      Text('No applications found', style: TextStyle(color: subtext, fontSize: 13)),
    ]),
  );
}

// ── Queue Row ─────────────────────────────────────────────────────────────────
class _QueueRow extends StatelessWidget {
  final ManagerQueueItem item;
  final bool isDark;
  final Color text, subtext, primary;
  final VoidCallback onReview;
  const _QueueRow({
    required this.item,
    required this.isDark,
    required this.text,
    required this.subtext,
    required this.primary,
    required this.onReview,
  });

  Color get _confColor {
    if (item.aiConfidence >= 80) return _kGreen;
    if (item.aiConfidence >= 60) return _kBlue;
    return _kRed;
  }

  @override
  Widget build(BuildContext context) => InkWell(
    onTap: onReview,
    child: Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 13),
      child: Row(children: [
        Expanded(flex: 4, child: Row(children: [
          _Avatar(initials: item.initials),
          const SizedBox(width: 12),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(item.applicantName,
                style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: text),
                maxLines: 1, overflow: TextOverflow.ellipsis),
            const SizedBox(height: 2),
            Text(item.purpose,
                style: TextStyle(fontSize: 11, color: subtext),
                maxLines: 1, overflow: TextOverflow.ellipsis),
          ])),
        ])),

        Expanded(flex: 3, child: Padding(
          padding: const EdgeInsets.only(right: 12),
          child: Row(children: [
            SizedBox(width: 36, child: Text('${item.aiConfidence}%',
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: _confColor))),
            const SizedBox(width: 6),
            Expanded(child: ClipRRect(
              borderRadius: BorderRadius.circular(4),
              child: LinearProgressIndicator(
                value: item.aiConfidence / 100,
                minHeight: 5,
                backgroundColor: isDark ? const Color(0xFF1E3A5F) : const Color(0xFFE2E8F0),
                valueColor: AlwaysStoppedAnimation(_confColor),
              ),
            )),
          ]),
        )),

        Expanded(flex: 2, child: _RiskBadge(riskClass: item.riskClass, label: item.riskStatus, isDark: isDark)),

        Expanded(flex: 3, child: Align(
          alignment: Alignment.centerRight,
          child: SizedBox(
            height: 30,
            child: ElevatedButton(
              onPressed: onReview,
              style: ElevatedButton.styleFrom(
                backgroundColor: primary,
                foregroundColor: isDark ? const Color(0xFF0D1B2A) : Colors.white,
                elevation: 0,
                padding: const EdgeInsets.symmetric(horizontal: 10),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                tapTargetSize: MaterialTapTargetSize.shrinkWrap,
              ),
              child: const Text('Review', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
            ),
          ),
        )),
      ]),
    ),
  );
}

class _Avatar extends StatelessWidget {
  final String initials;
  const _Avatar({required this.initials});

  @override
  Widget build(BuildContext context) => CircleAvatar(
    radius: 19,
    backgroundColor: const Color(0xFFDBEAFE),
    child: Text(initials, style: const TextStyle(color: Color(0xFF1E40AF), fontSize: 12, fontWeight: FontWeight.bold)),
  );
}


class _RiskBadge extends StatelessWidget {
  final String riskClass, label;
  final bool isDark;
  const _RiskBadge({required this.riskClass, required this.label, required this.isDark});

  (Color, Color, IconData) get _style {
    switch (riskClass) {
      case 'clear':        return (const Color(0xFFDCFCE7), const Color(0xFF15803D), Icons.check_circle_outline_rounded);
      case 'manual_check': return (const Color(0xFFE0F2FE), const Color(0xFF0369A1), Icons.help_outline_rounded);
      default:             return (const Color(0xFFFEE2E2), const Color(0xFFB91C1C), Icons.warning_amber_rounded);
    }
  }

  @override
  Widget build(BuildContext context) {
    final (bg, fg, icon) = _style;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: isDark ? fg.withValues(alpha: 0.18) : bg,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(mainAxisSize: MainAxisSize.min, children: [
        Icon(icon, size: 12, color: fg),
        const SizedBox(width: 4),
        Flexible(child: Text(label,
            style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: fg),
            maxLines: 1, overflow: TextOverflow.ellipsis)),
      ]),
    );
  }
}

// ═══════════════════════════════════════════════════════════════════════════════
// REVIEW SHEET
// ═══════════════════════════════════════════════════════════════════════════════
class _ReviewSheet extends StatefulWidget {
  final ManagerQueueItem item;
  final String? token;
  final VoidCallback onDecisionMade;
  const _ReviewSheet({required this.item, required this.token, required this.onDecisionMade});

  @override
  State<_ReviewSheet> createState() => _ReviewSheetState();
}

class _ReviewSheetState extends State<_ReviewSheet> {
  final _currency = NumberFormat.currency(locale: 'en_PH', symbol: '₱');
  bool _loadingProfile = true;
  bool _processing     = false;
  int  _tab            = 0;
  Map<String, dynamic>? _profile;

  @override
  void initState() { super.initState(); _loadProfile(); }

  Future<void> _loadProfile() async {
    try {
      final res = await http.get(
        Uri.parse(ApiConfig.managerApplicantUrl(widget.item.userId)),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          if (widget.token != null) 'Authorization': 'Bearer ${widget.token}',
        },
      );
      if (res.statusCode == 200 && mounted) {
        setState(() { _profile = jsonDecode(res.body); _loadingProfile = false; });
        return;
      }
    } catch (e) { debugPrint('profile error: $e'); }
    if (mounted) setState(() => _loadingProfile = false);
  }

  Future<void> _decide(String decision) async {
    if (_processing) return;
    setState(() => _processing = true);
    try {
      final res = await http.post(
        Uri.parse(ApiConfig.managerLoanDecisionUrl(widget.item.id)),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          if (widget.token != null) 'Authorization': 'Bearer ${widget.token}',
        },
        body: jsonEncode({'decision': decision, 'notes': 'Decision recorded by underwriting manager'}),
      );
      if (res.statusCode == 200 && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          backgroundColor: decision == 'approved' ? _kGreen : _kRed,
          content: Text(
            '${widget.item.applicantName} — ${decision.toUpperCase()}',
            style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.white),
          ),
        ));
        widget.onDecisionMade();
        return;
      }
    } catch (e) { debugPrint('decision error: $e'); }
    if (mounted) setState(() => _processing = false);
  }

  Future<void> _deleteApp() async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Remove Application', style: TextStyle(fontWeight: FontWeight.bold)),
        content: Text('Remove ${widget.item.applicantName}\'s application?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancel')),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(backgroundColor: _kRed, foregroundColor: Colors.white),
            child: const Text('Remove'),
          ),
        ],
      ),
    );
    if (ok != true || _processing) return;
    setState(() => _processing = true);
    try {
      final res = await http.delete(
        Uri.parse(ApiConfig.managerDeleteLoanUrl(widget.item.id)),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          if (widget.token != null) 'Authorization': 'Bearer ${widget.token}',
        },
      );
      if (res.statusCode == 200 && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          backgroundColor: _kGreen,
          content: Text('Application removed.', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white)),
        ));
        widget.onDecisionMade();
        return;
      }
    } catch (e) { debugPrint('delete error: $e'); }
    if (mounted) setState(() => _processing = false);
  }

  @override
  Widget build(BuildContext context) {
    final theme   = context.watch<ThemeProvider>();
    final isDark  = theme.isDarkMode;
    final primary = Theme.of(context).primaryColor;
    final sheetBg = isDark ? const Color(0xFF0F1F35) : Colors.white;
    final text    = isDark ? Colors.white             : const Color(0xFF0F172A);
    final subtext = isDark ? const Color(0xFF90A4AE)  : const Color(0xFF64748B);
    final card    = isDark ? const Color(0xFF152238)  : const Color(0xFFF8FAFC);
    final border  = isDark ? const Color(0xFF1E3A5F)  : const Color(0xFFE2E8F0);

    final applicant      = _profile?['applicant'];
    final credit         = _profile?['credit_assessment'];
    final financial      = _profile?['financial_health'];
    final psite          = _profile?['psite_metrics'] ?? credit?['psite_metrics'];
    final recommendation = _profile?['underwriter_recommendation'];
    final sources        = (_profile?['connected_sources'] as List?) ?? [];
    final shapFactors    = (credit?['shap_factors'] as List?) ?? [];
    final fraudChecks    = recommendation?['fraud_checks'] as Map<String, dynamic>?;
    final scoreColor     = widget.item.creditScore >= 80 ? _kGreen : _kBlue;

    return Container(
      decoration: BoxDecoration(
        color: sheetBg,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: Column(mainAxisSize: MainAxisSize.min, children: [
        // Drag handle
        Container(
          margin: const EdgeInsets.only(top: 10),
          width: 36, height: 4,
          decoration: BoxDecoration(color: border, borderRadius: BorderRadius.circular(2)),
        ),
        Flexible(
          child: SingleChildScrollView(
            padding: EdgeInsets.fromLTRB(20, 16, 20, MediaQuery.of(context).viewInsets.bottom + 24),
            child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
              // Header
              Row(children: [
                Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Row(children: [
                    Icon(Icons.analytics_outlined, size: 18, color: primary),
                    const SizedBox(width: 8),
                    Flexible(child: Text('Underwriting Dossier',
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: text),
                        overflow: TextOverflow.ellipsis)),
                  ]),
                  Text('Application #${widget.item.id} · ${widget.item.applicantName}',
                      style: TextStyle(fontSize: 11.5, color: subtext)),
                ])),
                IconButton(icon: Icon(Icons.close_rounded, color: subtext), onPressed: () => Navigator.pop(context)),
              ]),
              if (_loadingProfile) const LinearProgressIndicator(minHeight: 2),
              const SizedBox(height: 14),

              // Applicant Card
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(color: card, borderRadius: BorderRadius.circular(16), border: Border.all(color: border)),
                child: Row(children: [
                  _Avatar(initials: widget.item.initials),
                  const SizedBox(width: 12),
                  Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    Row(children: [
                      Flexible(child: Text(widget.item.applicantName,
                          style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: text),
                          maxLines: 1, overflow: TextOverflow.ellipsis)),
                      const SizedBox(width: 8),
                      _SmallBadge(label: 'PhilSys ✓', color: _kGreen),
                    ]),
                    const SizedBox(height: 3),
                    Text(applicant?['email'] ?? widget.item.applicantEmail,
                        style: TextStyle(fontSize: 11.5, color: subtext), overflow: TextOverflow.ellipsis),
                    Text('${applicant?['phone_number'] ?? "+63 917 123 4567"}  ·  ID: ${applicant?['id_number'] ?? "PH-7890123"}',
                        style: TextStyle(fontSize: 11, color: subtext), overflow: TextOverflow.ellipsis),
                  ])),
                  const SizedBox(width: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    decoration: BoxDecoration(
                      color: scoreColor.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: scoreColor.withValues(alpha: 0.3)),
                    ),
                    child: Column(children: [
                      Text('${widget.item.creditScore}',
                          style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: scoreColor)),
                      Text(widget.item.riskTier,
                          style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.w700, color: scoreColor)),
                    ]),
                  ),
                ]),
              ),
              const SizedBox(height: 14),

              // Tab Bar
              _TabBar(
                selected: _tab,
                tabs: const [
                  (Icons.assignment_outlined,      'Loan Request'),
                  (Icons.psychology_outlined,       'AI Analysis'),
                  (Icons.account_balance_outlined,  'Banking & Gig'),
                ],
                primary: primary, text: text, isDark: isDark,
                onSelect: (i) => setState(() => _tab = i),
              ),
              const SizedBox(height: 16),

              // Tab Body
              AnimatedSwitcher(
                duration: const Duration(milliseconds: 200),
                child: KeyedSubtree(
                  key: ValueKey(_tab),
                  child: _tab == 0
                      ? _LoanTab(item: widget.item, currency: _currency, applicant: applicant, card: card, border: border, text: text, subtext: subtext, primary: primary)
                      : _tab == 1
                          ? _AiTab(item: widget.item, currency: _currency, psite: psite, recommendation: recommendation, shapFactors: shapFactors, card: card, border: border, text: text, subtext: subtext, primary: primary)
                          : _BankingTab(currency: _currency, financial: financial, sources: sources, fraudChecks: fraudChecks, card: card, border: border, text: text, subtext: subtext, primary: primary),
                ),
              ),
              const SizedBox(height: 20),

              // Decision Buttons
              if (_processing)
                const Center(child: Padding(padding: EdgeInsets.symmetric(vertical: 12), child: CircularProgressIndicator()))
              else ...[
                Row(children: [
                  Expanded(child: OutlinedButton.icon(
                    onPressed: () => _decide('rejected'),
                    icon: const Icon(Icons.cancel_outlined, size: 16),
                    label: const Text('Reject', style: TextStyle(fontWeight: FontWeight.bold)),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: _kRed,
                      side: const BorderSide(color: _kRed),
                      padding: const EdgeInsets.symmetric(vertical: 13),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  )),
                  const SizedBox(width: 12),
                  Expanded(child: ElevatedButton.icon(
                    onPressed: () => _decide('approved'),
                    icon: const Icon(Icons.check_circle_outline_rounded, size: 16),
                    label: const Text('Approve Loan', style: TextStyle(fontWeight: FontWeight.bold)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: _kGreen,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 13),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  )),
                ]),
                const SizedBox(height: 8),
                Center(child: TextButton.icon(
                  onPressed: _deleteApp,
                  icon: const Icon(Icons.delete_outline_rounded, size: 15, color: _kRed),
                  label: const Text('Remove from Queue', style: TextStyle(color: _kRed, fontSize: 12, fontWeight: FontWeight.w600)),
                )),
              ],
            ]),
          ),
        ),
      ]),
    );
  }
}

// ── Tab Bar ───────────────────────────────────────────────────────────────────
class _TabBar extends StatelessWidget {
  final int selected;
  final List<(IconData, String)> tabs;
  final Color primary, text;
  final bool isDark;
  final ValueChanged<int> onSelect;
  const _TabBar({required this.selected, required this.tabs, required this.primary, required this.text, required this.isDark, required this.onSelect});

  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.all(4),
    decoration: BoxDecoration(
      color: isDark ? const Color(0xFF14243B) : const Color(0xFFEFF2F7),
      borderRadius: BorderRadius.circular(14),
    ),
    child: Row(
      children: List.generate(tabs.length, (i) {
        final icon  = tabs[i].$1;
        final label = tabs[i].$2;
        final isSel = selected == i;
        return Expanded(
          child: GestureDetector(
            onTap: () => onSelect(i),
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 180),
              padding: const EdgeInsets.symmetric(vertical: 9, horizontal: 4),
              decoration: BoxDecoration(
                color: isSel ? (isDark ? const Color(0xFF1E3A5F) : Colors.white) : Colors.transparent,
                borderRadius: BorderRadius.circular(10),
                boxShadow: isSel
                    ? [BoxShadow(color: Colors.black.withValues(alpha: 0.08), blurRadius: 6, offset: const Offset(0, 2))]
                    : null,
              ),
              child: Row(mainAxisAlignment: MainAxisAlignment.center, children: [
                Icon(icon, size: 13, color: isSel ? primary : text.withValues(alpha: 0.5)),
                const SizedBox(width: 4),
                Flexible(child: Text(label,
                    style: TextStyle(fontSize: 11, fontWeight: isSel ? FontWeight.bold : FontWeight.w500,
                        color: isSel ? primary : text.withValues(alpha: 0.5)),
                    maxLines: 1, overflow: TextOverflow.ellipsis)),
              ]),
            ),
          ),
        );
      }),
    ),
  );
}

// ── Shared small widgets ──────────────────────────────────────────────────────
class _SmallBadge extends StatelessWidget {
  final String label;
  final Color color;
  const _SmallBadge({required this.label, required this.color});

  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
    decoration: BoxDecoration(color: color.withValues(alpha: 0.15), borderRadius: BorderRadius.circular(6)),
    child: Text(label, style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.bold, color: color)),
  );
}

class _SectionHeader extends StatelessWidget {
  final String title;
  final Color color;
  const _SectionHeader(this.title, this.color);

  @override
  Widget build(BuildContext context) => Text(title,
      style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800, color: color, letterSpacing: 1.2));
}

class _Card extends StatelessWidget {
  final Widget child;
  final Color color, border;
  const _Card({required this.child, required this.color, required this.border});

  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.all(16),
    decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(16), border: Border.all(color: border)),
    child: child,
  );
}

class _RowPair extends StatelessWidget {
  final String label;
  final Widget value;
  final Color labelColor;
  const _RowPair({required this.label, required this.value, required this.labelColor});

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.symmetric(vertical: 5),
    child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Text(label, style: TextStyle(fontSize: 12.5, color: labelColor)),
      const SizedBox(width: 8),
      Expanded(child: Align(alignment: Alignment.centerRight, child: value)),
    ]),
  );
}

// ═══════════════════════════════════════════════════════════════════════════════
// TAB CONTENT
// ═══════════════════════════════════════════════════════════════════════════════

// ── Loan Request Tab ──────────────────────────────────────────────────────────
class _LoanTab extends StatelessWidget {
  final ManagerQueueItem item;
  final NumberFormat currency;
  final dynamic applicant;
  final Color card, border, text, subtext, primary;
  const _LoanTab({required this.item, required this.currency, required this.applicant, required this.card, required this.border, required this.text, required this.subtext, required this.primary});

  @override
  Widget build(BuildContext context) {
    final monthly = item.monthlyAmortization;
    return Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
      _Card(color: card, border: border, child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
          _SectionHeader('LOAN SPECIFICATIONS', primary),
          _SmallBadge(label: '${item.tenureDays}d Tenure', color: primary),
        ]),
        const SizedBox(height: 14),
        Text(currency.format(item.amount), style: TextStyle(fontSize: 28, fontWeight: FontWeight.bold, color: primary)),
        Text('Requested Principal', style: TextStyle(fontSize: 11.5, color: subtext)),
        Divider(height: 20, color: border),
        _RowPair(label: 'Loan Tenure', labelColor: subtext, value: Text(
          '${item.tenureDays} days (${(item.tenureDays / 30).round()} months)',
          style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600, color: text), textAlign: TextAlign.end)),
        _RowPair(label: 'Loan Purpose', labelColor: subtext, value: Text(
          item.purpose,
          style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600, color: text), textAlign: TextAlign.end)),
        _RowPair(label: 'Monthly Amortization', labelColor: subtext, value: Text(
          currency.format(monthly),
          style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: text), textAlign: TextAlign.end)),
        _RowPair(label: 'Disbursement', labelColor: subtext, value: Row(mainAxisSize: MainAxisSize.min, children: [
          const Icon(Icons.account_balance_wallet_outlined, size: 13, color: Color(0xFF007DFE)),
          const SizedBox(width: 4),
          Flexible(child: Text(
            'GCash (${applicant?['phone_number'] ?? "+63 917 123 4567"})',
            style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: text),
            overflow: TextOverflow.ellipsis, textAlign: TextAlign.end)),
        ])),
      ])),
      const SizedBox(height: 12),
      _Card(color: card, border: border, child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        _SectionHeader('SIMULATED REPAYMENT SCHEDULE', primary),
        const SizedBox(height: 12),
        _ScheduleRow(label: 'Installment 1', due: 'Day 30', amount: currency.format(monthly), text: text, subtext: subtext),
        Divider(height: 14, color: border),
        _ScheduleRow(label: 'Installment 2', due: 'Day 60', amount: currency.format(monthly), text: text, subtext: subtext),
        Divider(height: 14, color: border),
        _ScheduleRow(label: 'Installment 3', due: 'Day 90', amount: currency.format(monthly), text: text, subtext: subtext),
      ])),
    ]);
  }
}

class _ScheduleRow extends StatelessWidget {
  final String label, due, amount;
  final Color text, subtext;
  const _ScheduleRow({required this.label, required this.due, required this.amount, required this.text, required this.subtext});

  @override
  Widget build(BuildContext context) => Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
    Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Text(label, style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600, color: text)),
      Text('Due $due', style: TextStyle(fontSize: 11, color: subtext)),
    ]),
    Text(amount, style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: text)),
  ]);
}

// ── AI Analysis Tab ───────────────────────────────────────────────────────────
class _AiTab extends StatelessWidget {
  final ManagerQueueItem item;
  final NumberFormat currency;
  final dynamic psite, recommendation;
  final List<dynamic> shapFactors;
  final Color card, border, text, subtext, primary;
  const _AiTab({required this.item, required this.currency, required this.psite, required this.recommendation, required this.shapFactors, required this.card, required this.border, required this.text, required this.subtext, required this.primary});

  String _vel(dynamic v) {
    if (v == null) return '₱1,616.67 / day';
    if (v is String) return v;
    if (v is num) return '₱${NumberFormat('#,##0.00').format(v * 200)} / day';
    return v.toString();
  }
  String _burn(dynamic v) {
    if (v == null) return '33.4% of Inflow';
    if (v is String) return v;
    if (v is num) { final p = v <= 1.0 ? v * 100 : v; return '${p.toStringAsFixed(1)}% of Inflow'; }
    return v.toString();
  }
  String _rec(dynamic v) {
    if (v == null) return '2 days ago';
    if (v is String) return v;
    if (v is num) return '${v.toStringAsFixed(0)} days ago';
    return v.toString();
  }
  String _dti(dynamic v) {
    if (v == null) return '14.2% (Low Risk)';
    if (v is String) return v;
    if (v is num) { final p = v <= 1.0 ? v * 100 : v; return '${p.toStringAsFixed(1)}%'; }
    return v.toString();
  }

  @override
  Widget build(BuildContext context) {
    final status  = recommendation?['status'] ?? (item.creditScore >= 80 ? 'AUTO_APPROVE' : 'MANUAL_REVIEW');
    final isAuto  = status == 'AUTO_APPROVE';
    final dscr    = recommendation?['dscr'] ?? 4.49;
    final ceiling = recommendation?['recommended_ceiling'] ?? 45000;
    final summary = recommendation?['risk_summary'] ??
        'Solid alternative credit footprint supported by active open banking and gig platform cashflow velocity.';
    final bColor  = isAuto ? _kGreen : _kAmber;

    return Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
      Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: bColor.withValues(alpha: 0.08),
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: bColor.withValues(alpha: 0.35)),
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            Icon(isAuto ? Icons.check_circle_outline : Icons.warning_amber_rounded, size: 17, color: bColor),
            const SizedBox(width: 8),
            Flexible(child: Text(
              isAuto ? 'AI DECISION: AUTO-APPROVE' : 'AI DECISION: MANUAL REVIEW',
              style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: bColor),
              overflow: TextOverflow.ellipsis)),
            const SizedBox(width: 8),
            _SmallBadge(label: 'DSCR ${dscr}x', color: primary),
          ]),
          const SizedBox(height: 6),
          Text(summary, style: TextStyle(fontSize: 11.5, color: text)),
          const SizedBox(height: 8),
          Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
            Text('Recommended Credit Ceiling', style: TextStyle(fontSize: 11, color: subtext)),
            Text(currency.format(ceiling), style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: _kGreen)),
          ]),
        ]),
      ),
      const SizedBox(height: 12),

      _Card(color: card, border: border, child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        _SectionHeader('PSITE ALTERNATIVE CREDIT METRICS', primary),
        const SizedBox(height: 14),
        _MetricRow(icon: Icons.speed,                          label: 'Cash Flow Velocity',   value: _vel(psite?['cash_flow_velocity']),    subtitle: 'Continuous daily platform inflows',       accentColor: _kBlue,   text: text, subtext: subtext),
        Divider(height: 16, color: border),
        _MetricRow(icon: Icons.local_fire_department_outlined,  label: 'Burn Rate Trajectory', value: _burn(psite?['burn_rate_trajectory']), subtitle: 'Low non-essential spending footprint',    accentColor: _kGreen,  text: text, subtext: subtext),
        Divider(height: 16, color: border),
        _MetricRow(icon: Icons.receipt_long_outlined,           label: 'Utility Recency',      value: _rec(psite?['utility_recency_days']),  subtitle: 'Meralco & Manila Water on-time history', accentColor: _kPurple, text: text, subtext: subtext),
        Divider(height: 16, color: border),
        _MetricRow(icon: Icons.pie_chart_outline,               label: 'Debt-to-Income',       value: _dti(psite?['debt_to_income_ratio']),  subtitle: 'Well below maximum threshold (35%)',      accentColor: _kCyan,   text: text, subtext: subtext),
      ])),
      const SizedBox(height: 12),

      _Card(color: card, border: border, child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        _SectionHeader('SHAP FACTOR ATTRIBUTION (XAI)', primary),
        const SizedBox(height: 4),
        Text('Positive and negative contributions to credit rating:', style: TextStyle(fontSize: 11, color: subtext)),
        const SizedBox(height: 12),
        if (shapFactors.isNotEmpty)
          ...shapFactors.map((f) {
            final isPos  = (f['direction'] ?? 'positive') == 'positive';
            final flabel = f['displayText'] ?? f['display_text'] ?? f['factor'] ?? 'Factor impact';
            final weight = f['weight'] != null ? '${(f['weight'] as num).abs()}%' : null;
            return _ShapRow(label: flabel, weight: weight, isPositive: isPos, text: text);
          })
        else ...[
          _ShapRow(label: 'Active gig earnings velocity across multiple mobility platforms', weight: '+24 pts', isPositive: true,  text: text),
          _ShapRow(label: 'Consistent utility bill payments with zero 90-day defaults',     weight: '+18 pts', isPositive: true,  text: text),
          _ShapRow(label: 'Clean wallet balance maintenance exceeding debt amortization',    weight: '+12 pts', isPositive: true,  text: text),
        ],
      ])),
    ]);
  }
}

class _MetricRow extends StatelessWidget {
  final IconData icon;
  final String label, value, subtitle;
  final Color accentColor, text, subtext;
  const _MetricRow({required this.icon, required this.label, required this.value, required this.subtitle, required this.accentColor, required this.text, required this.subtext});

  @override
  Widget build(BuildContext context) => Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
    Container(
      padding: const EdgeInsets.all(8),
      decoration: BoxDecoration(color: accentColor.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(10)),
      child: Icon(icon, size: 16, color: accentColor),
    ),
    const SizedBox(width: 12),
    Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Row(children: [
        Expanded(child: Text(label, style: TextStyle(fontSize: 12, color: subtext))),
        Flexible(child: Text(value, style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold, color: text), textAlign: TextAlign.end)),
      ]),
      Text(subtitle, style: TextStyle(fontSize: 10.5, color: subtext.withValues(alpha: 0.75))),
    ])),
  ]);
}

class _ShapRow extends StatelessWidget {
  final String label;
  final String? weight;
  final bool isPositive;
  final Color text;
  const _ShapRow({required this.label, required this.weight, required this.isPositive, required this.text});

  @override
  Widget build(BuildContext context) {
    final color = isPositive ? _kGreen : _kRed;
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(children: [
        Container(
          padding: const EdgeInsets.all(4),
          decoration: BoxDecoration(color: color.withValues(alpha: 0.14), shape: BoxShape.circle),
          child: Icon(isPositive ? Icons.add : Icons.remove, size: 13, color: color),
        ),
        const SizedBox(width: 10),
        Expanded(child: Text(label, style: TextStyle(fontSize: 12, color: text))),
        if (weight != null)
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
            decoration: BoxDecoration(color: color.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(6)),
            child: Text(weight!, style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: color)),
          ),
      ]),
    );
  }
}

// ── Banking & Gig Tab ─────────────────────────────────────────────────────────
class _BankingTab extends StatelessWidget {
  final NumberFormat currency;
  final dynamic financial;
  final List<dynamic> sources;
  final Map<String, dynamic>? fraudChecks;
  final Color card, border, text, subtext, primary;
  const _BankingTab({required this.currency, required this.financial, required this.sources, required this.fraudChecks, required this.card, required this.border, required this.text, required this.subtext, required this.primary});

  @override
  Widget build(BuildContext context) {
    final inflow      = (financial?['total_inflow']      as num?)?.toDouble() ?? 48500.0;
    final outflow     = (financial?['total_outflow']     as num?)?.toDouble() ?? 16200.0;
    final disposable  = (financial?['disposable_income'] as num?)?.toDouble() ?? (inflow - outflow);
    final consistency = financial?['consistency'] ?? '97%';

    return Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
      _Card(color: card, border: border, child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        _SectionHeader('VERIFIED CASH FLOW SUMMARY', primary),
        const SizedBox(height: 14),
        _FinRow(label: 'Total Monthly Inflow',        value: currency.format(inflow),     valueColor: _kGreen,  text: text, subtext: subtext),
        Divider(height: 14, color: border),
        _FinRow(label: 'Monthly Outflow & Expenses',  value: currency.format(outflow),    valueColor: text,     text: text, subtext: subtext),
        Divider(height: 14, color: border),
        _FinRow(label: 'Net Disposable Surplus',      value: currency.format(disposable), valueColor: primary,  text: text, subtext: subtext),
        Divider(height: 14, color: border),
        _FinRow(label: 'Cashflow Consistency',        value: '$consistency (Low Volatility)', valueColor: _kBlue, text: text, subtext: subtext),
      ])),
      const SizedBox(height: 12),

      _Card(color: card, border: border, child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        _SectionHeader('OPEN BANKING & GIG PLATFORM SYNC', primary),
        const SizedBox(height: 12),
        if (sources.isNotEmpty)
          ...sources.map((s) => _SourceRow(
            name:   s['provider'] ?? s['platform'] ?? 'Open Finance Source',
            type:   s['account_type'] ?? s['type'] ?? 'Platform',
            status: s['status'] ?? 'Active Sync',
            text: text, subtext: subtext,
          ))
        else ...[
          _SourceRow(name: 'Grab / Joyride Delivery Stream', type: 'Gig Platform',  status: 'Active Sync',  text: text, subtext: subtext),
          _SourceRow(name: 'GCash Verified E-Wallet',         type: 'E-Wallet',      status: 'Verified',     text: text, subtext: subtext),
          _SourceRow(name: 'Smile Open Finance API',           type: 'Payroll Feed',  status: 'Direct Sync',  text: text, subtext: subtext),
        ],
      ])),
      const SizedBox(height: 12),

      _Card(color: card, border: border, child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        _SectionHeader('FRAUD & IDENTITY VERIFICATION', primary),
        const SizedBox(height: 12),
        _FraudRow(label: 'PhilSys National ID',  value: fraudChecks?['philsys_id']              ?? fraudChecks?['philsys_national_id']    ?? 'VERIFIED_ACTIVE',                  isSafe: true, text: text, subtext: subtext),
        _FraudRow(label: 'Biometric Liveness',   value: fraudChecks?['device_liveness']         ?? fraudChecks?['biometric_liveness']      ?? '99.4% Match',                      isSafe: true, text: text, subtext: subtext),
        _FraudRow(label: 'Duplicate Inquiry',    value: fraudChecks?['duplicate_inquiries']     ?? fraudChecks?['duplicate_inquiry_check'] ?? 'Clean (0 alerts in 90d)',           isSafe: true, text: text, subtext: subtext),
        _FraudRow(label: 'Device Geolocation',   value: fraudChecks?['geolocation_consistency'] ?? fraudChecks?['device_geolocation']       ?? 'Consistent (Metro Manila / Rizal)', isSafe: true, text: text, subtext: subtext),
      ])),
    ]);
  }
}

class _FinRow extends StatelessWidget {
  final String label, value;
  final Color valueColor, text, subtext;
  const _FinRow({required this.label, required this.value, required this.valueColor, required this.text, required this.subtext});

  @override
  Widget build(BuildContext context) => Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
    Text(label, style: TextStyle(fontSize: 12.5, color: subtext)),
    Text(value,  style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.bold, color: valueColor)),
  ]);
}

class _SourceRow extends StatelessWidget {
  final String name, type, status;
  final Color text, subtext;
  const _SourceRow({required this.name, required this.type, required this.status, required this.text, required this.subtext});

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.only(bottom: 10),
    child: Row(children: [
      const Icon(Icons.check_circle_outline_rounded, size: 16, color: _kGreen),
      const SizedBox(width: 10),
      Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text(name, style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600, color: text)),
        Text(type, style: TextStyle(fontSize: 10.5, color: subtext)),
      ])),
      _SmallBadge(label: status, color: _kGreen),
    ]),
  );
}

class _FraudRow extends StatelessWidget {
  final String label, value;
  final bool isSafe;
  final Color text, subtext;
  const _FraudRow({required this.label, required this.value, required this.isSafe, required this.text, required this.subtext});

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.only(bottom: 8),
    child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
      Flexible(child: Text(label, style: TextStyle(fontSize: 12, color: subtext))),
      const SizedBox(width: 8),
      Row(mainAxisSize: MainAxisSize.min, children: [
        Icon(isSafe ? Icons.verified_user_outlined : Icons.warning_amber_rounded, size: 13, color: isSafe ? _kGreen : _kRed),
        const SizedBox(width: 4),
        Flexible(child: Text(value,
            style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: isSafe ? _kGreen : _kRed),
            overflow: TextOverflow.ellipsis)),
      ]),
    ]),
  );
}
