import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/theme_provider.dart';
import '../../widgets/finclude_logo.dart';
import 'register_screen.dart';
import '../dashboard/dashboard_screen.dart';
import '../manager/manager_dashboard_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _identifierController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _obscurePassword = true;

  @override
  void initState() {
    super.initState();
    _passwordController.addListener(() {
      setState(() {});
    });
  }

  @override
  void dispose() {
    _identifierController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  void _showForgotPasswordDialog() {
    final auth = Provider.of<AuthProvider>(context, listen: false);
    final theme = Provider.of<ThemeProvider>(context, listen: false);
    final isDark = theme.isDarkMode;

    final emailCtrl = TextEditingController(text: _identifierController.text.trim());
    final newPassCtrl = TextEditingController();
    final confirmPassCtrl = TextEditingController();

    bool obscureNew = true;
    bool obscureConfirm = true;
    bool isSubmitting = false;
    String? dialogError;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setDialogState) {
          final primaryColor = Theme.of(context).primaryColor;
          final dialogBg = isDark ? const Color(0xFF1B263B) : Colors.white;
          final textColor = isDark ? Colors.white : const Color(0xFF0F172A);
          final subColor = isDark ? const Color(0xFF90E0EF) : const Color(0xFF64748B);
          final fieldFill = isDark ? const Color(0xFF0D1B2A) : const Color(0xFFF1F5F9);
          final fieldBorder = isDark ? const Color(0xFF415A77) : const Color(0xFFE2E8F0);

          return Dialog(
            backgroundColor: dialogBg,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            insetPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(24.0),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: primaryColor.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Icon(Icons.lock_reset, color: primaryColor, size: 24),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Reset Password',
                              style: TextStyle(
                                fontSize: 18,
                                fontWeight: FontWeight.bold,
                                color: textColor,
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              'Set a new password for your account',
                              style: TextStyle(fontSize: 12, color: subColor),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 20),
                  if (dialogError != null) ...[
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: Colors.redAccent.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.redAccent.withValues(alpha: 0.3)),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.error_outline, color: Colors.redAccent, size: 16),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              dialogError!,
                              style: const TextStyle(color: Colors.redAccent, fontSize: 12),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                  ],
                  Text(
                    'Email or Username',
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: textColor),
                  ),
                  const SizedBox(height: 6),
                  TextField(
                    controller: emailCtrl,
                    style: TextStyle(color: textColor, fontSize: 14),
                    decoration: InputDecoration(
                      hintText: 'Enter registered email or username',
                      hintStyle: TextStyle(color: subColor.withValues(alpha: 0.7), fontSize: 13),
                      prefixIcon: Icon(Icons.email_outlined, color: subColor, size: 18),
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
                    'New Password',
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: textColor),
                  ),
                  const SizedBox(height: 6),
                  TextField(
                    controller: newPassCtrl,
                    obscureText: obscureNew,
                    style: TextStyle(color: textColor, fontSize: 14),
                    decoration: InputDecoration(
                      hintText: 'At least 6 characters',
                      hintStyle: TextStyle(color: subColor.withValues(alpha: 0.7), fontSize: 13),
                      prefixIcon: Icon(Icons.lock_outline, color: subColor, size: 18),
                      suffixIcon: IconButton(
                        icon: Icon(obscureNew ? Icons.visibility_off : Icons.visibility, color: subColor, size: 18),
                        onPressed: () => setDialogState(() => obscureNew = !obscureNew),
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
                  const SizedBox(height: 14),
                  Text(
                    'Confirm New Password',
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: textColor),
                  ),
                  const SizedBox(height: 6),
                  TextField(
                    controller: confirmPassCtrl,
                    obscureText: obscureConfirm,
                    style: TextStyle(color: textColor, fontSize: 14),
                    decoration: InputDecoration(
                      hintText: 'Re-enter new password',
                      hintStyle: TextStyle(color: subColor.withValues(alpha: 0.7), fontSize: 13),
                      prefixIcon: Icon(Icons.lock_outline, color: subColor, size: 18),
                      suffixIcon: IconButton(
                        icon: Icon(obscureConfirm ? Icons.visibility_off : Icons.visibility, color: subColor, size: 18),
                        onPressed: () => setDialogState(() => obscureConfirm = !obscureConfirm),
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
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton(
                          onPressed: isSubmitting ? null : () => Navigator.of(ctx).pop(),
                          style: OutlinedButton.styleFrom(
                            foregroundColor: textColor,
                            side: BorderSide(color: fieldBorder),
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          ),
                          child: const Text('Cancel'),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: ElevatedButton(
                          onPressed: isSubmitting
                              ? null
                              : () async {
                                  final email = emailCtrl.text.trim();
                                  final newPass = newPassCtrl.text;
                                  final confirmPass = confirmPassCtrl.text;

                                  if (email.isEmpty) {
                                    setDialogState(() => dialogError = 'Please enter your email or username.');
                                    return;
                                  }
                                  if (newPass.length < 6) {
                                    setDialogState(() => dialogError = 'Password must be at least 6 characters.');
                                    return;
                                  }
                                  if (newPass != confirmPass) {
                                    setDialogState(() => dialogError = 'Passwords do not match.');
                                    return;
                                  }

                                  setDialogState(() {
                                    isSubmitting = true;
                                    dialogError = null;
                                  });

                                  final messenger = ScaffoldMessenger.of(context);
                                  final nav = Navigator.of(ctx);

                                  final result = await auth.resetPassword(email, newPass);

                                  if (!mounted || !ctx.mounted) return;

                                  if (result['success'] == true) {
                                    nav.pop();
                                    _identifierController.text = email;
                                    _passwordController.text = newPass;
                                    messenger.showSnackBar(
                                      SnackBar(
                                        backgroundColor: const Color(0xFF10B981),
                                        content: Text(
                                          result['message'] ?? 'Password reset successfully! You can now sign in.',
                                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
                                        ),
                                      ),
                                    );
                                  } else {
                                    setDialogState(() {
                                      isSubmitting = false;
                                      dialogError = result['message'] ?? 'Failed to reset password.';
                                    });
                                  }
                                },
                          style: ElevatedButton.styleFrom(
                            backgroundColor: primaryColor,
                            foregroundColor: isDark ? const Color(0xFF0D1B2A) : Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          ),
                          child: isSubmitting
                              ? const SizedBox(
                                  height: 18,
                                  width: 18,
                                  child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                                )
                              : const Text('Update Password', style: TextStyle(fontWeight: FontWeight.bold)),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  void _handleLogin() async {
    final auth = Provider.of<AuthProvider>(context, listen: false);
    final identifier = _identifierController.text.trim();
    final password = _passwordController.text;

    if (identifier.isEmpty || password.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please enter your email/username and password.')),
      );
      return;
    }

    final success = await auth.login(identifier, password);
    if (!mounted) return;

    if (success) {
      if (auth.user?.isManager == true || identifier.toLowerCase() == 'hyaylanan13@gmail.com') {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (_) => const ManagerDashboardScreen()),
        );
      } else {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (_) => const DashboardScreen()),
        );
      }
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(auth.errorMessage ?? 'Authentication failed'),
          backgroundColor: Colors.redAccent,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final theme = context.watch<ThemeProvider>();
    final isDark = theme.isDarkMode;

    final primaryColor = Theme.of(context).primaryColor;
    final subtextColor = isDark ? const Color(0xFF90E0EF) : const Color(0xFF64748B);

    return Scaffold(
      extendBodyBehindAppBar: true,
      appBar: AppBar(
        automaticallyImplyLeading: false,
        backgroundColor: Colors.transparent,
        elevation: 0,
        actions: [
          IconButton(
            icon: Icon(
              isDark ? Icons.light_mode_outlined : Icons.dark_mode_outlined,
              color: primaryColor,
            ),
            tooltip: isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode',
            onPressed: () => theme.toggleTheme(),
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: Container(
        color: isDark ? const Color(0xFF0D1B2A) : Colors.white,
        child: SafeArea(
          child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 8.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
                  decoration: BoxDecoration(
                    color: isDark ? const Color(0xFF1E293B) : Colors.white,
                    borderRadius: BorderRadius.circular(32),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.10),
                        blurRadius: 32,
                        spreadRadius: 0,
                        offset: const Offset(0, 8),
                      ),
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.06),
                        blurRadius: 8,
                        spreadRadius: 0,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Center(
                        child: Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: isDark ? const Color(0xFF0F172A) : Colors.white,
                            borderRadius: BorderRadius.circular(16),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withValues(alpha: 0.05),
                                blurRadius: 10,
                                offset: const Offset(0, 4),
                              ),
                            ],
                          ),
                          child: const FincludeLogo(size: 48, showText: false),
                        ),
                      ),
                      const SizedBox(height: 16),
                      Text(
                        'Finclude-AI',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          fontSize: 26,
                          fontWeight: FontWeight.w900,
                          letterSpacing: 0.5,
                          color: isDark ? Colors.white : const Color(0xFF0F172A),
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'Alternative AI Credit Scoring & Micro-Lending\nfor Filipino Gig Workers',
                        textAlign: TextAlign.center,
                        style: TextStyle(fontSize: 13, color: subtextColor, height: 1.4),
                      ),
                      const SizedBox(height: 28),
                      _buildTextField(
                        controller: _identifierController,
                        hint: 'Email or Username',
                        icon: Icons.person_outline,
                        isDark: isDark,
                      ),
                      const SizedBox(height: 16),
                      _buildTextField(
                        controller: _passwordController,
                        hint: 'Password',
                        icon: Icons.lock_outline,
                        isDark: isDark,
                        isPassword: true,
                        obscureText: _obscurePassword,
                        onToggleVisibility: () => setState(() => _obscurePassword = !_obscurePassword),
                      ),
                      const SizedBox(height: 12),
                      Align(
                        alignment: Alignment.centerRight,
                        child: TextButton(
                          onPressed: _showForgotPasswordDialog,
                          style: TextButton.styleFrom(
                            padding: EdgeInsets.zero,
                            minimumSize: Size.zero,
                            tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                          ),
                          child: Text(
                            'Forgot password?',
                            style: TextStyle(
                              fontSize: 12,
                              color: isDark ? const Color(0xFF90E0EF) : const Color(0xFF0077B6),
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 24),
                      ElevatedButton(
                        onPressed: auth.isLoading ? null : _handleLogin,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: isDark ? Colors.white : const Color(0xFF1E293B),
                          foregroundColor: isDark ? Colors.black : Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 16),
                          elevation: 0,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                        child: auth.isLoading
                            ? SizedBox(
                                height: 20,
                                width: 20,
                                child: CircularProgressIndicator(strokeWidth: 2, color: isDark ? Colors.black : Colors.white),
                              )
                            : const Text(
                                'Sign In to Dashboard',
                                style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                              ),
                      ),
                      const SizedBox(height: 24),
                      Wrap(
                        alignment: WrapAlignment.center,
                        crossAxisAlignment: WrapCrossAlignment.center,
                        spacing: 4,
                        runSpacing: 4,
                        children: [
                          Text(
                            "Don't have an account yet?",
                            style: TextStyle(color: subtextColor, fontSize: 13),
                          ),
                          GestureDetector(
                            onTap: () {
                              Navigator.of(context).push(
                                MaterialPageRoute(builder: (_) => const RegisterScreen()),
                              );
                            },
                            child: Text(
                              'Create Account',
                              style: TextStyle(color: isDark ? Colors.white : Colors.black87, fontWeight: FontWeight.bold, fontSize: 13),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
      ),
    );
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String hint,
    required IconData icon,
    required bool isDark,
    bool isPassword = false,
    bool obscureText = false,
    VoidCallback? onToggleVisibility,
  }) {
    return TextField(
      controller: controller,
      obscureText: obscureText,
      style: TextStyle(
        fontSize: 14,
        color: isDark ? Colors.white : Colors.black87,
      ),
      decoration: InputDecoration(
        hintText: hint,
        hintStyle: TextStyle(
          fontSize: 14,
          color: isDark ? Colors.white54 : Colors.black38,
        ),
        filled: true,
        fillColor: isDark ? const Color(0xFF334155).withValues(alpha: 0.5) : const Color(0xFFF1F5F9),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide.none,
        ),
        contentPadding: const EdgeInsets.symmetric(vertical: 16),
        prefixIcon: Icon(icon, size: 20, color: isDark ? Colors.white54 : Colors.black45),
        suffixIcon: isPassword
            ? IconButton(
                icon: Icon(
                  obscureText ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                  size: 20,
                  color: isDark ? Colors.white54 : Colors.black45,
                ),
                onPressed: onToggleVisibility,
              )
            : null,
      ),
    );
  }
}
