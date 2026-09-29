import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/theme_provider.dart';
import '../../widgets/password_strength_indicator.dart';
import '../../widgets/finclude_logo.dart';
import '../onboarding/consent_modal_screen.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _phoneController = TextEditingController();
  final _idNumberController = TextEditingController();
  String _selectedIdType = 'Philippine National ID (PhilSys)';
  bool _obscurePassword = true;

  final List<String> _idTypes = [
    'Philippine National ID (PhilSys)',
    'UMID / SSS ID',
    "Driver's License",
    'Passport',
    'PRC ID',
    'Postal ID',
  ];

  @override
  void initState() {
    super.initState();
    _passwordController.addListener(() {
      setState(() {});
    });
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _phoneController.dispose();
    _idNumberController.dispose();
    super.dispose();
  }

  void _handleRegister() async {
    final auth = Provider.of<AuthProvider>(context, listen: false);
    final name = _nameController.text.trim();
    final email = _emailController.text.trim();
    final password = _passwordController.text;
    final phone = _phoneController.text.trim();
    final idNumber = _idNumberController.text.trim();

    if (name.isEmpty || email.isEmpty || password.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please fill in your name, email, and password.')),
      );
      return;
    }

    if (password.length < 6) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Password must be at least 6 characters.')),
      );
      return;
    }

    final success = await auth.register(
      name: name,
      email: email,
      password: password,
      phoneNumber: phone.isNotEmpty ? phone : null,
      idType: _selectedIdType,
      idNumber: idNumber.isNotEmpty ? idNumber : null,
    );

    if (!mounted) return;

    if (success) {
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const ConsentModalScreen()),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(auth.errorMessage ?? 'Registration failed')),
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
        title: const Text('Create Finclude Account'),
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
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Container(
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  color: cardColor,
                  borderRadius: BorderRadius.circular(18),
                  border: Border.all(
                    color: isDark ? const Color(0xFF283A5A) : const Color(0xFFE2E8F0),
                  ),
                ),
                child: Row(
                  children: [
                    const FincludeLogo(size: 44),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Gig Worker Registration',
                            style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: textColor),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            'Unlock AI-evaluated alternative credit limits',
                            style: TextStyle(color: subtextColor, fontSize: 12),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),
              TextField(
                controller: _nameController,
                style: TextStyle(color: textColor),
                decoration: InputDecoration(
                  labelText: 'Full Legal Name',
                  prefixIcon: Icon(Icons.person_outline, color: primaryColor),
                ),
              ),
              const SizedBox(height: 16),
              TextField(
                controller: _emailController,
                style: TextStyle(color: textColor),
                decoration: InputDecoration(
                  labelText: 'Email Address',
                  prefixIcon: Icon(Icons.email_outlined, color: primaryColor),
                ),
              ),
              const SizedBox(height: 16),
              TextField(
                controller: _phoneController,
                style: TextStyle(color: textColor),
                decoration: InputDecoration(
                  labelText: 'Mobile Number (+63)',
                  prefixIcon: Icon(Icons.phone_outlined, color: primaryColor),
                ),
              ),
              const SizedBox(height: 16),
              DropdownButtonFormField<String>(
                initialValue: _selectedIdType,
                dropdownColor: cardColor,
                style: TextStyle(color: textColor),
                decoration: InputDecoration(
                  labelText: 'Valid Government ID Type',
                  prefixIcon: Icon(Icons.credit_card_outlined, color: primaryColor),
                ),
                items: _idTypes.map((type) {
                  return DropdownMenuItem(value: type, child: Text(type));
                }).toList(),
                onChanged: (val) => setState(() => _selectedIdType = val ?? _selectedIdType),
              ),
              const SizedBox(height: 16),
              TextField(
                controller: _idNumberController,
                style: TextStyle(color: textColor),
                decoration: InputDecoration(
                  labelText: 'Government ID Number',
                  prefixIcon: Icon(Icons.numbers_outlined, color: primaryColor),
                ),
              ),
              const SizedBox(height: 16),
              TextField(
                controller: _passwordController,
                obscureText: _obscurePassword,
                style: TextStyle(color: textColor),
                decoration: InputDecoration(
                  labelText: 'Password',
                  prefixIcon: Icon(Icons.lock_outline, color: primaryColor),
                  suffixIcon: IconButton(
                    icon: Icon(
                      _obscurePassword ? Icons.visibility_off : Icons.visibility,
                      color: subtextColor,
                    ),
                    onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                  ),
                ),
              ),
              // Real-Time Password Strength Indicator
              PasswordStrengthIndicator(password: _passwordController.text),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: auth.isLoading ? null : _handleRegister,
                style: ElevatedButton.styleFrom(
                  backgroundColor: primaryColor,
                  foregroundColor: isDark ? const Color(0xFF0D1B2A) : Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                ),
                child: auth.isLoading
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                      )
                    : const Text(
                        'Complete Registration & Proceed',
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                      ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
