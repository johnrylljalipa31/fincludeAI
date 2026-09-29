  import 'package:flutter/material.dart';


  class ConsentScreen extends StatefulWidget {
    final String userName;
    final VoidCallback onConsentAccepted;

    const ConsentScreen({
      super.key,
      required this.userName,
      required this.onConsentAccepted,
    });

    @override
    State<ConsentScreen> createState() => _ConsentScreenState();
  }

  class _ConsentScreenState extends State<ConsentScreen> {
    bool _agreedToPrivacy = false;
    bool _agreedToSmileSync = false;

    @override
    Widget build(BuildContext context) {
      return Scaffold(
        appBar: AppBar(title: const Text('Data Sharing Consent')),
        body: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24.0),
            child: Container(
              constraints: const BoxConstraints(maxWidth: 500),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Text(
                    'Welcome, ${widget.userName}!',
                    style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 8),
                  const Text(
                    'To calculate your credit score without traditional payslips, Finclude AI requires explicit permission to aggregate your income transactions.',
                    style: TextStyle(color: Colors.black87, height: 1.4),
                  ),
                  const SizedBox(height: 24),
                  Card(
                    elevation: 1,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    child: Padding(
                      padding: const EdgeInsets.all(12.0),
                      child: Column(
                        children: [
                          CheckboxListTile(
                            value: _agreedToPrivacy,
                            onChanged: (val) => setState(() => _agreedToPrivacy = val ?? false),
                            title: const Text(
                              'I consent to the processing of my personal data under the Data Privacy Act of 2012.',
                              style: TextStyle(fontSize: 13),
                            ),
                          ),
                          const Divider(),
                          CheckboxListTile(
                            value: _agreedToSmileSync,
                            onChanged: (val) => setState(() => _agreedToSmileSync = val ?? false),
                            title: const Text(
                              'I authorize Finclude AI and Smile API to read-only access my selected e-wallet and banking transaction history.',
                              style: TextStyle(fontSize: 13),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),
                  ElevatedButton(
                    onPressed: (_agreedToPrivacy && _agreedToSmileSync)
                        ? widget.onConsentAccepted
                        : null,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF0D6EFD),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                    ),
                    child: const Text('Agree & Open Dashboard', style: TextStyle(fontSize: 16)),
                  ),
                ],
              ),
            ),
          ),
        ),
      );
    }
  }